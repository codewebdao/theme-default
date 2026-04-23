<?php

namespace App\Blocks\Backend\PageTitle;

use System\Core\BaseBlock;

class PageTitleBlock extends BaseBlock
{

    public function __construct()
    {
        $this->setLabel('Backend PageTitle Block');
        $this->setName('Backend\PageTitle');
        $this->setProps([
            'layout'      => 'default',
        ]);
    }

    // đây là function xử lý data bắt buộc phải có
    public function handleData()
    {   $props = $this->getProps();
        $data = $props;
        return $data;
    }
}
