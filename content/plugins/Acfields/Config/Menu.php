<?php
return array(
    'menu' => array(

        [
            'type' => 'menu',
            'id' => 'posttypes',
            'label' => 'Advanced Custom Fields',
            'href' => admin_url('acfields/index'),
            'permissions' => [
                'Plugins\Acfields\Controllers\Acfields' => 'index'
            ],
            'icon' => 'file-text',
            'order' => 2,
            'children' => [
                [
                    'id' => 'list-posttypes',
                    'label' => __('list').' '.__('post types'),
                    'href' => admin_url('acfields/index'),
                    'permissions' => [
                        'Plugins\Acfields\Controllers\Acfields' => 'index'
                    ],
                    'icon' => 'list',
                    'order' => 1
                ],
                [
                    'id' => 'add-posttype',
                    'label' =>  __('add').' '.__('post types'),
                    'href' => admin_url('acfields/add'),
                    'permissions' => [
                        'Plugins\Acfields\Controllers\Acfields' => ['add','edit']
                    ],
                    'icon' => 'plus',
                    'order' => 2
                ]
            ]
        ]
    
    )
);