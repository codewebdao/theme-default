<?php
return [
    'roles_type' => 'merge', // add (add new role) | replace (overwrite completely) | merge (merge with existing role)
    'order' => 1,
    'admin' => [
        'name' => 'Administrator',
        'description' => 'Administrator role with update Roles for Acfields plugin',
        'permissions' => [
            'Plugins\Acfields\Controllers\Acfields' => [
                'index',
                'add',
                'edit',
                'copy',
                'settings',
                'save_settings',
                'delete',
                'changestatus'
            ]
        ]
    ]
];