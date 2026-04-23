<?php

namespace App\Events\Backend;

use System\Libraries\Events;

class PluginDeactivateEvent
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
        $camel = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $plugin)));
        $pluginEvent = 'Plugins\\' . $camel . '\\Events\\PluginDeactivateEvent';
        Events::run($pluginEvent, $this->payload);
    }
}


