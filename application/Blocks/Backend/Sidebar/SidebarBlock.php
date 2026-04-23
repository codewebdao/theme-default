<?php

namespace App\Blocks\Backend\Sidebar;

use System\Core\BaseBlock;
use App\Libraries\Admin\AdminMenuBuilder;

/**
 * @deprecated Giữ tương thích Render::block('Backend\\Sidebar'); logic menu nằm ở {@see AdminMenuBuilder}.
 */
class SidebarBlock extends BaseBlock
{
    public function __construct()
    {
        $this->setLabel('Backend\Sidebar Block');
        $this->setName('Backend\Sidebar');
        $this->setProps([
            'layout' => 'default',
            'url' => $_SERVER['REQUEST_URI'] ?? '/',
        ]);
    }

    public function handleData()
    {
        return [
            'menuData' => AdminMenuBuilder::getMenuData(),
        ];
    }
}
