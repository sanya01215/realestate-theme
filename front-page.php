<?php
/**
 * Головна сторінка каталогу нерухомості
 *
 * @package RealEstateCatalog
 */

get_header();

/**
 * Отримуємо терміни таксономії безпосередньо з атрибутів товарів/об'єктів (hide_empty => true),
 * а також прибираємо можливі дублікати за назвою, якщо вони є в базі.
 *
 * @param string $taxonomy Назва таксономії ('city' або 'property_type').
 * @return array Список унікальних термінів.
 */
function re_get_unique_property_terms(string $taxonomy): array {
    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    if (empty($terms) || is_wp_error($terms)) {
        return [];
    }

    $unique_terms = [];
    $seen_names   = [];

    foreach ($terms as $term) {
        $normalized_name = mb_strtolower(trim($term->name));
        if (!in_array($normalized_name, $seen_names, true)) {
            $seen_names[]   = $normalized_name;
            $unique_terms[] = $term;
        }
    }

    return $unique_terms;
}

// Динамічно отримуємо міста та типи
$cities = re_get_unique_property_terms('city');
$types  = re_get_unique_property_terms('property_type');

// Початковий запит для визначення загальної кількості об'єктів
$initial_query = new WP_Query([
    'post_type'      => 'property',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
]);
$total_found = $initial_query->found_posts;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-0 pb-4 sm:pb-6">
    <!-- Заголовок сторінки з мінімальним верхнім відступом -->
    <div class="mb-3 sm:mb-6">
        <h1 class="text-2xl sm:text-3xl font-serif font-semibold text-slate-900 tracking-tight">
            Каталог нерухомості
        </h1>
        <p class="mt-1 text-xs sm:text-sm font-normal text-slate-500 tracking-wide">
            Знайдіть найкращі варіанти житла та ділянок у перевірених локаціях
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-8 items-start">
        <!-- Сайдбар з формою фільтрації -->
        <aside class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-slate-200 lg:sticky lg:top-24">
            <div class="flex items-center justify-between mb-4 sm:mb-6 pb-3 sm:pb-4 border-b border-slate-100">
                <h2 class="text-sm sm:text-base font-bold text-slate-900">Фільтр пошуку</h2>
                <button type="button" id="reset-filter" class="text-xs font-semibold text-slate-400 hover:text-emerald-600 transition">
                    Скинути все
                </button>
            </div>

            <form id="property-filter-form" class="space-y-4 sm:space-y-5" onsubmit="return false;">

                <!-- Приховані поля для фільтрації обраного -->
                <input type="hidden" name="only_favorites" id="filter-only-favorites" value="0">
                <input type="hidden" name="favorite_ids" id="filter-favorite-ids" value="">

                <!-- Кнопка швидкого перемикання "Тільки обрані" -->
                <div>
                    <button
                        type="button"
                        id="toggle-favorites-filter"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-bold rounded-lg transition"
                    >
                        <span>❤️</span>
                        <span id="favorites-filter-label">Показати тільки обрані</span>
                    </button>
                </div>

                <!-- Фільтр за містом -->
                <div>
                    <label for="filter-city" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">
                        Місто
                    </label>
                    <select
                        name="city"
                        id="filter-city"
                        class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition"
                    >
                        <option value="">Всі міста</option>
                        <?php if (!empty($cities)) : ?>
                            <?php foreach ($cities as $city) : ?>
                                <option value="<?php echo esc_attr($city->slug); ?>">
                                    <?php echo esc_html($city->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Фільтр за типом нерухомості -->
                <div>
                    <label for="filter-property-type" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">
                        Тип нерухомості
                    </label>
                    <select
                        name="property_type"
                        id="filter-property-type"
                        class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition"
                    >
                        <option value="">Всі типи</option>
                        <?php if (!empty($types)) : ?>
                            <?php foreach ($types as $type) : ?>
                                <option value="<?php echo esc_attr($type->slug); ?>">
                                    <?php echo esc_html($type->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Фільтр за максимальною ціною -->
                <div>
                    <label for="filter-max-price" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">
                        Макс. ціна ($)
                    </label>
                    <input
                        type="number"
                        name="max_price"
                        id="filter-max-price"
                        min="0"
                        step="1000"
                        placeholder="Наприклад: 120000"
                        class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition"
                    >
                </div>
            </form>
        </aside>

        <!-- Основний блок заголовок/сотування + сітка об'єктів -->
        <section class="lg:col-span-3 flex flex-col gap-6">
            <!-- Верхня панель: Лічильник знайдених об'єктів + Сортування -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm font-semibold text-slate-700">
                    Знайдено: <span id="results-count" class="text-emerald-600 font-bold"><?php echo (int) $total_found; ?></span> об'єктів
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <label for="filter-sort" class="text-xs font-medium text-slate-500 whitespace-nowrap">
                        Сортувати:
                    </label>
                    <select
                        name="sort"
                        id="filter-sort"
                        form="property-filter-form"
                        class="w-full sm:w-auto bg-slate-50 border border-slate-200 text-slate-800 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition"
                    >
                        <option value="date_desc">Спочатку нові</option>
                        <option value="price_asc">Спочатку дешевші</option>
                        <option value="price_desc">Спочатку дорожчі</option>
                    </select>
                </div>
            </div>

            <!-- Сітка об'єктів нерухомості -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="property-grid">
                <?php
                if ($initial_query->have_posts()) :
                    while ($initial_query->have_posts()) :
                        $initial_query->the_post();
                        get_template_part('template-parts/content', 'property-card');
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <div class="col-span-full bg-white p-8 text-center rounded-xl border border-slate-200">
                        <p class="text-slate-500 font-medium">Об'єктів нерухомості поки немає.</p>
                    </div>
                    <?php
                endif;
                ?>
            </div>
        </section>
    </div>
</div>

<?php
get_footer();