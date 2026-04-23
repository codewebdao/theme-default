<?php

namespace App\Libraries\Admin;

use App\Libraries\Fastlang as Flang;
use App\Blocks\Backend\Sidebar\MenuService;

/**
 * Dữ liệu menu admin (sidebar) — tách khỏi Blocks để controller/theme chỉ gọi thư viện.
 */
final class AdminMenuBuilder
{
    /**
     * Menu đã resolve quyền + active (cùng định dạng SidebarBlock cũ trả về trong menuData).
     */
    public static function getMenuData(): array
    {
        Flang::load('general', APP_LANG);
        $postTypes = posttype_active();
        $builder = new self();
        $menuItems = $builder->buildFlatMenuItems($postTypes);
        MenuService::setItems($menuItems);

        return MenuService::getMenus();
    }

    /**
     * Tạo menu items phẳng với các types: space, label, drive, menu
     */
    private function buildFlatMenuItems($postTypes)
    {
        $items = [];
        $order = 1;

        // Overview
        $items[] = [
            'type' => 'label',
            'label' => Flang::__('overview'),
            'order' => $order++
        ];
        $items[] = [
            'type' => 'menu',
            'id' => 'dashboard',
            'label' => Flang::__('dashboard'),
            'href' => admin_url('home'),
            'permissions' => true,
            'icon' => 'home',
            'order' => $order++,
            'children' => []
        ];

        // Media management
        $items[] = [
            'type' => 'label',
            'label' => Flang::__('media management'),
            'permissions' => [
                'Backend\Files' => 'index'
            ],
            'order' => $order++
        ];
        $items[] = [
            'type' => 'menu',
            'id' => 'files',
            'label' => Flang::__('files manager'),
            'href' => admin_url('files'),
            'permissions' => [
                'Backend\Files' => 'index'
            ],
            'icon' => 'folder',
            'order' => $order++,
            'children' => [
                [
                    'id' => 'files-manager',
                    'label' => Flang::__('files timeline'),
                    'href' => admin_url('files'),
                    'permissions' => [
                        'Backend\Files' => 'index'
                    ],
                    'icon' => 'sliders',
                    'order' => 1
                ]
            ]
        ];

        // Posts management
        $items[] = [
            'type' => 'label',
            'label' => Flang::__('posts management'),
            'permissions' => [
                'Backend\Posts' => 'index'
            ],
            'order' => $order++
        ];
        // Dynamic post types
        $postTypeMenus = $this->getPostTypeMenus($postTypes, $order);
        $items = array_merge($items, $postTypeMenus);
        $order += count($postTypeMenus);


        // Plugins Group
        $pluginMenus = MenuService::getPluginMenus();
        $items[] = [
            'type' => 'label',
            'label' => Flang::__('plugins management'),
            'permissions' => (!empty($pluginMenus) && count($pluginMenus) > 0) ? true : false,
            'order' => $order++
        ];

        // Merge thêm menu từ plugins sử dụng MenuService
        usort($pluginMenus, function($a, $b) {
            return ($a['order'] ?? 0) - ($b['order'] ?? 0);
        });
        foreach ($pluginMenus as $pm) {
            $order++;
            $pm['order'] = $order;
            $items[] = $pm;
        }

        // Users & Permissions
        $items[] = [
            'type' => 'label',
            'label' => Flang::__('users permissions'),
            'permissions' => [
                'Backend\Users' => 'index'
            ],
            'order' => $order++
        ];
        $items[] = [
            'type' => 'menu',
            'id' => 'users',
            'label' => Flang::__('users manager'),
            'href' => admin_url('users/index'),
            'permissions' => [
                'Backend\Users' => 'index'
            ],
            'icon' => 'users-2',
            'order' => $order++,
            'children' => [
                [
                    'id' => 'list-users',
                    'label' => Flang::__('list').' '.Flang::__('users'),
                    'href' => admin_url('users/index'),
                    'permissions' => [
                        'Backend\Users' => 'index'
                    ],
                    'icon' => 'users-2',
                    'order' => 1
                ],
                [
                    'id' => 'add-user',
                    'label' => Flang::__('add').' '.Flang::__('user'),
                    'href' => admin_url('users/add'),
                    'permissions' => [
                        'Backend\Users' => 'add'
                    ],
                    'icon' => 'user-plus',
                    'order' => 2
                ]
            ]
        ];

        // Site management
        $items[] = [
            'type' => 'label',
            'label' => Flang::__('site management'),
            'permissions' => [
                'Backend\Options' => 'index'
            ],
            'order' => $order++
        ];


        $menuSettings =[
            'type' => 'menu',
            'id' => 'settings',
            'label' => Flang::__('site settings'),
            'href' => admin_url('options/index'),
            'permissions' => [
                'Backend\Options' => 'index'
            ],
            'icon' => 'settings',
            'order' => $order++,
            'children' => [
                [
                    'id' => 'index-options',
                    'label' => Flang::__('site settings'),
                    'href' => admin_url('options/index'),
                    'permissions' => [
                        'Backend\Options' => 'index'
                    ],
                    'icon' => 'settings',
                    'order' => 1
                ]
            ]
        ];
        if (defined('APP_DEVELOPMENT') && APP_DEVELOPMENT) {
            $menuSettings['children'][] = [
                'id' => 'lists-options',
                'label' => Flang::__('lists options'),
                'href' => admin_url('options/lists'),
                'permissions' => [
                    'Backend\Options' => 'lists'
                ],
                'icon' => 'list',
                'order' => 2
            ];
        }
        $menuSettings['children'][] = [
            'id' => 'add-options',
            'label' => Flang::__('add option'),
            'href' => admin_url('options/add'),
            'permissions' => [
                'Backend\Options' => 'add'
            ],
            'icon' => 'plus',
            'order' => 3
        ];
        $items[] = $menuSettings;

        $items[] = [
            'type' => 'menu',
            'id' => 'languages',
            'label' => Flang::__('languages'),
            'href' => admin_url('languages'),
            'permissions' => [
                'Backend\Languages' => 'index'
            ],
            'icon' => 'globe',
            'order' => $order++,
            'children' => [
                [
                    'id' => 'list-languages',
                    'label' => Flang::__('list').' '.Flang::__('languages'),
                    'href' => admin_url('languages/index'),
                    'permissions' => [
                        'Backend\Languages' => 'index'
                    ],
                    'icon' => 'settings',
                    'order' => 1
                ],
                [
                    'id' => 'add-language',
                    'label' => Flang::__('add').' '.Flang::__('language'),
                    'href' => admin_url('languages/?showform=true'),
                    'permissions' => [
                        'Backend\Languages' => 'add'
                    ],
                    'icon' => 'plus',
                    'order' => 2
                ]
            ]
        ];

        // Development Group
        $items[] = [
            'type' => 'label',
            'label' => Flang::__('development'),
            'permissions' => [
                'Backend\Libraries' => ['plugins', 'themes', 'index']
            ],
            'order' => $order++
        ];
        $items[] = [
            'type' => 'menu',
            'id' => 'plugins',
            'label' => Flang::__('plugins'),
            'href' => admin_url('libraries/plugins'),
            'permissions' => [
                'Backend\Libraries' => 'plugins'
            ],
            'icon' => 'puzzle',
            'order' => $order++,
            'children' => []
        ];
        $items[] = [
            'type' => 'menu',
            'id' => 'themes',
            'label' => Flang::__('themes'),
            'href' => admin_url('libraries/themes'),
            'permissions' => [
                'Backend\Libraries' => 'themes'
            ],
            'icon' => 'monitor',
            'order' => $order++,
            'children' => []
        ];

        // Thêm menu Backup (top-level, không children), ngang cấp với Development
        $items[] = [
            'type' => 'menu',
            'id' => 'backup',
            'label' => Flang::__("backup & restore"),
            'href' => admin_url('backups/index'),
            'permissions' => [
                'Backend\Backups' => 'index'
            ],
            'icon' => 'database',
            'order' => $order++,
            'children' => [
                [
                    "id" => "backups-settings",
                    "label" => Flang::__("setting backup"),
                    "href" => admin_url('backups/settings'),
                    'permissions' => [
                        'Backend\Backups' => 'settings'
                    ],
                    "icon" => 'settings',
                    "order" => 1
                ],
                [
                    "id" => "backups-index",
                    "label" => Flang::__("list backups"),
                    "href" => admin_url('backups/index'),
                    'permissions' => [
                        'Backend\Backups' => 'index'
                    ],
                    "icon" => 'database',
                    "order" => 2
                ]
            ]
        ];
        $items[] = [
            'type' => 'menu',
            'id' => 'development',
            'label' => Flang::__('development'),
            'href' => 'https://docs.cmsfullform.com',
            'icon' => 'code',
            'order' => $order++,
            'children' => [
            [
                'id' => 'cms-documentation',
                'label' => Flang::__('documentation'),
                'href' => 'https://docs.cmsfullform.com',
                'icon' => 'file-text',
                'order' => 1
            ],
            [
                'id' => 'restful-index',
                'label' => Flang::__('API Keys'),
                'href' => admin_url('restful/index'),
                'icon' => 'key',
                'order' => 2
                ]
            ]
        ];

        //print_r($items);die;

        return $items;
    }


