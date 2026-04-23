<?php

namespace System\Libraries\Logger;

/**
 * LogLevel - PSR-3 Compatible Log Levels (Standalone - No External Dependencies)
 * 
 * This class is identical to Psr\Log\LogLevel but implemented locally
 * to avoid external dependencies while maintaining PSR-3 compatibility.
 * 
 * @package System\Libraries\Logger
 */
class LogLevel
{
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';
}
