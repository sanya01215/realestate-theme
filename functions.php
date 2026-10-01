<?php
/**
 * Функції та налаштування теми "Real Estate Catalog Theme"
 *
 * @package RealEstateCatalog
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 1. Базові налаштування теми
 */
add_action('after_setup_theme', function () {
    // Підтримка тегу <title>
    add_theme_support('title-tag');

    // Підтримка мініатюр записів (Featured Image)
    add_theme_support('post-thumbnails');

    // Підтримка HTML5 розмітки для стандартних компонентів
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);
});

/**
 * 2. Підключення скриптів та стилів
 * Використовуємо Vanilla JS (без залежності від jQuery) та передаємо AJAX конфігурацію
 */
add_action('wp_enqueue_scripts', function () {
    $css_path = get_template_directory() . '/assets/css/main.css';
    $css_ver  = file_exists($css_path) ? filemtime($css_path) : '1.0.0';

    // Головні стилі (Tailwind CSS збірка)
    wp_enqueue_style(
        're-main-style',
        get_template_directory_uri() . '/assets/css/main.css',
        [],
        $css_ver
    );

    $js_path = get_template_directory() . '/assets/js/main.js';
    $js_ver  = file_exists($js_path) ? filemtime($js_path) : '1.0.0';

    // Головний скрипт теми (Vanilla JS)
    wp_enqueue_script(
        're-main-script',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        $js_ver,
        true
    );

    // Локалізація параметрів для AJAX запитів
    wp_localize_script('re-main-script', 're_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('re_filter_nonce'),
    ]);
});

/**
 * 3. Реєстрація Custom Post Type "property" та таксономій "city", "property_type"
 */
add_action('init', function () {
    // Реєстрація CPT "property" (Нерухомість)
    register_post_type('property', [
        'labels' => [
            'name'               => 'Нерухомість',
            'singular_name'      => 'Об\'єкт нерухомості',
            'add_new'            => 'Додати об\'єкт',
            'add_new_item'       => 'Додати новий об\'єкт нерухомості',
            'edit_item'          => 'Редагувати об\'єкт',
            'new_item'           => 'Новий об\'єкт',
            'view_item'          => 'Переглянути об\'єкт',
            'search_items'       => 'Шукати об\'єкти',
            'not_found'          => 'Об\'єктів не знайдено',
            'not_found_in_trash' => 'У кошику об\'єктів не знайдено',
            'menu_name'          => 'Нерухомість',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'properties'],
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
        'menu_icon'    => 'dashicons-admin-home',
        'show_in_rest' => true,
    ]);

    // Реєстрація таксономії "city" (Міста)
    register_taxonomy('city', ['property'], [
        'labels' => [
            'name'          => 'Міста',
            'singular_name' => 'Місто',
            'search_items'  => 'Шукати міста',
            'all_items'     => 'Всі міста',
            'edit_item'     => 'Редагувати місто',
            'update_item'   => 'Оновити місто',
            'add_new_item'  => 'Додати нове місто',
            'new_item_name' => 'Назва нового міста',
            'menu_name'     => 'Міста',
        ],
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'city'],
    ]);

    // Реєстрація таксономії "property_type" (Типи нерухомості)
    register_taxonomy('property_type', ['property'], [
        'labels' => [
            'name'          => 'Типи нерухомості',
            'singular_name' => 'Тип нерухомості',
            'search_items'  => 'Шукати типи нерухомості',
            'all_items'     => 'Всі типи',
            'edit_item'     => 'Редагувати тип',
            'update_item'   => 'Оновити тип',
            'add_new_item'  => 'Додати новий тип',
            'new_item_name' => 'Назва нового типу',
            'menu_name'     => 'Типи нерухомості',
        ],
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'property-type'],
    ]);
});

/**
 * 4. Очищення дублікатів термінів, створення канонічних латинських термінів та прив'язка
 */
