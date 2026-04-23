<?php
namespace System\Libraries\Logger;

/**
 * PsrLogger - PSR Compatible Logger for Database Queries
 * 
 * - Ghi file theo config:
 *   + 'db.query'      -> logging.info.path (khi enabled)
 *   + 'db.slow_query' -> logging.slow.path (khi enabled)
 *   + 'db.error'      -> logging.error.path (khi enabled) hoặc bất kỳ level >= error
 * - Các message khác: nếu bạn muốn, map vào 'info' path.
 * 
 * @package System\Libraries\Logger
 */
final class PsrLogger implements LoggerInterface
{
    use LoggerTrait;

    /** @var array<string, array{enabled:bool,path:string}> */
    private $targets;

    public function __construct(array $targets)
    {
        // expected keys: info, slow, error
        $this->targets = $targets;
    }

    public function emergency($message, array $context = array()) { $this->log(LogLevel::EMERGENCY, $message, $context); }
    public function alert($message, array $context = array())     { $this->log(LogLevel::ALERT, $message, $context); }
    public function critical($message, array $context = array())  { $this->log(LogLevel::CRITICAL, $message, $context); }
    public function error($message, array $context = array())     { $this->log(LogLevel::ERROR, $message, $context); }
    public function warning($message, array $context = array())   { $this->log(LogLevel::WARNING, $message, $context); }
    public function notice($message, array $context = array())    { $this->log(LogLevel::NOTICE, $message, $context); }
    public function info($message, array $context = array())      { $this->log(LogLevel::INFO, $message, $context); }
    public function debug($message, array $context = array())     { $this->log(LogLevel::DEBUG, $message, $context); }

    public function log($level, $message, array $context = array())
    {
        $channel = (string)$message; // ví dụ: 'db.query', 'db.slow_query', 'db.error'
        $payload = '['.date('Y-m-d H:i:s')."] {$channel} ".json_encode($context, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;

        // route theo channel ưu tiên
        if ($channel === 'db.query' && $this->isEnabled('info')) {
            $this->appendToFile($this->targets['info']['path'], $payload);
            return;
        }
        if ($channel === 'db.slow_query' && $this->isEnabled('slow')) {
            $this->appendToFile($this->targets['slow']['path'], $payload);
            return;
        }
        if ($channel === 'db.error' && $this->isEnabled('error')) {
            $this->appendToFile($this->targets['error']['path'], $payload);
            return;
        }

        // fallback: ghi error-level trở lên vào error.log nếu bật
        $sev = $this->getSeverity($level);
        if ($sev >= $this->getSeverity(LogLevel::ERROR) && $this->isEnabled('error')) {
            $this->appendToFile($this->targets['error']['path'], $payload);
            return;
        }

        // còn lại: nếu info enabled -> ghi vào query.log
        if ($this->isEnabled('info')) {
            $this->appendToFile($this->targets['info']['path'], $payload);
        }
    }

    private function isEnabled($key)
    {
        return isset($this->targets[$key]['enabled']) && $this->targets[$key]['enabled'] === true;
    }
}
