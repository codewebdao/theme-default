<?php

namespace App\Blocks\Backend\Head;

use System\Core\BaseBlock;

class HeadBlock extends BaseBlock
{
    public function __construct()
    {
        $this->setLabel('Backend\Head Block');
        $this->setName('Backend\Head');
        $this->setProps([
            'layout'      => 'default',  // Tên file layout: default.php
            'title'       => 'My Website Title',
            'description' => 'This is the meta description of my website.',
            'keywords'    => '',
            //'canonical'   => 'https://mywebsite.com',
            'meta'        => [
                'author'   => 'My Company',
                'viewport' => 'width=device-width, initial-scale=1'
            ]
        ]);

        // Asset head: đăng ký trong theme admin `functions.php` (View::addJs), không dùng Render::asset.
    }

    public function handleData()
    {
        // Sử dụng dữ liệu menu được truyền vào
        $data = $this->getProps();

        return $data;
    }
}
