<?php
namespace App\Events\Backend;

class LanguagesAddEvent {
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
     * Default handler for LanguagesAddEvent.
     */
    public function handle()
    {
        // Example: Write log, send email notification, update cache, etc.
        echo "Default LanguagesAddEvent handler executed. Data: " . json_encode($this->data) . "<br>";
    }
}