add_action('init', function () {
    // Канонічні терміни відповідно до паспорту проєкту
    $canonical_terms = [
        'city' => [
            'vasylkiv'                    => 'Васильків',
            'petropavlivska-borshchahivka' => 'Петропавлівська Борщагівка',
            'vyshneve'                    => 'Вишневе',
            'kyiv'                        => 'Київ',
        ],
        'property_type' => [
            'house'     => 'Будинок',
            'apartment' => 'Квартира',
            'land'      => 'Ділянка',
            'townhouse' => 'Таунхаус',
        ],
    ];

    foreach ($canonical_terms as $taxonomy => $terms_list) {
        foreach ($terms_list as $target_slug => $target_name) {
            // Отримуємо або створюємо канонічний термін
            $canonical_term = get_term_by('slug', $target_slug, $taxonomy);

            if (!$canonical_term) {
                // Перевіряємо, можливо існує термін з такою ж назвою
                $existing_by_name = get_term_by('name', $target_name, $taxonomy);
                if ($existing_by_name) {
                    wp_update_term($existing_by_name->term_id, $taxonomy, [
                        'slug' => $target_slug,
                        'name' => $target_name,
                    ]);
                    $canonical_term = get_term($existing_by_name->term_id, $taxonomy);
                } else {
                    $inserted = wp_insert_term($target_name, $taxonomy, ['slug' => $target_slug]);
                    if (!is_wp_error($inserted) && isset($inserted['term_id'])) {
                        $canonical_term = get_term($inserted['term_id'], $taxonomy);
                    }
                }
            }

            if (!$canonical_term || is_wp_error($canonical_term)) {
                continue;
            }

            $canonical_id = (int) $canonical_term->term_id;

            // Шукаємо дублікати: інші терміни з такою ж назвою або схожим слагом
            $all_taxonomy_terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ]);

            if (!empty($all_taxonomy_terms) && !is_wp_error($all_taxonomy_terms)) {
                foreach ($all_taxonomy_terms as $term_item) {
                    // Якщо це той самий канонічний термін — пропускаємо
                    if ($term_item->term_id === $canonical_id) {
                        continue;
                    }

                    // Якщо назва збігається або slug є кириличним/суфіксним дублікатом
                    $is_same_name = (mb_strtolower(trim($term_item->name)) === mb_strtolower(trim($target_name)));
                    $is_dup_slug  = (strpos($term_item->slug, $target_slug) === 0);

                    if ($is_same_name || $is_dup_slug) {
                        // Знаходимо всі пости, прив'язані до дубліката
                        $posts_with_dup = get_posts([
                            'post_type'      => 'property',
                            'posts_per_page' => -1,
                            'post_status'    => 'any',
                            'fields'         => 'ids',
                            'tax_query'      => [
                                [
                                    'taxonomy' => $taxonomy,
                                    'field'    => 'term_id',
                                    'terms'    => $term_item->term_id,
                                ],
                            ],
                        ]);

                        // Переприв'язуємо пости до канонічного терміна
                        foreach ($posts_with_dup as $post_id) {
                            wp_set_object_terms($post_id, [$canonical_id], $taxonomy, true);
                            wp_remove_object_terms($post_id, [$term_item->term_id], $taxonomy);
                        }

                        // Видаляємо дублікат терміна з бази
                        wp_delete_term($term_item->term_id, $taxonomy);
                    }
                }
            }
        }
    }

    // Прив'язка для постів, у яких терміни взагалі не встановлені
    $properties = get_posts([
        'post_type'      => 'property',
        'posts_per_page' => -1,
        'post_status'    => 'any',
    ]);

    foreach ($properties as $prop) {
        $has_city = has_term('', 'city', $prop->ID);
        $has_type = has_term('', 'property_type', $prop->ID);

        if (!$has_city || !$has_type) {
            $title = $prop->post_title;

            // Визначення міста
            if (!$has_city) {
                if (mb_stripos($title, 'Васильків') !== false) {
                    wp_set_object_terms($prop->ID, 'vasylkiv', 'city');
                } elseif (mb_stripos($title, 'Петропавлівськ') !== false || mb_stripos($title, 'Борщагівк') !== false) {
                    wp_set_object_terms($prop->ID, 'petropavlivska-borshchahivka', 'city');
                } elseif (mb_stripos($title, 'Вишнев') !== false) {
                    wp_set_object_terms($prop->ID, 'vyshneve', 'city');
                } elseif (mb_stripos($title, 'Київ') !== false || mb_stripos($title, 'Києв') !== false) {
                    wp_set_object_terms($prop->ID, 'kyiv', 'city');
                }
            }

            // Визначення типу
            if (!$has_type) {
                if (mb_stripos($title, 'Квартир') !== false) {
                    wp_set_object_terms($prop->ID, 'apartment', 'property_type');
                } elseif (mb_stripos($title, 'Ділян') !== false) {
                    wp_set_object_terms($prop->ID, 'land', 'property_type');
                } elseif (mb_stripos($title, 'Таунхаус') !== false) {
                    wp_set_object_terms($prop->ID, 'townhouse', 'property_type');
                } else {
                    wp_set_object_terms($prop->ID, 'house', 'property_type');
                }
            }
        }
    }
}, 20);

/**
 * 5. Реєстрація кастомних полів ACF Pro через PHP
 */
