<?php
namespace System\Database\Query\Traits;

/**
 * DateTimeTrait (final API)
 *
 * ✅ Operator is the LAST argument (default '=')
 *    - whereDate($column, $value, $op='=')
 *    - whereDateTime($column, $value, $op='=')
 *    - whereTime($column, $value, $op='=')
 *    - *Between helpers giữ nguyên chữ ký*
 *
 * 🔁 Backward-compat shim:
 *    - If called like whereDate('col','>=','2025-10-01'), we auto-swap to ('2025-10-01','>=')
 */
trait DateTimeTrait
{
    /* ================= Normalizers ================= */

    /** @return string 'Y-m-d' */
    private function normDate($v)
    {
        if ($v instanceof \DateTimeInterface) return $v->format('Y-m-d');
        if (\is_numeric($v)) return \date('Y-m-d', (int)$v);
        $s = \trim((string)$v);
        if (\preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s;
        $ts = \strtotime($s);
        if ($ts === false) throw new \InvalidArgumentException('Invalid date: '.\print_r($v,true));
        return \date('Y-m-d', $ts);
    }

    /** @return string 'Y-m-d H:i:s' */
    private function normDateTime($v)
    {
        if ($v instanceof \DateTimeInterface) return $v->format('Y-m-d H:i:s');
        if (\is_numeric($v)) return \date('Y-m-d H:i:s', (int)$v);
        $ts = \strtotime(\trim((string)$v));
        if ($ts === false) throw new \InvalidArgumentException('Invalid datetime: '.\print_r($v,true));
        return \date('Y-m-d H:i:s', $ts);
    }

    /** @return string 'H:i:s' */
    private function normTime($v)
    {
        if ($v instanceof \DateTimeInterface) return $v->format('H:i:s');
        $s = \trim((string)$v);
        if (\preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $s)) {
            if (\strlen($s) === 5) $s .= ':00';
            list($hh,$mm,$ss) = \explode(':',$s);
            if ((int)$hh>23 || (int)$mm>59 || (int)$ss>59) {
                throw new \InvalidArgumentException('Invalid time (range): '.$s);
            }
            return \sprintf('%02d:%02d:%02d',$hh,$mm,$ss);
        }
        $ts = \strtotime($s);
        if ($ts === false) throw new \InvalidArgumentException('Invalid time: '.\print_r($v,true));
        return \date('H:i:s',$ts);
    }

    private function dayStart($ymd)     { return $ymd.' 00:00:00'; }
    private function nextDayStart($ymd) { $ts=\strtotime($ymd.' +1 day'); return \date('Y-m-d',$ts).' 00:00:00'; }

    /** Heuristic: does a value look like a SQL comparison operator? */
    private function looksLikeOperator($x): bool
    {
        if (!\is_string($x)) return false;
        $x = \strtoupper(\trim($x));
        return \in_array($x, ['=','>','>=','<','<=','<>','!=','LIKE','NOT LIKE','IN','NOT IN'], true)
            || $x === '=>' || $x === '=<';
    }

    /** Swap ($value,$op) if passed in legacy order: ($op,$value). */
    private function normalizeValueAndOp($value, $op)
    {
        // legacy call: whereDate('col','>=','2025-10-01')
        if ($this->looksLikeOperator($value) && !$this->looksLikeOperator($op)) {
            $tmp = $value; $value = $op; $op = $tmp;
        }
        // normalize typos like '=>'/'=<'
        $op = $this->normalizeOp($op);
        return [$value, $op];
    }

    /* ================= WHERE DATE (index-friendly) ================= */

    /**
     * WHERE by DATE (compare by calendar day, sargable range)
     * Input:
     *  - string $column
     *  - string|int|\DateTimeInterface $value (any parseable date; unix ts ok)
     *  - string $op '='|'!='|'<'|'<='|'>'|'>=' (default '=')
     * Output: $this
     */
    public function whereDate($column, $value, $op = '=')
    {
        list($value, $op) = $this->normalizeValueAndOp($value, $op);

        $col   = $this->grammar->quoteIdentifier($column);
        $ymd   = $this->normDate($value);
        $start = $this->dayStart($ymd);
        $end   = $this->nextDayStart($ymd);

        switch ($op) {
            case '=':  $this->wheres[] = ['type'=>'basic','boolean'=>'AND','sql'=>"{$col} >= ? AND {$col} < ?", 'bindings'=>[$start,$end]]; break;
            case '!=':
            case '<>': $this->wheres[] = ['type'=>'basic','boolean'=>'AND','sql'=>"NOT ({$col} >= ? AND {$col} < ?)", 'bindings'=>[$start,$end]]; break;
            case '>':  $this->wheres[] = ['type'=>'basic','boolean'=>'AND','sql'=>"{$col} >= ?", 'bindings'=>[$end]]; break;
            case '>=': $this->wheres[] = ['type'=>'basic','boolean'=>'AND','sql'=>"{$col} >= ?", 'bindings'=>[$start]]; break;
            case '<':  $this->wheres[] = ['type'=>'basic','boolean'=>'AND','sql'=>"{$col} < ?",  'bindings'=>[$start]]; break;
            case '<=': $this->wheres[] = ['type'=>'basic','boolean'=>'AND','sql'=>"{$col} < ?",  'bindings'=>[$end]]; break;
            default: throw new \InvalidArgumentException("Unsupported operator for whereDate: {$op}");
        }
        return $this;
    }

