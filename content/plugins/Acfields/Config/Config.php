<?php
$fields_system = array(
    [
        "type" => "Text",
        "label" => "Title",
        "field_name" => "title",
        "required" => true,
        "indexdb" => true, //Index for Speed UP Query Filter this column (field)
        "synchronous" => false,
        "max" => 190,
        "position" => 'top',
        "width_value" => 100,
        "width_unit" => '%',
        "order" => 1,
        "lock_edit" => ['type', 'field_name'],
        "lock_delete" => true,
    ],
    [
        "type" => "Text",
        "label" => "Slug URL",
        "field_name" => "slug",
        "unique" => true,
        "required" => true,
        "indexdb" => true, //Index for Speed UP Query Filter this column (field)
        "synchronous" => false,
        "autofill" => "title",
        "autofill_type" => "slug",
        "max" => 190,
        "position" => 'left',
        "width_value" => 100,
        "width_unit" => '%',
        "order" => 1,
        "lock_edit" => ['type', 'field_name'],
        "lock_delete" => true,
    ],
    [
        "type" => "Textarea",
        "label" => "Lang Slug",
        "field_name" => "lang_slug",
        "description" => 'Lang slug of the article',
        "required" => false,
        "indexdb" => false,
        "synchronous" => true,
        "max" => 10000,
        "position" => 'top',
        "width_value" => 100,
        "width_unit" => '%',
        "order" => 1,
        "visibility" => false,
        "lock_edit" => ['type', 'field_name'],
        "lock_delete" => true,
    ],
    [
        "type" => "Select",
        "label" => "Status",
        "field_name" => "status",
        "required" => true,
        "indexdb" => true, //Index for Speed UP Query Filter this column (field)
        "synchronous" => true,
        "options" => [
            ["value" => "active",  "label" => "Active", "is_group" => false],
            ["value" => "pending",  "label" => "Pending", "is_group" => false],
            ["value" => "inactive",  "label" => "Inactive", "is_group" => false],
            ["value" => "schedule",  "label" => "Schedule", "is_group" => false],
            ["value" => "draft",  "label" => "Draft", "is_group" => false],
            ["value" => "suspended",  "label" => "Suspended", "is_group" => false],
            ["value" => "deleted",  "label" => "Deleted", "is_group" => false],
        ],
        "default_value" => "active",
        "max" => 30,
        "position" => 'right',
        "width_value" => 100,
        "width_unit" => '%',
        "order" => 1,
        "lock_edit" => ['type', 'field_name', 'indexdb', 'options'],
        "lock_delete" => true,
    ],
    [
        "type" => "DateTime",
        "label" => "Created at",
        "field_name" => "created_at",
        "required" => true,
        "indexdb" => true, //Index for Speed UP Query Filter this column (field)
        "synchronous" => true,
        "max" => 190,
        "position" => 'right',
        "width_value" => 100,
        "width_unit" => '%',
        "order" => 1,
        "lock_edit" => ['type', 'field_name'],
        "lock_delete" => true,
    ],
    [
        "type" => "DateTime",
        "label" => "Updated at",
        "field_name" => "updated_at",
        "required" => true,
        "indexdb" => true, //Index for Speed UP Query Filter this column (field)
        "synchronous" => false,
        "max" => 190,
        "position" => 'right',
        "width_value" => 100,
        "width_unit" => '%',
        "order" => 1,
        "lock_edit" => ['type', 'field_name'],
        "lock_delete" => true,
    ]
);
$fields_default = array(
    [
        "type" => "Text",
        "label" => "Search Keywords",
        "field_name" => "search_string",
        "autofill" => "title",
        "autofill_type" => "keyword", //slug|keyword|lower|upper|normal
        "description" => 'Search keywords without accents (max 190 Chars)',
        "required" => true,
        "indexdb" => true, //Index for Speed UP Query Filter this column (field)
        "synchronous" => false,
        "max" => 190,
    ],
    [
        "type" => "Textarea",
        "label" => "Description",
        "field_name" => "description",
        "max" => 500,
        "rows" => 3,
    ],
    [
        "type" => "WYSIWYG",
        "label" => "Content",
        "field_name" => "content",
        "max" => 500000,
        "allow_types" => ["jpg", "jpeg", "png", "gif", "webp"],
        "format" => "jpg",
        "quality" => 90,
        "convert_webp" => true,
        "resizes" => [
            ["name" => "medium", "width" => "800", "height" => "1200"],
        ],
        "autocrop" => true,
        "watermark" => true,
    ],
    [
        "type" => "Text",
        "label" => "SEO Title",
        "field_name" => "seo_title",
        "max" => 190,
    ],
    [
        "type" => "Text",
        "label" => "SEO Description",
        "field_name" => "seo_desc",
        "max" => 190,
    ],
    [
        "type" => "Image",
        "label" => "Feature Image",
        "field_name" => "feature",
        "synchronous" => true,
        "allow_types" => ["jpg", "jpeg", "png", "gif", "webp"],
        "format" => "jpg",
        "quality" => 90,
        "convert_webp" => true,
        // "resizes" => [
        //     ["width" => "200", "height" => "200"],
        //     ["width" => "250", "height" => "250"],
        // ], //Add if you want add custom resize for Any Image Field. Or add "resizes" => [] for not use Resizes Default of System
        "autocrop" => true,
        "watermark" => false,
        "position" => 'right',
    ],
    [
        "type" => "User",
        "label" => "Author User ID",
        "synchronous" => true,
        "field_name" => "user_id",
        "required" => true,
        "indexdb" => true, //Index for Speed UP Query Filter this column (field)
        "position" => 'right',
    ],
    [
        "type" => "Number",
        "label" => "Rating AVG",
        "field_name" => "rating_avg",
        "indexdb" => true, //Index for Speed UP Query Filter this column (field)
        "synchronous" => true,
        "placeholder" => "Average",
        "default_value" => 0,
        "min" => 0,
        "max" => 5,
        "step" => 0.1,
        "position" => 'right',
        "width_value" => 33,
    ],
    [
        "type" => "Number",
        "label" => "Rating Count",
        "field_name" => "rating_count",
        "indexdb" => true, //Index for Speed UP Query Filter this column (field)
        "synchronous" => true,
        "placeholder" => "Count",
        "default_value" => 0,
        "min" => 0,
        "step" => 1,
        "width_value" => 50,
        "position" => 'right',
        "width_value" => 33,
    ],
    [
        "type" => "Number",
        "label" => "Rating Total",
        "field_name" => "rating_total",
        "synchronous" => true,
        "placeholder" => "Total",
        "default_value" => 0,
        "min" => 0,
        "step" => 1,
        "width_value" => 50,
        "position" => 'right',
        "width_value" => 33,
    ],
    [
        "type" => "Number",
        "label" => "Views Day",
        "field_name" => "views_day",
        "indexdb" => true, //Index for Speed UP Query Filter this column (field)
        "default_value" => 0,
        "min" => 0,
        "step" => 1,
        "width_value" => 33,
        "position" => 'right',
    ],
    [
        "type" => "Number",
        "label" => "Views Week",
        "field_name" => "views_week",
        "indexdb" => true,
        "placeholder" => "0",
        "default_value" => 0,
        "min" => 0,
        "step" => 1,
        "width_value" => 33,
        "position" => 'right',
    ],
    [
        "type" => "Number",
        "label" => "Views",
        "field_name" => "views",
        "indexdb" => true,
        "placeholder" => "0",
        "default_value" => 0,
        "min" => 0,
        "step" => 1,
        "width_value" => 33,
        "position" => 'right',
    ]
);
$fields_default = array_merge($fields_system, $fields_default);

//Move created_at and updated_at to the end of the array
foreach ($fields_default as $key => $field) {
    if ($field['field_name'] == 'created_at' || $field['field_name'] == 'updated_at') {
        unset($fields_default[$key]);
        $fields_default[] = $field;
    }
}

return array(
    'plugin' => array(
        'name' => 'Advanced Custom Fields',
        'version' => '1.0.1',
        'author' => 'CMS Team',
        'description' => 'Advanced Custom Fields là plugin quản lý các trường tùy chỉnh cho các posttype cụ thể',
        'buttons' => [
            [
                'label' => 'Settings',
                'url' => admin_url('acfields/settings'),
                'icon' => 'settings'
            ],
            [
                'label' => 'Start Setup',
                'url' => admin_url('acfields/add'),
                'icon' => 'plus'
            ],
        ],
    ),
    'fields_system' => $fields_system,
    'fields_default' => $fields_default
);