add_action('acf/init', function () {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group([
            'key'                   => 'group_property_details',
            'title'                 => 'Деталі об\'єкта нерухомості',
            'fields'                => [
                [
                    'key'               => 'field_property_price',
                    'label'             => 'Ціна ($)',
                    'name'              => 'price',
                    'type'              => 'number',
                    'instructions'      => 'Вкажіть вартість об\'єкта у доларах США',
                    'required'          => 1,
                    'min'               => 0,
                    'step'              => 1,
                    'placeholder'       => '120000',
                ],
                [
                    'key'               => 'field_property_area',
                    'label'             => 'Площа (м²)',
                    'name'              => 'area',
                    'type'              => 'number',
                    'instructions'      => 'Вкажіть загальну площу у квадратних метрах',
                    'required'          => 0,
                    'min'               => 0,
                    'step'              => 'any',
                    'placeholder'       => '85',
                ],
                [
                    'key'               => 'field_property_address',
                    'label'             => 'Адреса',
                    'name'              => 'address',
                    'type'              => 'text',
                    'instructions'      => 'Вкажіть вулицю та номер будинку',
                    'required'          => 0,
                    'placeholder'       => 'вул. Соборна, 10',
                ],
            ],
            'location'              => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'property',
                    ],
                ],
            ],
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'active'                => true,
        ]);
    }
});

/**
 * 6. PHP-обробник AJAX-фільтрації нерухомості
 */
function re_filter_properties_handler() {
    // Перевірка nonce для безпеки
    check_ajax_referer('re_filter_nonce', 'nonce');

    $args = [
        'post_type'      => 'property',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => ['relation' => 'AND'],
        'tax_query'      => ['relation' => 'AND'],
    ];

    // Фільтр за лише обраними об'єктами
    if (!empty($_POST['only_favorites']) && $_POST['only_favorites'] === '1') {
        $favorite_ids = !empty($_POST['favorite_ids']) ? array_map('absint', explode(',', $_POST['favorite_ids'])) : [];
        if (!empty($favorite_ids)) {
            $args['post__in'] = $favorite_ids;
        } else {
            // Якщо обраних немає — повертаємо відповідь з нулем
            wp_send_json_success([
                'html'  => '<div class="col-span-full bg-white p-8 text-center rounded-xl border border-slate-200"><p class="text-slate-500 font-medium">Ваш список обраного порожній. Додайте об\'єкти за допомогою сердечка ❤️</p></div>',
                'count' => 0,
            ]);
            return;
        }
    }

    // Фільтр за максимальною ціною
    if (!empty($_POST['max_price'])) {
        $max_price = absint($_POST['max_price']);
        if ($max_price > 0) {
            $args['meta_query'][] = [
                'key'     => 'price',
                'value'   => $max_price,
                'type'    => 'NUMERIC',
                'compare' => '<=',
            ];
        }
    }

    // Фільтр за містом (латинський slug)
    if (!empty($_POST['city'])) {
        $city_slug = sanitize_title(wp_unslash($_POST['city']));
        if (!empty($city_slug)) {
            $args['tax_query'][] = [
                'taxonomy' => 'city',
                'field'    => 'slug',
                'terms'    => $city_slug,
            ];
        }
    }

    // Фільтр за типом нерухомості (латинський slug)
    if (!empty($_POST['property_type'])) {
        $type_slug = sanitize_title(wp_unslash($_POST['property_type']));
        if (!empty($type_slug)) {
            $args['tax_query'][] = [
                'taxonomy' => 'property_type',
                'field'    => 'slug',
                'terms'    => $type_slug,
            ];
        }
    }

    // Сортування результатів
    $sort = !empty($_POST['sort']) ? sanitize_text_field(wp_unslash($_POST['sort'])) : 'date_desc';

    switch ($sort) {
        case 'price_asc':
            $args['meta_key'] = 'price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'ASC';
            break;
        case 'price_desc':
            $args['meta_key'] = 'price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
        case 'date_desc':
        default:
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
            break;
    }

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) :
        while ($query->have_posts()) :
            $query->the_post();
            get_template_part('template-parts/content', 'property-card');
        endwhile;
        wp_reset_postdata();
    else :
        ?>
        <div class="col-span-full bg-white p-8 text-center rounded-xl border border-slate-200">
            <p class="text-slate-500 font-medium">За вашим запитом об'єктів не знайдено.</p>
        </div>
        <?php
    endif;

    $html = ob_get_clean();

    wp_send_json_success([
        'html'  => $html,
        'count' => (int) $query->found_posts,
    ]);
}
add_action('wp_ajax_filter_properties', 're_filter_properties_handler');
add_action('wp_ajax_nopriv_filter_properties', 're_filter_properties_handler');