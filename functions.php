<?php
if (!defined("ABSPATH")) exit;

add_action("after_setup_theme", function() {
    add_theme_support("title-tag");
    add_theme_support("post-thumbnails");
    add_theme_support("html5", ["search-form", "comment-form", "comment-list", "gallery", "caption"]);
});

add_action("wp_enqueue_scripts", function() {
    $css_path = get_template_directory() . "/assets/css/main.css";
    $css_ver  = file_exists($css_path) ? filemtime($css_path) : "1.0.0";
    
    wp_enqueue_style("re-main", get_template_directory_uri() . "/assets/css/main.css", [], $css_ver);
    wp_enqueue_script("re-main", get_template_directory_uri() . "/assets/js/main.js", ["jquery"], "1.0.0", true);
});

add_action("init", function() {
    register_post_type("property", [
        "labels" => ["name" => "Нерухомість", "singular_name" => "Об'єкт", "add_new" => "Додати об'єкт"],
        "public" => true, "has_archive" => true, "rewrite" => ["slug" => "properties"],
        "supports" => ["title", "editor", "thumbnail", "excerpt"],
        "menu_icon" => "dashicons-admin-home", "show_in_rest" => true,
    ]);

    register_taxonomy("city", ["property"], [
        "labels" => ["name" => "Міста", "singular_name" => "Місто"],
        "hierarchical" => true, "show_in_rest" => true, "rewrite" => ["slug" => "city"],
    ]);

    register_taxonomy("property_type", ["property"], [
        "labels" => ["name" => "Типи нерухомості", "singular_name" => "Тип"],
        "hierarchical" => true, "show_in_rest" => true, "rewrite" => ["slug" => "type"],
    ]);
});

// 4. Програмна реєстрація полів ACF Pro для CPT 'property'
add_action('acf/init', function() {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group([
            'key' => 'group_property_details',
            'title' => 'Деталі об\'єкта',
            'fields' => [
                [
                    'key' => 'field_property_price',
                    'label' => 'Ціна ($)',
                    'name' => 'price',
                    'type' => 'number',
                    'required' => 1,
                    'placeholder' => '150000',
                ],
                [
                    'key' => 'field_property_area',
                    'label' => 'Площа (м²)',
                    'name' => 'area',
                    'type' => 'number',
                    'placeholder' => '75',
                ],
                [
                    'key' => 'field_property_address',
                    'label' => 'Адреса',
                    'name' => 'address',
                    'type' => 'text',
                    'placeholder' => 'вул. Хрещатик, 1',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'property',
                    ],
                ],
            ],
        ]);
    }
});
