<?php
namespace System\Libraries;

use System\Core\AppException;

class TaskQueue {

    // Prefix for queue and failed queue keys on Redis
    protected static $queueKeyPrefix = 'task_queue:';
    protected static $failedQueueKeyPrefix = 'failed_task_queue:';

    /**
     * Push a job into the queue.
     *
     * @param string $queueName Queue name (e.g.: 'email', 'push_notifications')
     * @param string $eventName Event name to be called when the job is processed (e.g.: 'SendEmailEvent')
     * @param mixed  $payload   Data attached to the event.
     * @param int    $attempts  Number of attempts (default is 0)
     *
     * @return void
     */
    public static function push($queueName, $eventName, $payload = null, $attempts = 0)
    {
        $key = self::$queueKeyPrefix . $queueName;
        $job = [
            'event'     => $eventName,
            'payload'   => $payload,
            'timestamp' => time(),
            'attempts'  => $attempts,
        ];
        $jobJson = json_encode($job);
        // Push to the head of the list
        RedisCache::lpush($key, $jobJson);
    }

    /**
     * Pop a job from the queue using blocking pop (BRPOP) if available.
     *
     * @param string $queueName Queue name.
     * @param int    $timeout   Wait time (seconds) if the queue is empty.
     *
     * @return array|null Job as array if available, or null if not.
     */
    public static function pop($queueName, $timeout = 5)
    {
        $key = self::$queueKeyPrefix . $queueName;
        // If RedisCache supports blocking pop, use BRPOP
        if (method_exists('RedisCache', 'brpop')) {
            // BRPOP usually returns array [key, value]
            $result = RedisCache::brpop($key, $timeout);
            if ($result && isset($result[1])) {
                return json_decode($result[1], true);
            }
        } else {
            // Fallback to non-blocking pop
            $jobJson = RedisCache::rpop($key);
            if ($jobJson) {
                return json_decode($jobJson, true);
            }
        }
        return null;
    }

    /**
     * Process a job from the queue.
     *
     * If the job fails, increase the attempts count and:
     * - If not exceeding max retries, requeue it.
     * - If exceeded, move the job to the failed queue.
     *
     * @param string $queueName Queue name.
     * @param int    $maxRetries Maximum number of retries (default: 3)
     *
     * @return bool Returns true if processed successfully, false if no job or error.
     */
    public static function processJob($queueName, $maxRetries = 3)
    {
        $job = self::pop($queueName);
        if (!$job) {
            return false;
        }
        try {
            // Call the registered event (using the built Events library)
            \System\Libraries\Events::run($job['event'], $job['payload']);
        } catch (\Exception $e) {
            // You can log the error here
            // Example: Logger::error("Error processing event {$job['event']}: " . $e->getMessage());
            // Increase the number of attempts
            $job['attempts'] = isset($job['attempts']) ? $job['attempts'] + 1 : 1;
            if ($job['attempts'] <= $maxRetries) {
                // Requeue job for retry
                self::push($queueName, $job['event'], $job['payload'], $job['attempts']);
            } else {
                // Move job to failed queue
                self::pushFailed($queueName, $job);
            }
            return false;
        }
        return true;
    }

    /**
     * Push a failed job into the failed queue.
     *
     * @param string $queueName Original queue name.
     * @param array  $job       Job as array.
     *
     * @return void
     */
    public static function pushFailed($queueName, $job)
    {
        $key = self::$failedQueueKeyPrefix . $queueName;
        RedisCache::lpush($key, json_encode($job));
        // You can log failed jobs for monitoring.
    }

    /**
     * Run a worker to continuously fetch and process jobs from the queue.
     *
     * Use blocking pop if available to reduce continuous I/O calls.
     * You can add signal handling for graceful shutdown support.
     *
     * @param string $queueName    Queue name.
     * @param int    $maxRetries   Maximum number of retries.
     * @param int    $timeout      Wait time (seconds) for blocking pop.
     *
     * @return void
     */
    public static function runWorker($queueName, $maxRetries = 3, $timeout = 5)
    {
        // You can register signal handling (SIGTERM, SIGINT) for graceful shutdown if needed.
        while (true) {
            $processed = self::processJob($queueName, $maxRetries);
            if (!$processed) {
                // If there are no jobs, will wait for blocking pop (timeout) or sleep more.
                sleep(1);
            }
        }
    }
}
