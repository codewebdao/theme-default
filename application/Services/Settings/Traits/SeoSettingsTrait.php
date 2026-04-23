<?php

namespace App\Services\Settings\Traits;

/**
 * SEO settings: General & Titles, SEO General & Local, Sitemap, Redirects, Breadcrumbs & Connect.
 * Lưu trữ: THEO TỪNG NGÔN NGỮ (phần lớn).
 *
 * @package App\Services\Settings\Traits
 */
trait SeoSettingsTrait
{
    public function getSeoSettingsGroup(): array
    {
        return [
            'id' => 'seo',
            'icon' => 'search',
            'title' => __('SEO & Site Meta'),
            'description' => __('Search engine optimization and sitemap'),
            'detail' => __('Meta, titles, local SEO, sitemap, redirects and breadcrumbs'),
            'url' => admin_url('settings/seo'),
            'tabs' => [
                ['id' => 'general_titles', 'label' => __('General & Titles')],
                ['id' => 'seo_local', 'label' => __('SEO General & Local')],
                ['id' => 'sitemap', 'label' => __('Sitemap')],
                ['id' => 'redirects', 'label' => __('Redirects')],
                ['id' => 'breadcrumbs_connect', 'label' => __('Breadcrumbs & Connect')],
            ],
        ];
    }

