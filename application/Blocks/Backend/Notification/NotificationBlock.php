<?php

namespace App\Blocks\Backend\Notification;

use System\Core\BaseBlock;

class NotificationBlock extends BaseBlock
{

    public function __construct()
    {
        $this->setLabel('Backend Notification Block');
        $this->setName('Backend\Notification');
        $this->setProps([
            'layout'      => 'default',
            'type'      => 'success',
            'message'      => 'This is a notification message',
        ]);
    }

    // đây là function xử lý data bắt buộc phải có
    public function handleData()
    {   $props = $this->getProps();
        $data = $props;
        return $data;
    }
}
