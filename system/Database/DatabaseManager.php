<?php
namespace System\Database;

use System\Libraries\Logger\LoggerInterface;
use System\Libraries\Logger\PsrLogger;

/**
 * DatabaseManager
 *
 * Holds global config and produces named DatabaseConnection instances.
 * Internally wires Router & ConnectionManager (provided in Part 2).
 */
final class DatabaseManager
{
    /** @var array */
    private $config;

    /** @var LoggerInterface|null */
    private $logger;

    /** @var Router */
    private $router;

    /**
     * @param array $config
     * @param LoggerInterface|null $logger
     */
    public function __construct(array $config, LoggerInterface $logger = null)
    {
        $this->config = $config;

        if ($logger) {
            $this->logger = $logger;
        } else {
            // Lấy logging config từ connection default (đủ dùng 90%).
            $def = isset($config['default']) ? $config['default'] : null;
            $cx  = $def && isset($config['connections'][$def]) ? $config['connections'][$def] : array();
            $lg  = isset($cx['logging']) ? $cx['logging'] : array();

            $targets = array(
                'info'  => array(
                    'enabled' => isset($lg['info']['enabled']) ? (bool)$lg['info']['enabled'] : false,
                    'path'    => isset($lg['info']['path'])    ? (string)$lg['info']['path']    : (PATH_WRITE.'logs/query.log'),
                ),
                'slow'  => array(
                    'enabled' => isset($lg['slow']['enabled']) ? (bool)$lg['slow']['enabled'] : true,
                    'path'    => isset($lg['slow']['path'])    ? (string)$lg['slow']['path']    : (PATH_WRITE.'logs/slow.log'),
                ),
                'error' => array(
                    'enabled' => isset($lg['error']['enabled']) ? (bool)$lg['error']['enabled'] : true,
                    'path'    => isset($lg['error']['path'])    ? (string)$lg['error']['path']    : (PATH_WRITE.'logs/db_error.log'),
                ),
            );
            $this->logger = new PsrLogger($targets);
        }

        $this->router = new Router($config, new ConnectionManager($config, $this->logger), $this->logger);
    }

    /**
     * Get a DatabaseConnection by name (or default).
     *
     * @param string|null $name
     * @return DatabaseConnection
     */
    public function connection($name = null)
    {
        $name = $name ?: (isset($this->config['default']) ? $this->config['default'] : 'default');
        return new DatabaseConnection($name, $this->config, $this->router, $this->logger);
    }
}
