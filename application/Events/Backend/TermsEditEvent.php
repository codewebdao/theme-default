<?php
namespace App\Events\Backend;

class TermsEditEvent {
    protected $data;

    /**
     * Khởi tạo event với dữ liệu liên quan đến post.
     *
     * @param mixed $data Dữ liệu của post (có thể là array, object, v.v.)
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Xử lý mặc định cho event TermsEditEvent.
     */
    public function handle()
    {
        // Example: Write log, send notification email, update cache, etc.
    }
}
