<?php

namespace Themes\Cmsfullform\Events;

class ThemeActivateEvent
{
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        // \System\Libraries\Logger::info('[cmsfullform theme] activated (root Events)', $this->payload);
    }
}


