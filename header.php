<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo("charset"); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class('min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased font-sans'); ?>>
<?php wp_body_open(); ?>

<header class="bg-white/95 backdrop-blur-md shadow-sm border-b border-slate-200/80 sticky top-0 z-50 transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2">
        <!-- Логотип -->
        <a href="<?php echo esc_url(home_url("/")); ?>" class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2 hover:opacity-80 transition shrink-0">
            <span class="text-xl sm:text-2xl">🏠</span>
            <span class="font-extrabold tracking-tight">RealEstate <span class="text-emerald-600">Catalog</span></span>
        </a>

        <!-- Блок "Обране" -->
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        id="header-favorites-btn"
                        class="flex items-center gap-2 px-3 sm:px-4 py-2 bg-slate-100 hover:bg-slate-200/80 active:scale-95 text-slate-800 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200/60 shadow-2xs transition duration-200"
                    >
                        <span class="text-red-500">❤️</span>
                        <span class="hidden sm:inline">Обране</span>
                        <span id="favorites-badge" class="px-2 py-0.5 text-xs font-bold bg-emerald-600 text-white rounded-full leading-none">0</span>
                    </button>
                </div>
            </div>
        </header>
<main class="site-main flex-1 py-6 sm:py-8">