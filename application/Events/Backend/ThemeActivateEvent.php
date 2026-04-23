<?php

namespace App\Events\Backend;

use System\Libraries\Events;

class ThemeActivateEvent
{
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        $theme = $this->payload['theme'] ?? '';
        if (!$theme) return;
        $camel = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $theme)));
        $themeEvent = 'Themes\\' . $camel . '\\Events\\ThemeActivateEvent';
        Events::run($themeEvent, $this->payload);
    }
}


