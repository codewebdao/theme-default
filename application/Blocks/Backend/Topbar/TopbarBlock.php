<?php

namespace App\Blocks\Backend\Topbar;

use App\Models\LanguagesModel;
use System\Core\BaseBlock;

class TopbarBlock extends BaseBlock
{
    protected $langModel;
    public function __construct()
    {
        $this->setLabel('Backend\Topbar Block');
        $this->setName('Backend\Topbar');
        $this->setProps([
            'layout'      => 'default',
        ]);
        $this->langModel = new LanguagesModel();
    }

    // đây là function xử lý data bắt buộc phải có
    public function handleData()
    {
        $props = $this->getProps();
        $data = $props;
        $data['user_info'] = $props['user_info'];
        return $data;
    }
}
