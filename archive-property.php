<?php
get_header();
?>

<div class="container">
    <h1 class="page-title">Каталог нерухомості</h1>

    <div class="property-layout">
        <aside class="property-filter" id="property-filter">
            <h3>Фільтр пошуку</h3>
            <!-- Тут буде AJAX-форма фільтрації -->
        </aside>

        <section class="property-content">
            <div class="property-grid" id="property-grid">
                <?php if (have_posts()) : ?>
                    <?php while (have_posts()) : the_post(); ?>
                        <?php get_template_part('template-parts/content', 'property-card'); ?>
                    <?php endwhile; ?>
                <?php else : ?>
                    <p>Об'єктів не знайдено.</p>
                <?php endif; ?>
            </div>

            <div class="pagination">
                <?php the_posts_pagination(); ?>
            </div>
        </section>
    </div>
</div>

<?php
get_footer();
?>
