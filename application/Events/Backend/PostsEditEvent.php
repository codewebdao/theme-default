<?php
namespace App\Events\Backend;

class PostsEditEvent {
    protected $data;

    /**
     * Khởi tạo event với dữ liệu liên quan đến post.
     *
     * @param mixed $data Dữ liệu của post (có thể là array, object, v.v.)
     */
    public function __construct($data)
    {
        $this->data = $data;
        load_helpers(['frontend']);
    }

    /**
     * Xử lý mặc định cho event PostsEditEvent.
     */
    public function handle()
    {
    }
}
