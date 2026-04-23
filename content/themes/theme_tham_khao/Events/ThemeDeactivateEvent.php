<?php

namespace Themes\Cmsfullform\Events;

class ThemeDeactivateEvent
{
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        // \System\Libraries\Logger::info('[cmsfullform theme] deactivated (root Events)', $this->payload);
    }
}


