<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo("charset"); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-slate-50 text-slate-800 antialiased'); ?>>
<?php wp_body_open(); ?>

<header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="<?php echo esc_url(home_url("/")); ?>" class="text-xl font-bold text-slate-900 flex items-center gap-2 hover:opacity-80 transition">
            <span class="text-2xl">🏠</span> RealEstate Catalog
        </a>
    </div>
</header>
<main class="site-main py-8">
