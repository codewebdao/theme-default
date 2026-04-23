<?php
namespace System\Libraries;

class Events {
    // Array storing listeners for each event in format: [ 'EventName' => [ priority => [listener1, listener2, ...], ... ], ... ]
    protected static $listeners = [];

    /**
     * Register listener for an event.
     *
     * @param string $eventName Event name, e.g.: 'PostsAddEvent'
     * @param callable|string $listener Callback or class name (with handle() method) that will be called when event runs
     * @param int $priority Priority (higher number means listener runs earlier)
     */
    public static function on($eventName, $listener, $priority = 0)
    {
        self::$listeners[$eventName][$priority][] = $listener;
    }

    /**
     * Trigger (dispatch) an event.
     *
     * This action will:
     * 1. Call all registered listeners for the event (in descending priority order).
     * 2. If there's a default event file in \App\Events with matching name, it will also be called.
     *
     * @param string $eventName Event name
     * @param mixed $payload Data accompanying the event (can be array, object, etc.)
     */
    public static function run($eventName, $payload = null, $namespace = 'App\\Events')
    {
        // Call registered listeners (if any)
        if (!empty(self::$listeners[$eventName])) {
            // Sort by descending priority (higher numbers run first)
            krsort(self::$listeners[$eventName]);
            foreach (self::$listeners[$eventName] as $priority => $listeners) {
                foreach ($listeners as $listener) {
                    try {
                        if (APP_DEBUGBAR){
                            $debug_start = microtime(true);
                        }
                        if (is_callable($listener)) {
                            call_user_func($listener, $payload);
                        } elseif (is_string($listener) && class_exists($listener)) {
                            $instance = new $listener($payload);
                            if (method_exists($instance, 'handle')) {
                                $instance->handle();
                            }
                        }
                        if (APP_DEBUGBAR){
                            global $debug_events;
                            $debug_events[] = [
                                'event' => $eventName,
                                'listener' => $listener,
                                'time' => microtime(true)-$debug_start,
                            ];
                        }
                    } catch (\Exception $e) {
                        // You can log errors here if needed
                        // Example: \System\Libraries\Logger::error("Error in event '{$eventName}' listener: " . $e->getMessage());
                    }
                }
            }
        }
        // Fallback: if default event exists (in App\Events) with matching name, execute it
        $defaultEventClass = $namespace . '\\' . $eventName;
        if (class_exists($defaultEventClass)) {
            try {
                if (APP_DEBUGBAR){
                    $debug_start = microtime(true);
                }
                $instance = new $defaultEventClass($payload);
                if (method_exists($instance, 'handle')) {
                    $instance->handle();
                }
                if (APP_DEBUGBAR){
                    global $debug_events;
                    $debug_events[] = [
                        'event' => $eventName,
                        'listener' => $defaultEventClass,
                        'time' => microtime(true)-$debug_start,
                    ];
                }
            } catch (\Exception $e) {
                // Log error if needed
            }
        }
    }

    /**
     * Trigger multiple events at once.
     *
     * @param array $events Array in format [ 'EventName' => $payload, ... ]
     */
    public static function runs(array $events)
    {
        foreach ($events as $eventName => $payload) {
            self::run($eventName, $payload);
        }
    }
}
