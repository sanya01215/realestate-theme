<?php
/**
 * Шаблон окремої картки об'єкта нерухомості
 *
 * @package RealEstateCatalog
 */

$post_id = get_the_ID();

// Отримуємо значення кастомних полів ACF
$price   = function_exists('get_field') ? get_field('price', $post_id) : get_post_meta($post_id, 'price', true);
$area    = function_exists('get_field') ? get_field('area', $post_id) : get_post_meta($post_id, 'area', true);
$address = function_exists('get_field') ? get_field('address', $post_id) : get_post_meta($post_id, 'address', true);

// Отримуємо терміни для відображення міток
$property_types = get_the_terms($post_id, 'property_type');
$cities         = get_the_terms($post_id, 'city');

$type_name = (!empty($property_types) && !is_wp_error($property_types)) ? $property_types[0]->name : '';
$city_name = (!empty($cities) && !is_wp_error($cities)) ? $cities[0]->name : '';
?>

<article id="post-<?php echo esc_attr($post_id); ?>" <?php post_class('bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition duration-200 group flex flex-col relative'); ?>>
    <!-- Верхня частина: Зображення, мітки та кнопка "Обране" -->
    <div class="relative aspect-[16/10] bg-slate-100 overflow-hidden">
        <a href="<?php the_permalink(); ?>" class="block w-full h-full">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('medium_large', [
                    'class' => 'w-full h-full object-cover group-hover:scale-105 transition duration-300 ease-out',
                    'alt'   => the_title_attribute(['echo' => false]),
                ]); ?>
            <?php else : ?>
                <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm font-medium">
                    Немає фото
                </div>
            <?php endif; ?>
        </a>

        <!-- Бейджі таксономій (ліворуч зверху) -->
        <div class="absolute top-3 left-3 flex flex-wrap gap-1.5 z-10 pointer-events-none max-w-[calc(100%-1.5rem)]">
            <?php if ($type_name) : ?>
                <span class="px-2.5 py-1 text-xs font-semibold bg-slate-900/90 text-white rounded-md shadow-sm">
                    <?php echo esc_html($type_name); ?>
                </span>
            <?php endif; ?>
            <?php if ($city_name) : ?>
                <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-600/90 text-white rounded-md shadow-sm">
                    <?php echo esc_html($city_name); ?>
                </span>
            <?php endif; ?>
        </div>

        <!-- Кнопка "Додати в обране" (праворуч знизу зображення) -->
        <button
            type="button"
            class="favorite-toggle-btn absolute bottom-3 right-3 z-20 w-9 h-9 rounded-full bg-white/90 backdrop-blur-sm shadow-md flex items-center justify-center text-slate-400 hover:text-red-500 hover:scale-110 active:scale-95 transition-all duration-200"
            data-property-id="<?php echo esc_attr($post_id); ?>"
            title="Додати в обране"
            aria-label="Додати в обране"
        >
            <svg class="w-5 h-5 fill-current pointer-events-none" viewBox="0 0 24 24">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
        </button>
    </div>

    <!-- Нижня частина: Контент картки -->
    <div class="p-5 flex flex-col flex-1">
        <a href="<?php the_permalink(); ?>" class="block mb-2">
            <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-600 transition line-clamp-2">
                <?php the_title(); ?>
            </h3>
        </a>

        <?php if ($address) : ?>
            <p class="text-xs text-slate-500 mb-4 flex items-center gap-1">
                <span aria-hidden="true">📍</span> <?php echo esc_html($address); ?>
            </p>
        <?php endif; ?>

        <!-- Нижня панель: Ціна та площа -->
        <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-between">
            <div>
                <?php if ($price) : ?>
                    <span class="text-lg font-extrabold text-emerald-600">
                        $<?php echo number_format((float) $price, 0, '.', ' '); ?>
                    </span>
                <?php else : ?>
                    <span class="text-sm font-semibold text-slate-400">Ціна за запитом</span>
                <?php endif; ?>
            </div>

            <?php if ($area) : ?>
                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-md">
                    <?php echo esc_html($area); ?> м²
                </span>
            <?php endif; ?>
        </div>
    </div>
</article>