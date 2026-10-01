<?php
get_header();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-slate-900 mb-8">Каталог нерухомості</h1>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <aside class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 h-fit">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Фільтр пошуку</h3>
            <p class="text-sm text-slate-500">Тут буде AJAX-фільтр</p>
        </aside>

        <section class="lg:col-span-3">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="property-grid">
                <?php
                $args = ['post_type' => 'property', 'posts_per_page' => 6];
                $query = new WP_Query($args);
                
                if ($query->have_posts()) : 
                    while ($query->have_posts()) : $query->the_post();
                        get_template_part('template-parts/content', 'property-card');
                    endwhile;
                    wp_reset_postdata();
                else : ?>
                    <div class="col-span-full bg-white p-8 text-center rounded-xl border border-slate-200">
                        <p class="text-slate-600">Об'єктів нерухомості поки немає. Додайте перший об'єкт в адмінці WordPress!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php
get_footer();
?>
