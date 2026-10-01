<?php
get_header();
while (have_posts()) : the_post();
    $price   = get_field('price') ?: '0';
    $area    = get_field('area') ?: '-';
    $address = get_field('address') ?: '';
?>
<div class="container">
    <article class="single-property">
        <header class="single-property__header">
            <h1><?php the_title(); ?></h1>
            <?php if ($address) : ?>
                <p class="address">📍 <?php echo esc_html($address); ?></p>
            <?php endif; ?>
        </header>

        <?php if (has_post_thumbnail()) : ?>
            <div class="single-property__image">
                <?php the_post_thumbnail('large'); ?>
            </div>
        <?php endif; ?>

        <div class="single-property__details">
            <div class="price">Ціна: $<?php echo number_format((float)$price); ?></div>
            <div class="area">Площа: <?php echo esc_html($area); ?> м²</div>
        </div>

        <div class="single-property__content">
            <?php the_content(); ?>
        </div>
    </article>
</div>
<?php
endwhile;
get_footer();
?>
