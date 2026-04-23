<?php
namespace App\Libraries;

class Fastuuid
{
    const SAFE_SYMBOLS = '_-0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Generate nanoid ID (giống JS)
     *
     * @param int $size
     * @param int $mode (1 = normal, 2 = dynamic)
     * @return string
     */
    public static function uuid(int $size = 21, int $mode = 1): string
    {
        $alphabet = self::SAFE_SYMBOLS;
        if ($mode === 2) {
            return self::dynamic($size, $alphabet);
        }
        return self::normal($size, $alphabet);
    }

    /**
     * Generate a numeric, time-ordered ID (length 16..18), no shared cache required.
     *
     * Design:
     *  - Prefix: UNIX epoch in microseconds (always 16 digits around year 2025).
     *  - Suffix (when length > 16): left-most = sequence (dominates ordering),
     *    then optional nodeId (to reduce cross-process collisions),
     *    then optional random digits for extra entropy.
     *  - For exactly 16 digits: we reserve 1–2 lowest digits of the microsecond
     *    part to embed {seq + node + random}. This sacrifices a tiny bit of time
     *    resolution but keeps total length at 16.
     *
     * Properties:
     *  - Monotonic per process: never goes backwards; guards clock skews by
     *    not allowing the "tick" to decrease.
     *  - Collision-resistant across processes without Redis/APCu thanks to:
     *    nodeId (if provided) + sequence + small random tail.
     *  - Blocking behavior: if all sequence slots are consumed within a tick,
     *    it spins briefly (usleep) until the next tick; if the spin budget is
     *    exhausted, it “steps” the logical tick forward to avoid blocking.
     *
     * @param int      $digits      Desired length: 16..18 (default 17).
     * @param int|null $nodeId      Optional node ID (0..99). Extra digits beyond
     *                              available suffix space are truncated automatically.
     * @param int      $spinMicros  Max spin-wait in microseconds before a logical
     *                              tick step (default 2000µs = 2ms).
     * @return string               Numeric string ID (do NOT cast to int on 32-bit).
     */
    public static function timeuuid(int $digits = 17, ?int $nodeId = null, int $spinMicros = 2000): string
    {
        // ---- sanitize inputs ----------------------------------------------------
        if ($digits < 16) $digits = 16;
        if ($digits > 18) $digits = 18;
        if ($nodeId !== null) {
            if ($nodeId < 0 || $nodeId > 99) {
                throw new InvalidArgumentException('nodeId must be between 0 and 99 or null');
            }
        }
        if ($spinMicros < 0) $spinMicros = 0;

        // Static state for monotonic behavior (per PHP process)
        static $lastTick = -1;  // logical "tick": microseconds (len>16) or grouped micros (len=16)
        static $seq      = -1;  // sequence within the same tick

        // Current microseconds since epoch (int)
        $nowUs = (int) floor(microtime(true) * 1_000_000);

        // -------------------------------------------------------------------------
        // CASE A: length > 16  (17 or 18 digits)
        // -------------------------------------------------------------------------
        if ($digits > 16) {
            // Available suffix length beyond the 16-digit microsecond prefix
            $suffixLen = $digits - 16; // 1 or 2

            // How many digits of nodeId can we actually fit?
            $nodeDigits = ($nodeId === null) ? 0 : (($nodeId <= 9) ? 1 : 2);
            // Reserve at least 1 digit for sequence; node consumes what's left.
            $nodeUse    = min($nodeDigits, max(0, $suffixLen - 1));
            $remain     = $suffixLen - $nodeUse;

            // Allocate sequence digits (1..2) and leftover random digits (0..1)
            $seqLen  = max(1, min(2, $remain));
            $randLen = max(0, $remain - $seqLen);

            // Tick is raw microseconds for len > 16
            $tick = max($nowUs, $lastTick); // monotonic guard
            if ($tick === $lastTick) {
                $seq++;
            } else {
                $seq = 0;
            }

            // Sequence overflow handling (spin, then logical step)
            $maxSeq = (int) (10 ** $seqLen);
            if ($seq >= $maxSeq) {
                $deadline = ($spinMicros > 0) ? (hrtime(true) + $spinMicros * 1000) : 0;
                do {
                    usleep(1);
                    $tickNow = (int) floor(microtime(true) * 1_000_000);
                    if ($tickNow > $lastTick) {
                        $tick = $tickNow;
                        $seq  = 0;
                        break;
                    }
                } while ($deadline && hrtime(true) < $deadline);

                // If still same tick after spinning, step the logical tick
                if ($tick <= $lastTick) {
                    $tick = $lastTick + 1;
                    $seq  = 0;
                }
            }

            // Persist tick for next call
            $lastTick = $tick;

            // Build suffix = [SEQ][NODE][RAND]  (left to right)
            $seqStr  = str_pad((string)$seq,     $seqLen,  '0', STR_PAD_LEFT);
            $nodeStr = ($nodeUse > 0)
                ? str_pad((string)($nodeId % (10 ** $nodeUse)), $nodeUse, '0', STR_PAD_LEFT)
                : '';
            $randStr = ($randLen > 0)
                ? str_pad((string)random_int(0, (10 ** $randLen) - 1), $randLen, '0', STR_PAD_LEFT)
                : '';

            $prefix = str_pad((string)$tick, 16, '0', STR_PAD_LEFT); // microseconds as 16 digits
            return $prefix . $seqStr . $nodeStr . $randStr;
        }

        // -------------------------------------------------------------------------
        // CASE B: length = 16  (exactly the microsecond length)
        // We must reserve 1–2 lowest digits to embed {seq + node + rand}.
        // -------------------------------------------------------------------------
        $nodeDigits = ($nodeId === null) ? 0 : (($nodeId <= 9) ? 1 : 2);
        $reserve   = max(1, min(2, $nodeDigits + 1)); // keep ≥1 for seq; cap at 2 total reserved digits

        // Group microseconds by 10^reserve so the sequence applies within the group
        $groupSize = (int) (10 ** $reserve);
        $groupTick = intdiv(max($nowUs, $lastTick), $groupSize); // monotonic guard at group level

        if ($groupTick === $lastTick) {
            $seq++;
        } else {
            $seq = 0;
        }

        // One sequence digit only for 16-length (0..9)
        $maxSeq = 10;
        if ($seq >= $maxSeq) {
            // Spin up to spinMicros for next group, then step logically
            $deadline = ($spinMicros > 0) ? (hrtime(true) + $spinMicros * 1000) : 0;
            do {
                usleep(1);
                $tickNow = intdiv((int) floor(microtime(true) * 1_000_000), $groupSize);
                if ($tickNow > $lastTick) {
                    $groupTick = $tickNow;
                    $seq       = 0;
                    break;
                }
            } while ($deadline && hrtime(true) < $deadline);

            if ($groupTick <= $lastTick) {
                $groupTick = $lastTick + 1; // logical step
                $seq       = 0;
            }
        }

        // Persist tick for next call
        $lastTick = $groupTick;

        // Compose base = [microseconds with lowest $reserve digits zeroed]
        $usStr   = str_pad((string)$nowUs, 16, '0', STR_PAD_LEFT);
        // Replace current group suffix with zeros (string-safe on 32-bit):
        $baseStr = substr($usStr, 0, 16 - $reserve) . str_repeat('0', $reserve);

        // Tail = [SEQ (1 digit)][NODE (<= reserve-1)][RAND (fill rest)]
        $nodeUse = min($nodeDigits, max(0, $reserve - 1));
        $randLen = max(0, $reserve - 1 - $nodeUse);

        $seqStr  = (string)($seq % 10); // 1 digit
        $nodeStr = ($nodeUse > 0)
            ? str_pad((string)($nodeId % (10 ** $nodeUse)), $nodeUse, '0', STR_PAD_LEFT)
            : '';
        $randStr = ($randLen > 0)
            ? str_pad((string)random_int(0, (10 ** $randLen) - 1), $randLen, '0', STR_PAD_LEFT)
            : '';

        $tail = $seqStr . $nodeStr . $randStr; // length == $reserve
        return substr($baseStr, 0, 16 - $reserve) . $tail;
    }


