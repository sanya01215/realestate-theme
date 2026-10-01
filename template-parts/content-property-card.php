<?php
$price   = get_field('price') ?: '0';
$area    = get_field('area') ?: '-';
$address = get_field('address') ?: '';
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('property-card'); ?>>
    <a href="<?php the_permalink(); ?>" class="property-card__link">
        <?php if (has_post_thumbnail()) : ?>
            <div class="property-card__image">
                <?php the_post_thumbnail('medium'); ?>
            </div>
        <?php endif; ?>
        <div class="property-card__body">
            <h3 class="property-card__title"><?php the_title(); ?></h3>
            <?php if ($address) : ?>
                <p class="property-card__address">📍 <?php echo esc_html($address); ?></p>
            <?php endif; ?>
            <div class="property-card__meta">
                <span class="property-card__price">$<?php echo number_format((float)$price); ?></span>
                <span class="property-card__area"><?php echo esc_html($area); ?> м²</span>
            </div>
        </div>
    </a>
</article>
