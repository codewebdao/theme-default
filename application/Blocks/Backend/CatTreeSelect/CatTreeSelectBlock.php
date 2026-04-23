<?php

namespace App\Blocks\Backend\CatTreeSelect;
use App\Models\TermsModel;
use System\Core\BaseBlock;

class CatTreeSelectBlock extends BaseBlock
{

    public function __construct()
    {
        $this->setLabel('Backend CatTreeSelect Block');
        $this->setName('Backend\CatTreeSelect');
        $this->setProps([
            'layout'      => 'default',
            'posttype'    => 'post',
            'type'        => 'category',
            'lang'        => APP_LANG,
            'title'       => 'Category',
            'active'      => [],
        ]);
    }

    // đây là function xử lý data bắt buộc phải có
    public function handleData()
    {   $props = $this->getProps();
        $termsModel = new TermsModel();
        $termsList = $termsModel->getTermsByTypeAndPostTypeAndLang($props['posttype'], $props['type'], $props['lang']);
        $data['title'] = $props['title'] ?? 'Category';
        $data['terms'] = $this->treeTerm($termsList);
        $data['active'] = $props['active'];
        return $data;
    }

    private function treeTerm($terms) {
        $result = [];
        $tree = [];
            
        // Step 1: Initialize each term with a children array
        foreach ($terms as $item) {
            $item['children'] = [];
            $result[$item['id']] = $item;
        }
    
        // Step 2: Assign children to their respective parents
        foreach ($result as $id => &$node) {
            if (!empty($node['parent'])) {
                $parent_id = $node['parent'];
                if (isset($result[$parent_id])) {
                    $result[$parent_id]['children'][] = &$node;
                    $node['parent_name'] = $result[$parent_id]['name'];
                } else {
                    // Parent not found, treat this node as a root node
                    $tree[] = &$node;
                }
            } else {
                // No parent, it's a root node
                $tree[] = &$node;
            }
        }
        unset($node); // Break the reference with the last element
    
        return $tree;
    }
}
