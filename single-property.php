<?php
/**
 * Шаблон окремої сторінки об'єкта нерухомості (Single Property)
 *
 * @package RealEstateCatalog
 */

get_header();

while (have_posts()) :
    the_post();
    $post_id = get_the_ID();

    // Отримання ACF полів
    $price   = function_exists('get_field') ? get_field('price', $post_id) : get_post_meta($post_id, 'price', true);
    $area    = function_exists('get_field') ? get_field('area', $post_id) : get_post_meta($post_id, 'area', true);
    $address = function_exists('get_field') ? get_field('address', $post_id) : get_post_meta($post_id, 'address', true);

    // Отримання таксономій
    $cities = get_the_terms($post_id, 'city');
    $types  = get_the_terms($post_id, 'property_type');

    $city_name = (!empty($cities) && !is_wp_error($cities)) ? $cities[0]->name : '';
    $type_name = (!empty($types) && !is_wp_error($types)) ? $types[0]->name : '';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Навігація назад до каталогу -->
    <div class="mb-6">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition">
            <span class="mr-1.5" aria-hidden="true">&larr;</span> Назад до каталогу
        </a>
    </div>

    <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden'); ?>>
        <!-- Головне зображення об'єкта з кнопкою "Обране" -->
        <?php if (has_post_thumbnail()) : ?>
            <div class="relative w-full max-h-[480px] bg-slate-100 overflow-hidden">
                <?php the_post_thumbnail('large', [
                    'class' => 'w-full h-full object-cover max-h-[480px]',
                    'alt'   => the_title_attribute(['echo' => false]),
                ]); ?>

                <!-- Кнопка "Додати в обране" в правому нижньому кутку фотографії -->
                <button
                    type="button"
                    class="favorite-toggle-btn absolute bottom-4 right-4 z-20 w-11 h-11 rounded-full bg-white/90 backdrop-blur-sm shadow-lg flex items-center justify-center text-slate-400 hover:text-red-500 hover:scale-110 active:scale-95 transition-all duration-200"
                    data-property-id="<?php echo esc_attr($post_id); ?>"
                    title="Додати в обране"
                    aria-label="Додати в обране"
                >
                    <svg class="w-6 h-6 fill-current pointer-events-none" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </button>
            </div>
        <?php endif; ?>

        <div class="p-6 sm:p-8 lg:p-10">
            <!-- Заголовок та метадані -->
            <header class="mb-8">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <?php if ($type_name) : ?>
                        <span class="px-3 py-1 text-xs font-semibold bg-slate-900 text-white rounded-md">
                            <?php echo esc_html($type_name); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($city_name) : ?>
                        <span class="px-3 py-1 text-xs font-semibold bg-emerald-600 text-white rounded-md">
                            <?php echo esc_html($city_name); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-3">
                    <?php the_title(); ?>
                </h1>

                <?php if ($address) : ?>
                    <p class="text-sm text-slate-500 flex items-center gap-1.5">
                        <span aria-hidden="true">📍</span> <?php echo esc_html($address); ?>
                    </p>
                <?php endif; ?>
            </header>

            <!-- Блок ключових характеристик + Кнопка дії -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 p-6 bg-slate-50 rounded-xl border border-slate-200 mb-8 items-center">
                <div class="flex flex-col">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Вартість</span>
                    <span class="text-2xl font-black text-emerald-600">
                        <?php echo $price ? '$' . number_format((float) $price, 0, '.', ' ') : 'За домовленістю'; ?>
                    </span>
                </div>

                <div class="flex flex-col">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Загальна площа</span>
                    <span class="text-xl font-bold text-slate-900">
                        <?php echo $area ? esc_html($area) . ' м²' : '—'; ?>
                    </span>
                </div>

                <div class="flex flex-col">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Локація</span>
                    <span class="text-xl font-bold text-slate-900">
                        <?php echo $city_name ? esc_html($city_name) : 'Не вказано'; ?>
                    </span>
                </div>

                <div class="sm:col-span-2 md:col-span-1 flex justify-end">
                    <button
                        type="button"
                        id="open-lead-modal"
                        class="w-full sm:w-auto px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-sm transition duration-200 flex items-center justify-center gap-2"
                    >
                        <span>📅</span>
                        <span>Замовити огляд</span>
                    </button>
                </div>
            </div>

            <!-- Детальний опис -->
            <div class="border-t border-slate-200 pt-8">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Опис об'єкта</h2>
                <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed space-y-4">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </article>
</div>

<!-- Модальне вікно "Замовити огляд" -->
<div id="lead-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 sm:p-8 relative transition-all transform">
        <!-- Кнопка закриття -->
        <button type="button" id="close-lead-modal" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1 text-xl font-bold">
            &times;
        </button>

        <h3 class="text-xl font-bold text-slate-900 mb-2">Замовити огляд об'єкта</h3>
        <p class="text-xs text-slate-500 mb-6">Залиште ваші контакти, і наш менеджер зв'яжеться з вами для узгодження часу</p>

        <form id="lead-form" class="space-y-4">
            <input type="hidden" name="property_id" value="<?php echo esc_attr($post_id); ?>">

            <div>
                <label for="lead-name" class="block text-xs font-semibold text-slate-700 uppercase mb-1">Ваше ім'я</label>
                <input type="text" id="lead-name" required placeholder="Олександр" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label for="lead-phone" class="block text-xs font-semibold text-slate-700 uppercase mb-1">Телефон</label>
                <input type="tel" id="lead-phone" required placeholder="+380 67 000 0000" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition mt-2">
                Відправити заявку
            </button>
        </form>

        <div id="lead-success" class="hidden text-center py-6 space-y-2">
            <span class="text-4xl">🎉</span>
            <h4 class="text-lg font-bold text-slate-900">Заявку прийнято!</h4>
            <p class="text-xs text-slate-500">Ми зателефонуємо вам найближчим часом.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('lead-modal');
    const openBtn = document.getElementById('open-lead-modal');
    const closeBtn = document.getElementById('close-lead-modal');
    const form = document.getElementById('lead-form');
    const successMsg = document.getElementById('lead-success');

    if (openBtn && modal) {
        openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
    }

    if (closeBtn && modal) {
        closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.add('hidden');
        });
    }

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            form.classList.add('hidden');
            successMsg.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.add('hidden');
                form.reset();
                form.classList.remove('hidden');
                successMsg.add('hidden');
            }, 2500);
        });
    }
});
</script>

<?php
endwhile;

get_footer();