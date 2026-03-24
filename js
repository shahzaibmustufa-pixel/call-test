/**
 * Knowledge-hud - Main JavaScript
 * Handles Theme Toggle, Mobile Menu, Search Filter, Category Filter, and Form Validation
 */

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initMobileMenu();
    initSmoothScroll();
    initSearchFilter();
    initGlobalSearch();
    initCategoryFilter();
    initContactForm();
});

// --- Theme Toggle (Light / Dark Mode) ---
function initThemeToggle() {
    const themeToggleBtn = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;
    const themeIcon = document.getElementById('theme-icon');

    if (!themeToggleBtn) return;

    // Check saved theme in localStorage or default to system preference
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    // Set initial theme
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        htmlElement.setAttribute('data-theme', 'dark');
        updateThemeIcon(true);
    } else {
        htmlElement.setAttribute('data-theme', 'light');
        updateThemeIcon(false);
    }

    // Toggle click event
    themeToggleBtn.addEventListener('click', () => {
        const currentTheme = htmlElement.getAttribute('data-theme');
        if (currentTheme === 'dark') {
            htmlElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
            updateThemeIcon(false);
        } else {
            htmlElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            updateThemeIcon(true);
        }
    });

    function updateThemeIcon(isDark) {
        if (!themeIcon) return;
        const themeText = document.getElementById('theme-text');
        if (isDark) {
            // Sun icon
            themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />';
            if (themeText) themeText.textContent = 'Light Mode';
        } else {
            // Moon icon
            themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />';
            if (themeText) themeText.textContent = 'Dark Mode';
        }
    }
}

// --- Mobile Menu Toggle ---
function initMobileMenu() {
    const mobileToggle = document.getElementById('mobile-toggle');
    const navLinks = document.getElementById('nav-links');
    const menuIcon = document.getElementById('menu-icon');

    if (!mobileToggle || !navLinks) return;

    mobileToggle.addEventListener('click', () => {
        navLinks.classList.toggle('active');
        const isActive = navLinks.classList.contains('active');

        if (isActive) {
            menuIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
        } else {
            menuIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
        }
    });

    const links = navLinks.querySelectorAll('.nav-link');
    links.forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('active');
            menuIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
        });
    });
}

// --- Smooth Scrolling ---
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                const headerOffset = 80;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// --- Page Search Filter ---
function initSearchFilter() {
    const searchInput = document.getElementById('blog-search');
    const blogCards = document.querySelectorAll('.blog-card');
    const noResults = document.getElementById('no-results');

    if (!searchInput) return;

    // Check if there's a search query in the URL (from global search)
    const urlParams = new URLSearchParams(window.location.search);
    const query = urlParams.get('q');
    if (query) {
        searchInput.value = query;
        filterArticles(query);
    }

    searchInput.addEventListener('input', (e) => {
        filterArticles(e.target.value);
    });

    function filterArticles(searchTerm) {
        searchTerm = searchTerm.toLowerCase().trim();
        let visibleCount = 0;

        blogCards.forEach(card => {
            const title = card.querySelector('.blog-title') ? card.querySelector('.blog-title').textContent.toLowerCase() : '';
            const category = card.querySelector('.blog-category') ? card.querySelector('.blog-category').textContent.toLowerCase() : '';
            const excerpt = card.querySelector('.blog-excerpt') ? card.querySelector('.blog-excerpt').textContent.toLowerCase() : '';

            // Also check global active category filter if on categories page
            const activeFilterBtn = document.querySelector('.filter-btn.active');
            const activeCategory = activeFilterBtn && activeFilterBtn.dataset.filter !== 'all' ? activeFilterBtn.dataset.filter.toLowerCase() : '';

            const matchesSearch = title.includes(searchTerm) || category.includes(searchTerm) || excerpt.includes(searchTerm);
            const matchesCategory = activeCategory === '' || category.includes(activeCategory);

            if (matchesSearch && matchesCategory) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noResults) {
            noResults.style.display = (visibleCount === 0 && searchTerm !== '') ? 'block' : 'none';
        }
    }
}

// --- Global Search (Navbar) ---
function initGlobalSearch() {
    const navSearchInput = document.getElementById('nav-search-input');
    if (!navSearchInput) return;

    navSearchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const term = e.target.value.trim();
            if (term) {
                // If on blog or categories page, use the built in search
                const path = window.location.pathname;
                if (path.includes('blog.html') || path.includes('categories.html')) {
                    const pageSearch = document.getElementById('blog-search');
                    if (pageSearch) {
                        pageSearch.value = term;
                        pageSearch.dispatchEvent(new Event('input'));
                        document.querySelector('.nav-search').style.display = 'none'; // hide nav search
                        window.scrollTo({ top: pageSearch.offsetTop - 100, behavior: 'smooth' });
                    }
                } else {
                    // Redirect to blog page with query
                    window.location.href = `blog.html?q=${encodeURIComponent(term)}`;
                }
            }
        }
    });
}

// --- Category Filter (Categories Page) ---
function initCategoryFilter() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const blogCards = document.querySelectorAll('.blog-card');
    
    if (filterBtns.length === 0) return;

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Update active state
            filterBtns.forEach(b => {
                b.classList.remove('active');
                b.style.borderColor = 'var(--border-color)';
                b.style.color = 'var(--text-primary)';
            });
            btn.classList.add('active');
            btn.style.borderColor = 'var(--accent-primary)';
            btn.style.color = 'var(--accent-primary)';

            const filterValue = btn.getAttribute('data-filter').toLowerCase();
            const searchInput = document.getElementById('blog-search');
            const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';

            blogCards.forEach(card => {
                const categoryEl = card.querySelector('.blog-category');
                const titleEl = card.querySelector('.blog-title');
                const category = categoryEl ? categoryEl.textContent.toLowerCase() : '';
                const title = titleEl ? titleEl.textContent.toLowerCase() : '';
                
                const matchesCategory = filterValue === 'all' || category.includes(filterValue);
                const matchesSearch = searchTerm === '' || title.includes(searchTerm) || category.includes(searchTerm);

                if (matchesCategory && matchesSearch) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
}

// --- Contact Form Validation ---
function initContactForm() {
    const contactForm = document.getElementById('contact-form');

    if (!contactForm) return;

    contactForm.addEventListener('submit', (e) => {
        e.preventDefault();

        let isValid = true;
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const messageInput = document.getElementById('message');
        const successMessage = document.getElementById('form-success');

        document.querySelectorAll('.form-error').forEach(el => el.style.display = 'none');
        successMessage.style.display = 'none';

        if (nameInput && nameInput.value.trim() === '') {
            nameInput.nextElementSibling.style.display = 'block';
            isValid = false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailInput && !emailRegex.test(emailInput.value.trim())) {
            emailInput.nextElementSibling.style.display = 'block';
            isValid = false;
        }

        if (messageInput && messageInput.value.trim() === '') {
            messageInput.nextElementSibling.style.display = 'block';
            isValid = false;
        }

        if (isValid) {
            successMessage.style.display = 'block';
            contactForm.reset();
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 5000);
        }
    });
}
