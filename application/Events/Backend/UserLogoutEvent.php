<?php
namespace App\Events\Backend;

class UserLogoutEvent {
    protected $data;

    /**
     * Initialize event with data related to post.
     *
     * @param mixed $data Post data (can be array, object, etc.)
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Default handler for UserLogoutEvent.
     */
    public function handle()
    {
        // Handle event here
        // Example: Write log, send notification, etc.
        // Log user login information
    }
}
