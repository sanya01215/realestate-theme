/**
 * AJAX-фільтрація та функціонал "Обране" (LocalStorage) (Vanilla JS)
 */
document.addEventListener('DOMContentLoaded', () => {
    // Елементи DOM
    const filterForm            = document.getElementById('property-filter-form');
    const propertyGrid          = document.getElementById('property-grid');
    const resetButton           = document.getElementById('reset-filter');
    const resultsCount          = document.getElementById('results-count');
    const sortSelect            = document.getElementById('filter-sort');
    const favoritesBadge        = document.getElementById('favorites-badge');
    const headerFavoritesBtn    = document.getElementById('header-favorites-btn');
    const toggleFavoritesBtn    = document.getElementById('toggle-favorites-filter');
    const favoritesFilterLabel  = document.getElementById('favorites-filter-label');
    const onlyFavoritesInput    = document.getElementById('filter-only-favorites');
    const favoriteIdsInput      = document.getElementById('filter-favorite-ids');

    let debounceTimer = null;
    let currentAbortController = null;

    /**
     * Отримання списку обраних ID з LocalStorage
     */
    const getFavorites = () => {
        try {
            const data = localStorage.getItem('re_favorites');
            return data ? JSON.parse(data) : [];
        } catch (e) {
            console.error('Error reading LocalStorage', e);
            return [];
        }
    };

    /**
     * Збереження списку обраних ID у LocalStorage
     */
    const saveFavorites = (favs) => {
        try {
            localStorage.setItem('re_favorites', JSON.stringify(favs));
        } catch (e) {
            console.error('Error writing LocalStorage', e);
        }
    };

    /**
     * Оновлення активного стану кнопок-сердечок та шапки у DOM
     */
    const updateFavoriteButtonsUI = () => {
        const favs = getFavorites();

        // Оновлюємо бейдж у шапці
        if (favoritesBadge) {
            favoritesBadge.textContent = favs.length;
        }

        // Перевіряємо всі кнопки сердечок на сторінці (у т.ч. на single-property.php)
        document.querySelectorAll('.favorite-toggle-btn').forEach((btn) => {
            const id = parseInt(btn.getAttribute('data-property-id'), 10);
            if (favs.includes(id)) {
                btn.classList.add('text-red-500');
                btn.classList.remove('text-slate-400');
            } else {
                btn.classList.remove('text-red-500');
                btn.classList.add('text-slate-400');
            }
        });
    };

    /**
     * Перемикання статусу обраного для ID
     */
    const toggleFavorite = (id) => {
        let favs = getFavorites();
        const index = favs.indexOf(id);

        if (index > -1) {
            favs.splice(index, 1);
        } else {
            favs.push(id);
        }

        saveFavorites(favs);
        updateFavoriteButtonsUI();

        // Якщо зараз на головній сторінці активовано фільтр "Тільки обрані" — оновлюємо видачу
        if (onlyFavoritesInput && onlyFavoritesInput.value === '1') {
            if (favoriteIdsInput) {
                favoriteIdsInput.value = favs.join(',');
            }
            if (typeof fetchProperties === 'function') {
                fetchProperties();
            }
        }
    };

    // Глобальний слухач кліку на кнопку "Обране" (на всіх сторінках)
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('.favorite-toggle-btn');
        if (btn) {
            event.preventDefault();
            event.stopPropagation();
            const id = parseInt(btn.getAttribute('data-property-id'), 10);
            if (id) {
                toggleFavorite(id);
            }
        }
    });

    // Оновлюємо UI обраного одразу при завантаженні будь-якої сторінки
    updateFavoriteButtonsUI();

    // ЯКЩО МИ НЕ НА СТОРІНЦІ КАТАЛОГУ (немає форми фільтрів або сітки) - заперечуємо виконання AJAX-фільтрів
    if (!filterForm || !propertyGrid || typeof window.re_ajax === 'undefined') {
        return;
    }

    /**
     * Оновлення URL-адреси в браузері без перезавантаження
     */
    const updateURL = (formData) => {
        const params = new URLSearchParams();

        for (const [key, value] of formData.entries()) {
            if (value && key !== 'action' && key !== 'nonce' && key !== 'favorite_ids') {
                params.set(key, value);
            }
        }

        const newURL = params.toString()
            ? `${window.location.pathname}?${params.toString()}`
            : window.location.pathname;

        window.history.pushState({ path: newURL }, '', newURL);
    };

    /**
     * Відновлення значень форми з URL
     */
    const syncFormFromURL = () => {
        const urlParams = new URLSearchParams(window.location.search);

        const citySelect     = filterForm.querySelector('[name="city"]');
        const typeSelect     = filterForm.querySelector('[name="property_type"]');
        const maxPriceInput  = filterForm.querySelector('[name="max_price"]');

        if (citySelect) citySelect.value = urlParams.get('city') || '';
        if (typeSelect) typeSelect.value = urlParams.get('property_type') || '';
        if (maxPriceInput) maxPriceInput.value = urlParams.get('max_price') || '';
        if (sortSelect) sortSelect.value = urlParams.get('sort') || 'date_desc';

        const favsParam = urlParams.get('only_favorites');
        if (favsParam === '1' && onlyFavoritesInput) {
            onlyFavoritesInput.value = '1';
            if (favoriteIdsInput) favoriteIdsInput.value = getFavorites().join(',');
            if (toggleFavoritesBtn) toggleFavoritesBtn.classList.add('bg-red-600', 'text-white');
        }
    };

    /**
     * Генерація скелетонів
     */
    const renderSkeletons = (count = 3) => {
        let html = '';
        for (let i = 0; i < count; i++) {
            html += `
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden animate-pulse flex flex-col">
                    <div class="aspect-[16/10] bg-slate-200"></div>
                    <div class="p-5 flex flex-col flex-1 space-y-3">
                        <div class="h-4 bg-slate-200 rounded w-3/4"></div>
                        <div class="h-3 bg-slate-200 rounded w-1/2 mb-4"></div>
                        <div class="mt-auto pt-3 border-t border-slate-100 flex justify-between items-center">
                            <div class="h-5 bg-slate-200 rounded w-1/3"></div>
                            <div class="h-5 bg-slate-200 rounded w-1/4"></div>
                        </div>
                    </div>
                </div>
            `;
        }
        return html;
    };

    /**
     * Відправка AJAX-запиту
     */
    const fetchProperties = (updateHistory = true) => {
        if (currentAbortController) {
            currentAbortController.abort();
        }
        currentAbortController = new AbortController();

        propertyGrid.innerHTML = renderSkeletons(3);

        const formData = new FormData(filterForm);

        if (sortSelect && !formData.has('sort')) {
            formData.append('sort', sortSelect.value);
        }

        formData.append('action', 'filter_properties');
        formData.append('nonce', window.re_ajax.nonce);

        if (updateHistory) {
            updateURL(formData);
        }

        fetch(window.re_ajax.ajax_url, {
            method: 'POST',
            body: formData,
            signal: currentAbortController.signal,
        })
            .then((response) => {
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return response.json();
            })
            .then((res) => {
                if (res.success && typeof res.data.html !== 'undefined') {
                    propertyGrid.innerHTML = res.data.html;

                    if (resultsCount && typeof res.data.count !== 'undefined') {
                        resultsCount.textContent = res.data.count;
                    }

                    updateFavoriteButtonsUI();
                } else {
                    propertyGrid.innerHTML = `
                        <div class="col-span-full bg-white p-8 text-center rounded-xl border border-red-200 text-red-500">
                            Не вдалося завантажити дані. Спробуйте пізніше.
                        </div>
                    `;
                }
            })
            .catch((error) => {
                if (error.name === 'AbortError') return;
                console.error('Помилка фільтрації нерухомості:', error);
                propertyGrid.innerHTML = `
                    <div class="col-span-full bg-white p-8 text-center rounded-xl border border-red-200 text-red-500">
                        Виникла помилка під час запиту до сервера.
                    </div>
                `;
            });
    };

    /**
     * Перемикання режиму "Тільки обрані"
     */
    const toggleFavoritesMode = () => {
        const favs = getFavorites();
        const isCurrentlyOnlyFavs = onlyFavoritesInput.value === '1';

        if (!isCurrentlyOnlyFavs) {
            onlyFavoritesInput.value = '1';
            favoriteIdsInput.value = favs.join(',');
            if (toggleFavoritesBtn) {
                toggleFavoritesBtn.classList.add('bg-red-600', 'text-white');
                toggleFavoritesBtn.classList.remove('bg-red-50', 'text-red-600');
            }
            if (favoritesFilterLabel) {
                favoritesFilterLabel.textContent = 'Показати всі об\'єкти';
            }
        } else {
            onlyFavoritesInput.value = '0';
            favoriteIdsInput.value = '';
            if (toggleFavoritesBtn) {
                toggleFavoritesBtn.classList.remove('bg-red-600', 'text-white');
                toggleFavoritesBtn.classList.add('bg-red-50', 'text-red-600');
            }
            if (favoritesFilterLabel) {
                favoritesFilterLabel.textContent = 'Показати тільки обрані';
            }
        }

        fetchProperties();
    };

    if (toggleFavoritesBtn) {
        toggleFavoritesBtn.addEventListener('click', toggleFavoritesMode);
    }

    if (headerFavoritesBtn) {
        headerFavoritesBtn.addEventListener('click', () => {
            if (filterForm && propertyGrid) {
                toggleFavoritesMode();
            } else {
                window.location.href = `${window.re_ajax.home_url || '/'}?only_favorites=1`;
            }
        });
    }

    syncFormFromURL();

    if (window.location.search) {
        fetchProperties(false);
    }

    filterForm.addEventListener('change', (event) => {
        if (event.target.tagName.toLowerCase() === 'select') {
            fetchProperties();
        }
    });

    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            fetchProperties();
        });
    }

    filterForm.addEventListener('input', (event) => {
        if (event.target.name === 'max_price') {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchProperties();
            }, 400);
        }
    });

    if (resetButton) {
        resetButton.addEventListener('click', (event) => {
            event.preventDefault();
            filterForm.reset();
            onlyFavoritesInput.value = '0';
            favoriteIdsInput.value = '';
            if (sortSelect) sortSelect.value = 'date_desc';
            if (toggleFavoritesBtn) {
                toggleFavoritesBtn.classList.remove('bg-red-600', 'text-white');
                toggleFavoritesBtn.classList.add('bg-red-50', 'text-red-600');
            }
            if (favoritesFilterLabel) {
                favoritesFilterLabel.textContent = 'Показати тільки обрані';
            }
            fetchProperties();
        });
    }

    window.addEventListener('popstate', () => {
        syncFormFromURL();
        fetchProperties(false);
    });
});