<?php
$price   = get_field('price') ?: '0';
$area    = get_field('area') ?: '-';
$address = get_field('address') ?: '';
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition group flex flex-col'); ?>>
    <a href="<?php the_permalink(); ?>" class="block flex-1 flex flex-col">
        <div class="relative aspect-[16/10] bg-slate-100 overflow-hidden">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('medium_large', ['class' => 'w-full h-full object-cover group-hover:scale-105 transition duration-300']); ?>
            <?php else : ?>
                <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm">Немає фото</div>
            <?php endif; ?>
        </div>
        
        <div class="p-5 flex flex-col flex-1">
            <h3 class="text-lg font-bold text-slate-900 group-hover:text-emerald-600 transition mb-2">
                <?php the_title(); ?>
            </h3>
            
            <?php if ($address) : ?>
                <p class="text-xs text-slate-500 mb-4 flex items-center gap-1">
                    📍 <?php echo esc_html($address); ?>
                </p>
            <?php endif; ?>

            <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-lg font-extrabold text-emerald-600">
                    $<?php echo number_format((float)$price); ?>
                </span>
                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-md">
                    <?php echo esc_html($area); ?> м²
                </span>
            </div>
        </div>
    </a>
</article>
