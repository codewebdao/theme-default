<?php

namespace App\Blocks\Backend\Footer;

use System\Core\BaseBlock;
use System\Libraries\Render;

class FooterBlock extends BaseBlock
{

    public function __construct()
    {
        $this->setLabel('Backend\Footer Block');
        $this->setName('Backend\Footer');
        $this->setProps([
            'layout'      => 'default',
        ]);
        // Render::asset('js', 'js/jfast.1.2.3.js', ['area' => 'backend', 'location' => 'footer']);
        // Render::asset('js', 'js/script.js', ['area' => 'backend', 'location' => 'footer']);
        // Render::asset('js', 'js/theme.js', ['area' => 'backend', 'location' => 'footer']);
    }

    // đây là function xử lý data bắt buộc phải có
    public function handleData()
    {
        return $this->getProps();
    }
}
