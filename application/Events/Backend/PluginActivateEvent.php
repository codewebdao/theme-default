<?php

namespace App\Events\Backend;

use System\Libraries\Events;

class PluginActivateEvent
{
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        $plugin = $this->payload['plugin'] ?? '';
        if (!$plugin) return;
        // Gọi event nội bộ trong plugin (Plugins\{Plugin}\Events\Backend\PluginActivateEvent)
        $camel = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $plugin)));
        Events::run('Events\\PluginActivateEvent', $this->payload, 'Plugins\\' . $camel);
    }
}