    /**
     * @return array{tabs: array, fields: array}
     */
    public function getSeoSettings(): array
    {
        $tabs = [
            forms_tab('general_titles', __('General & Titles'), ['icon' => 'tag']),
            forms_tab('seo_local', __('SEO General & Local'), ['icon' => 'map-pin']),
            forms_tab('sitemap', __('Sitemap'), ['icon' => 'file-text']),
            forms_tab('redirects', __('Redirects'), ['icon' => 'external-link']),
            forms_tab('breadcrumbs_connect', __('Breadcrumbs & Connect'), ['icon' => 'git-branch']),
        ];

        $fields = [
            // TAB 1: General & Titles – enable_* lưu all; title/meta/og theo ngôn ngữ
            forms_field('boolean', 'enable_index', __('Allow indexing'), [
                'tab' => 'general_titles', 'default_value' => true, 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'enable_toc', __('Table of Contents'), [
                'tab' => 'general_titles', 'default_value' => false, 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'seo_global_noindex', __('Global NoIndex'), [
                'tab' => 'general_titles', 'default_value' => false, 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('text', 'seo_title_pattern', __('Default Title Pattern'), [
                'tab' => 'general_titles', 'default_value' => '{post_title} | {site_name}', 'placeholder' => '{post_title} | {site_name}', 'width_value' => 100,
            ]),
            forms_field('textarea', 'seo_meta_description', __('Default Meta Description'), [
                'tab' => 'general_titles', 'rows' => 3, 'width_value' => 100,
            ]),
            forms_field('image', 'seo_default_og_image', __('Default OG Image'), [
                'tab' => 'general_titles', 'width_value' => 50,
            ]),
            forms_field('boolean', 'auto_noindex', __('Empty Taxonomy auto noindex'), [
                'tab' => 'general_titles', 'default_value' => true, 'width_value' => 50, 'storage_lang' => 'all',
            ]),
            // TAB 2: SEO General & Local – options all; local business theo ngôn ngữ
            forms_field('boolean', 'remove_category_base', __('Remove Category Base'), [
                'tab' => 'seo_local', 'default_value' => false, 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'external_links_nofollow', __('External Links Nofollow'), [
                'tab' => 'seo_local', 'default_value' => false, 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'image_links_nofollow', __('Image Links Nofollow'), [
                'tab' => 'seo_local', 'default_value' => false, 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'external_links_target', __('External Links Target _blank'), [
                'tab' => 'seo_local', 'default_value' => true, 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'canonical_control', __('Canonical Control'), [
                'tab' => 'seo_local', 'default_value' => true, 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('text', 'company', __('Local: Tên'), [
                'tab' => 'seo_local', 'placeholder' => __('Company name'), 'width_value' => 50,
            ]),
            forms_field('textarea', 'site_address', __('Local: Địa chỉ'), [
                'tab' => 'seo_local', 'rows' => 2, 'width_value' => 50,
            ]),
            forms_field('text', 'site_phone', __('Local: Số điện thoại'), ['tab' => 'seo_local', 'width_value' => 33]),
            forms_field('text', 'site_email', __('Local: Email liên hệ'), ['tab' => 'seo_local', 'width_value' => 33]),
            forms_field('text', 'site_url', __('Local: Website URL'), [
                'tab' => 'seo_local', 'placeholder' => 'https://example.com', 'width_value' => 33,
            ]),
            forms_field('text', 'site_logo', __('Local: Logo (URL)'), ['tab' => 'seo_local', 'width_value' => 50]),
            forms_field('textarea', 'site_open_hours', __('Local: Giờ mở cửa'), [
                'tab' => 'seo_local', 'rows' => 3, 'width_value' => 50,
            ]),
            forms_field('textarea', 'site_socials', __('Local: Social profiles (SameAs)'), [
                'tab' => 'seo_local', 'rows' => 3, 'width_value' => 50,
            ]),
            // TAB 3: Sitemap – toàn bộ all
            forms_field('boolean', 'enable_sitemap', __('Sitemap Index'), [
                'tab' => 'sitemap', 'default_value' => true, 'width_value' => 25, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'enable_sitemap_posts', __('Posts Sitemap'), [
                'tab' => 'sitemap', 'default_value' => true, 'width_value' => 25, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'enable_sitemap_pages', __('Pages Sitemap'), [
                'tab' => 'sitemap', 'default_value' => true, 'width_value' => 25, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'enable_sitemap_categories', __('Categories Sitemap'), [
                'tab' => 'sitemap', 'default_value' => true, 'width_value' => 25, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'enable_sitemap_authors', __('Authors Sitemap'), [
                'tab' => 'sitemap', 'default_value' => false, 'width_value' => 25, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'enable_sitemap_local', __('Local Sitemap'), [
                'tab' => 'sitemap', 'default_value' => false, 'width_value' => 25, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'enable_sitemap_image', __('Image Sitemap'), [
                'tab' => 'sitemap', 'default_value' => true, 'width_value' => 25, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'auto_exclude_noindex', __('Exclude Noindex'), [
                'tab' => 'sitemap', 'default_value' => true, 'width_value' => 50, 'storage_lang' => 'all',
            ]),
            forms_field('checkbox', 'sitemap_posttypes', __('Include Post Types'), [
                'tab' => 'sitemap', 'options' => $this->getPosttypesOptions(), 'default_value' => ['posts', 'pages'], 'width_value' => 50, 'storage_lang' => 'all',
            ]),
            forms_field('text', 'schema_search_url', __('Sitelinks search URL (JSON-LD)'), [
                'tab' => 'sitemap',
                'description' => __('Full URL with literal ?...={search_term_string}'),
                'placeholder' => (function_exists('link_search') ? rtrim(link_search(''), '/') : rtrim(base_url('search'), '/')) . '?q={search_term_string}',
                'width_value' => 100, 'storage_lang' => 'all',
            ]),
            forms_field('textarea', 'robots_txt_content', __('Robots.txt Content'), [
                'tab' => 'sitemap', 'rows' => 8, 'default_value' => "User-agent: *\nDisallow: /admin/\nSitemap: {site_url}/sitemap.xml", 'width_value' => 100, 'storage_lang' => 'all',
            ]),
            // TAB 4: Redirects – all
            forms_field('textarea', 'redirect_url', __('Redirect rules'), [
                'tab' => 'redirects', 'rows' => 8, 'placeholder' => "/old-page|/new-page|301", 'width_value' => 100, 'storage_lang' => 'all',
            ]),
            forms_field('select', 'redirect_status', __('Default Redirect Type'), [
                'tab' => 'redirects',
                'options' => [['value' => '301', 'label' => '301'], ['value' => '302', 'label' => '302'], ['value' => '307', 'label' => '307'], ['value' => '308', 'label' => '308']],
                'default_value' => '301', 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('text', 'redirect_regex', __('Regex Redirect'), [
                'tab' => 'redirects', 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'auto_redirect', __('Auto Redirect when slug changes'), [
                'tab' => 'redirects', 'default_value' => true, 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            // TAB 5: Breadcrumbs & Connect – all
            forms_field('boolean', 'enable_breadcrumbs', __('Breadcrumbs Toggle'), [
                'tab' => 'breadcrumbs_connect', 'default_value' => true, 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('select', 'breadcrumbs_struct', __('Breadcrumb Structure'), [
                'tab' => 'breadcrumbs_connect',
                'options' => [['value' => 'home_cat_post', 'label' => __('Home → Category → Post')], ['value' => 'home_post', 'label' => __('Home → Post')]],
                'default_value' => 'home_cat_post', 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'schema_breadcrumbs', __('Breadcrumb Schema'), [
                'tab' => 'breadcrumbs_connect', 'default_value' => true, 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('text', 'breadcrumb_separator', __('Breadcrumb Separator'), [
                'tab' => 'breadcrumbs_connect', 'default_value' => ' › ', 'placeholder' => ' › ', 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'enable_schema_markup', __('Enable Schema Markup'), [
                'tab' => 'breadcrumbs_connect', 'default_value' => true, 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('select', 'schema_type', __('Default Schema Type'), [
                'tab' => 'breadcrumbs_connect',
                'options' => [['value' => 'Article', 'label' => 'Article'], ['value' => 'BlogPosting', 'label' => 'Blog Posting'], ['value' => 'Product', 'label' => 'Product'], ['value' => 'Organization', 'label' => 'Organization']],
                'default_value' => 'Article', 'width_value' => 33, 'storage_lang' => 'all',
            ]),
            forms_field('select', 'permalink_structure', __('Permalink Structure'), [
                'tab' => 'breadcrumbs_connect',
                'options' => [['value' => '/{slug}/', 'label' => '/{slug}/'], ['value' => '/{year}/{month}/{slug}/', 'label' => '/{year}/{month}/{slug}/'], ['value' => '/{posttype}/{slug}/', 'label' => '/{posttype}/{slug}/'], ['value' => '/{id}/{slug}/', 'label' => '/{id}/{slug}/']],
                'default_value' => '/{slug}/', 'width_value' => 50, 'storage_lang' => 'all',
            ]),
            forms_field('boolean', 'trailing_slash', __('Trailing Slash'), [
                'tab' => 'breadcrumbs_connect', 'default_value' => true, 'width_value' => 33, 'storage_lang' => 'all',
            ]),
        ];

        return ['tabs' => $tabs, 'fields' => $fields];
    }

    /** @return array<array{value: string, label: string}> */
    protected function getPosttypesOptions(): array
    {
        try {
            if (function_exists('posttype_active')) {
                $posttypes = posttype_active();
                $options = [];
                foreach ($posttypes as $posttype) {
                    $options[] = ['value' => $posttype['slug'] ?? '', 'label' => $posttype['name'] ?? ''];
                }
                if (!empty($options)) {
                    return $options;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return [['value' => 'posts', 'label' => 'Posts'], ['value' => 'pages', 'label' => 'Pages']];
    }
}