    /**
     * Giống nanoid gốc: tạo ID với alphabet tùy chỉnh
     *
     * @param string $alphabet
     * @param int $size
     * @return string
     */
    public static function format(string $alphabet = '', int $size = 21): string
    {
        $alphabet = $alphabet ?: self::SAFE_SYMBOLS;
        return self::dynamic($size, $alphabet);
    }

    /**
     * Normal random mode (mt_rand)
     */
    protected static function normal(int $size, string $alphabet): string
    {
        $id = '';
        $len = strlen($alphabet);
        while ($size-- > 0) {
            $rand = mt_rand(0, $len - 1);
            $id .= $alphabet[$rand];
        }
        return $id;
    }

    /**
     * Dynamic random mode (crypto + mask giống JS Nanoid)
     */
    protected static function dynamic(int $size, string $alphabet): string
    {
        $len = strlen($alphabet);
        $mask = (2 << (int)(log($len - 1) / M_LN2)) - 1;
        $step = (int)ceil(1.6 * $mask * $size / $len);
        $id = '';

        while (true) {
            $bytes = random_bytes($step);
            foreach (str_split($bytes) as $byte) {
                $index = ord($byte) & $mask;
                if (isset($alphabet[$index])) {
                    $id .= $alphabet[$index];
                    if (strlen($id) === $size) {
                        return $id;
                    }
                }
            }
        }
    }
}