    /** OR variant */
    public function orWhereDate($column, $value, $op = '=')
    {
        $before = \count($this->wheres);
        $this->whereDate($column, $value, $op);
        $this->wheres[$before]['boolean'] = 'OR';
        return $this;
    }

    /**
     * WHERE date BETWEEN inclusive days: [start 00:00:00, (end+1d) 00:00:00)
     * Input:
     *  - string $column
     *  - mixed  $startDate
     *  - mixed  $endDate
     */
    public function whereDateBetween($column, $startDate, $endDate)
    {
        $col   = $this->grammar->quoteIdentifier($column);
        $sYmd  = $this->normDate($startDate);
        $eYmd  = $this->normDate($endDate);
        $start = $this->dayStart($sYmd);
        $end   = $this->nextDayStart($eYmd);

        $this->wheres[] = ['type'=>'basic','boolean'=>'AND','sql'=>"{$col} >= ? AND {$col} < ?", 'bindings'=>[$start,$end]];
        return $this;
    }

    /** OR variant */
    public function orWhereDateBetween($column, $startDate, $endDate)
    {
        $before = \count($this->wheres);
        $this->whereDateBetween($column, $startDate, $endDate);
        $this->wheres[$before]['boolean'] = 'OR';
        return $this;
    }

    /* ================= WHERE DATETIME ================= */

    /**
     * WHERE by DATETIME (exact timestamp)
     * Input:
     *  - string $column
     *  - mixed  $value  (string|\DateTimeInterface|int)
     *  - string $op '='|'!='|'<>','>','>=','<','<='
     */
    public function whereDateTime($column, $value, $op = '=')
    {
        list($value, $op) = $this->normalizeValueAndOp($value, $op);

        if (!\in_array($op, ['=','!=','<>','>','>=','<','<='], true)) {
            throw new \InvalidArgumentException("Unsupported operator for whereDateTime: {$op}");
        }

        $col = $this->grammar->quoteIdentifier($column);
        $dt  = $this->normDateTime($value);
        $this->wheres[] = ['type'=>'basic','boolean'=>'AND','sql'=>"{$col} {$op} ?", 'bindings'=>[$dt]];
        return $this;
    }

    /** OR variant */
    public function orWhereDateTime($column, $value, $op = '=')
    {
        $before = \count($this->wheres);
        $this->whereDateTime($column, $value, $op);
        $this->wheres[$before]['boolean'] = 'OR';
        return $this;
    }

    /**
     * WHERE datetime BETWEEN inclusive: [start, end]
     */
    public function whereDateTimeBetween($column, $startDateTime, $endDateTime)
    {
        $col   = $this->grammar->quoteIdentifier($column);
        $start = $this->normDateTime($startDateTime);
        $end   = $this->normDateTime($endDateTime);

        $this->wheres[] = ['type'=>'basic','boolean'=>'AND','sql'=>"{$col} >= ? AND {$col} <= ?", 'bindings'=>[$start,$end]];
        return $this;
    }

    /** OR variant */
    public function orWhereDateTimeBetween($column, $startDateTime, $endDateTime)
    {
        $before = \count($this->wheres);
        $this->whereDateTimeBetween($column, $startDateTime, $endDateTime);
        $this->wheres[$before]['boolean'] = 'OR';
        return $this;
    }

    /* ================= WHERE TIME ================= */

    /**
     * WHERE by TIME (HH:MM[:SS])
     * Input:
     *  - string $column
     *  - mixed  $value
     *  - string $op '='|'!='|'<>','>','>=','<','<='
     */
    public function whereTime($column, $value, $op = '=')
    {
        list($value, $op) = $this->normalizeValueAndOp($value, $op);

        if (!\in_array($op, ['=','!=','<>','>','>=','<','<='], true)) {
            throw new \InvalidArgumentException("Unsupported operator for whereTime: {$op}");
        }

        $col = $this->grammar->quoteIdentifier($column);
        $tm  = $this->normTime($value);
        $this->wheres[] = ['type'=>'basic','boolean'=>'AND','sql'=>"{$col} {$op} ?", 'bindings'=>[$tm]];
        return $this;
    }

    /** OR variant */
    public function orWhereTime($column, $value, $op = '=')
    {
        $before = \count($this->wheres);
        $this->whereTime($column, $value, $op);
        $this->wheres[$before]['boolean'] = 'OR';
        return $this;
    }

    /**
     * WHERE time BETWEEN inclusive: [start, end]
     */
    public function whereTimeBetween($column, $startTime, $endTime)
    {
        $col   = $this->grammar->quoteIdentifier($column);
        $start = $this->normTime($startTime);
        $end   = $this->normTime($endTime);

        $this->wheres[] = ['type'=>'basic','boolean'=>'AND','sql'=>"{$col} >= ? AND {$col} <= ?", 'bindings'=>[$start,$end]];
        return $this;
    }

    /** OR variant */
    public function orWhereTimeBetween($column, $startTime, $endTime)
    {
        $before = \count($this->wheres);
        $this->whereTimeBetween($column, $startTime, $endTime);
        $this->wheres[$before]['boolean'] = 'OR';
        return $this;
    }
}
