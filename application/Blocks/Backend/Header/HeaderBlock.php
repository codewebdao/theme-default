<?php

namespace App\Blocks\Backend\Header;

use System\Libraries\Session;
use App\Libraries\Fasttoken;
use App\Models\UsersModel;
use App\Libraries\Fastlang as Flang;
use System\Libraries\Render;


use System\Core\BaseBlock;

class HeaderBlock extends BaseBlock
{
    public function __construct()
    {
        $this->setLabel('Backend\Header Block'); //Bat buoc: Khai bao ten cua Block
        $this->setName('Backend\Header'); //Bat buoc: Khai bao ten Folder Block
        //Bat buoc: Khai bao cac Props mac dinh cua Block
        $this->setProps([
            'layout' => 'default',
            'title' =>  'Backend Header Block Title',
            'breadcrumb' => [
                [
                    'name' => 'Dashboard',
                    'url' => admin_url('home'),
                    'active' => true
                ]
            ]
        ]);
    }
    // đây là function xử lý data bắt buộc phải có
    public function handleData()
    {
        global $me_info;
        if(empty($me_info)){
            redirect(auth_url('logout'));
        }
        $props = $this->getProps();
        //truy van sql de lay du lieu can thiet cua khoi blocks

        $result = [
            'userInfo' => $me_info,
            'title' => $props['title'],
            'breadcrumb' => $props['breadcrumb'],
        ];
        return $result;
    }
}
