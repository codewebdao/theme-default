<?php

namespace App\Blocks\Backend\Sidebar\MenuItem;

/**
 * MenuItemFactory - Factory để tạo menu items
 */
class MenuItemFactory
{
    /**
     * Tạo menu item từ array data
     */
    public static function create(array $data): ?BaseMenuItem
    {
        $type = $data['type'] ?? 'menu';

        switch ($type) {
            case 'menu':
                $item = new MenuItem($data);
                return $item->validate() ? $item : null;
                
            case 'label':
                $item = new LabelItem($data);
                return $item->validate() ? $item : null;
                
            case 'space':
                $item = new SpaceItem($data);
                return $item->validate() ? $item : null;
                
            case 'drive':
                $item = new DriveItem($data);
                return $item->validate() ? $item : null;
                
            case 'hr':
                $item = new HRItem($data);
                return $item->validate() ? $item : null;
                
            default:
                return null;
        }
    }

    /**
     * Tạo nhiều menu items từ array
     */
    public static function createMultiple(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $created = self::create($item);
            if ($created) {
                $result[] = $created;
            }
        }
        return $result;
    }
}
