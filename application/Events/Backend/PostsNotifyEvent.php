<?php
namespace App\Events\Backend;

class PostsNotifyEvent {
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {

    }
}