    function getLangSlugFromUrl($url, $supportedLangs)
    {
        $parsedUrl = parse_url($url);
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $segments = explode('/', trim($path, '/'));

        if (!empty($segments) && in_array($segments[0], $supportedLangs)) {
            return '/' . $segments[0];
        }
        return '';
    }

    /**
     * Lấy danh sách menu items cho các post types
     * @param array $postTypes Danh sách post types
     * @param int $startOrder Order bắt đầu
     * @return array Danh sách menu items
     */
    private function getPostTypeMenus($postTypes, $startOrder)
    {
        $items = [];
        $order = $startOrder;
        $currentPostType = $this->getCurrentPostType();

        foreach ($postTypes as $postType) {
            if (isset($postType['status']) && strtolower($postType['status']) === 'active') {
                if (empty($postType['slug']) || empty($postType['menu']) || $postType['menu'] != $postType['slug']) {
                    continue;
                }

                $postTypeName = htmlspecialchars($postType['name']);
                $postTypeSlug = htmlspecialchars($postType['slug']);

                $children = [
                    [
                        'id' => 'list-' . $postTypeSlug,
                        'label' => Flang::__('list') . ' ' . Flang::__($postTypeName),
                        'href' => admin_url('posts/index') . '?type=' . $postTypeSlug,
                        'permissions' => [
                            'Backend\Posts' => 'index'
                        ],
                        'icon' => 'list',
                        'order' => 1
                    ],
                    [
                        'id' => 'add-' . $postTypeSlug,
                        'label' => Flang::__('add') . ' ' . Flang::__($postTypeName),
                        'href' => admin_url('posts/add') . '?type=' . $postTypeSlug,
                        'permissions' => [
                            'Backend\Posts' => 'add'
                        ],
                        'icon' => 'plus',
                        'order' => 2
                    ]
                ];

                // Subtypes
                foreach ($postTypes as $subtype) {
                    if (empty($subtype['name']) || $subtype['menu'] == $subtype['slug']) {
                        continue;
                    }
                    if ($subtype['menu'] == $postType['slug']) {
                        $children[] = [
                            'id' => 'list-' . $subtype['slug'],
                            'label' => Flang::__('list') . ' ' . Flang::__($subtype['name']),
                            'href' => admin_url('posts/index') . '?type=' . $subtype['slug'],
                            'permissions' => [
                                'Backend\Posts' => 'index'
                            ],
                            'icon' => 'database',
                            'order' => count($children) + 1
                        ];
                    }
                }

                // Terms
                $postType['terms'] = is_string($postType['terms']) ? json_decode($postType['terms'], true) : $postType['terms'];
                if (isset($postType['terms']) && is_array($postType['terms'])) {
                    foreach ($postType['terms'] as $term) {
                        if (empty($term['name']) || empty($term['type'])) {
                            continue;
                        }
                        $termType = htmlspecialchars($term['type']);
                        $children[] = [
                            'id' => 'list-' . $postTypeSlug . '-' . $termType,
                            'label' => Flang::__('list') . ' ' . Flang::__($termType) . ' ' . Flang::__($postTypeName),
                            'href' => admin_url('terms/index') . '?posttype=' . $postTypeSlug . '&type=' . $termType,
                            'permissions' => [
                                'Backend\Terms' => 'index'
                            ],
                            'icon' => 'grid',
                            'order' => count($children) + 1
                        ];
                    }
                }

                $items[] = [
                    'type' => 'menu',
                    'id' => $postTypeSlug,
                    'label' => Flang::__($postTypeName),
                    'href' => admin_url('posts/index') . '?type=' . $postTypeSlug,
                    'permissions' => [
                        'Backend\Posts' => 'index'
                    ],
                    'icon' => 'edit',
                    'order' => $order++,
                    'children' => $children,
                    'expanded' => ($currentPostType && trim($currentPostType) === trim($postTypeSlug))
                ];
            }
        }

        return $items;
    }

    /**
     * Xác định posttype hiện tại từ URL parameters
     */
    private function getCurrentPostType()
    {
        // Kiểm tra URL path để xác định context
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
        $parsedUrl = parse_url($currentUrl);
        $path = $parsedUrl['path'] ?? '';
        
        // Nếu đang ở trang posts, check S_GET('type')
        if (preg_match('/\/admin\/posts\//', $path)) {
            if (HAS_GET('type') && !empty(S_GET('type'))) {
                return S_GET('type');
            }
        }
        
        // Nếu đang ở trang terms, check S_GET('posttype') (KHÔNG check S_GET('type'))
        if (preg_match('/\/admin\/terms\//', $path)) {
            if (HAS_GET('posttype') && !empty(S_GET('posttype'))) {
                return S_GET('posttype');
            }
        }
        
                    return null;
    }
}
