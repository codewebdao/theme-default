<?php

// Check if PATH_ROOT is not defined, prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

if (!function_exists('base_url')) {
    exit('Need load Uri helper first.');
}

if (!function_exists('posttype_default')) {
    function posttype_default() {
        global $posttype_default;
        if (empty($posttype_default)) {
            $posttype_default = option('default_posttype', APP_LANG);
            if ($posttype_default && posttype_lang_exists($posttype_default, APP_LANG)) {
                $posttype_default = $posttype_default;
            } else {
                $posttype_default = '';
            }
        }
        return $posttype_default;
    }
}

if (!function_exists('link_posts')) {
    function link_posts($slug = '', $posttype = 'posts', $lang = APP_LANG) {
        $posttype_default = posttype_default();
        if ($posttype_default == $posttype || 'pages' == $posttype) {
            $posttypePrefix = '';
        }else{
            $posttypePrefix = $posttype;
        }
        return base_url($posttypePrefix . '/' . ltrim($slug, '/'), $lang);
    }
}

if (!function_exists('link_page')) {
    function link_page($slug = '', $lang = APP_LANG) {
        $posttype_default = posttype_default();
        if ($posttype_default == 'pages') {
            return base_url($slug, $lang);
        }
        return base_url('pages/' . $slug, $lang);
    }
}

if (!function_exists('link_terms')) {
    function link_terms($taxonomy = 'category', $slug = '', $posttype = 'posts', $lang = APP_LANG) {
        $posttype_default = posttype_default();
        return base_url( ($posttype_default == $posttype ? '' : $posttype) . '/' . $taxonomy . '/' . ltrim($slug, '/'), $lang);
    }
}

if (!function_exists('link_category')) {
    function link_category($slug = '', $posttype = 'posts', $lang = APP_LANG) {
        return link_terms('category', $slug, $posttype, $lang);
    }
}

if (!function_exists('link_tag')) {
    function link_tag($slug = '', $posttype = 'posts', $lang = APP_LANG) {
        return link_terms('tag', $slug, $posttype, $lang);
    }
}

if (!function_exists('link_author')) {
    function link_author($slug = '', $posttype = 'posts', $lang = APP_LANG) {
        return base_url('author/' . ltrim($slug, '/'), $lang);
    }
}

if (!function_exists('link_search')) {
    function link_search($query = '', $posttype = 'posts', $lang = APP_LANG) {
        if ($posttype == 'posts') {
            if (!empty($query)){
                return base_url('search', $lang).'?q='.urlencode($query);
            }
            return base_url('search', $lang);
        }else{
            if (!empty($query)){
                return base_url('search/'.$posttype, $lang).'?q='.urlencode($query);
            }
            return base_url('search/'.$posttype, $lang);
        }
    }
}

