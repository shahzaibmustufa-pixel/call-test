<?php
require_once 'config.php';
$db = getDB();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge-hud - Latest Articles</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background: var(--bg-body); color: var(--text-main);">
    
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">Knowledge<span>-hud</span></a>
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link active">Home</a></li>
                <li><a href="blog.php" class="nav-link">Blog</a></li>
                <li><a href="categories.php" class="nav-link">Categories</a></li>
            </ul>
        </div>
    </nav>

    <!-- 🟡 FRONTEND FIXES 3: FEATURED ARTICLE SECTION -->
    <header id="hero" class="hero section-padding" style="min-height: 85vh; padding-top: 120px; display: flex; align-items: center; position: relative; overflow: hidden; background: #0f172a;">
        <!-- Background Overlay -->
        <div id="hero-bg" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; transition: background 0.5s ease; filter: brightness(0.4);"></div>
        
        <div class="container" style="position: relative; z-index: 2; color: white;">
            <div id="hero-content" style="max-width: 800px; display: none;">
                <span id="hero-category" class="blog-category" style="background: rgba(79, 70, 229, 0.3); padding: 8px 20px; border-radius: 999px; margin-bottom: 2rem; display: inline-block;">Latest News</span>
                <h1 id="hero-title" style="font-size: clamp(2.5rem, 5vw, 4.5rem); line-height: 1.1; margin-bottom: 1.5rem; text-shadow: 0 4px 10px rgba(0,0,0,0.5);">Loading Latest Article...</h1>
                <p id="hero-desc" style="font-size: 1.2rem; line-height: 1.6; margin-bottom: 2.5rem; color: #cbd5e1;"></p>
                <a id="hero-link" href="#" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem; border-radius: 0.75rem; background: #4361ee; border: none;">Read Featured Article</a>
            </div>
            <div id="hero-loader" style="font-size: 1.5rem; padding-bottom: 15rem;">Searching for latest articles...</div>
        </div>
    </header>

    <!-- 🟡 FRONTEND FIXES 5: ALL ARTICLES GRID -->
    <section class="section-padding container">
        <h2 style="margin-bottom: 3rem;">Recent <span class="text-gradient">Articles</span></h2>
        <div id="articles-grid" class="blog-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2.5rem;">
            <!-- Fetched from API -->
        </div>
    </section>

    <!-- 🟡 FRONTEND FIXES 1: FETCH ARTICLES JS -->
    <script>
        async function fetchArticles() {
            try {
                // 🟡 API URL: /api/articles.php (Relative to root)
                const response = await fetch('api/articles.php');
                if (!response.ok) throw new Error("API Error: " + response.status);
                const articles = await response.json();

                if (articles.length === 0) {
                    document.getElementById('hero-loader').innerText = "No articles found in the database.";
                    return;
                }

                // 🟡 SORTING: Already done by backend ORDER BY created_at DESC
                // 1. First article (latest) for hero
                renderHero(articles[0]);

                // 2. Remaining articles for grid
                const remaining = articles.slice(1);
                renderGrid(remaining);

            } catch (err) {
                console.error("Fetch failed:", err);
                document.getElementById('hero-loader').innerHTML = `<span style="color: #ef4444;">Error: ${err.message}</span>`;
            }
        }

        function renderHero(article) {
            document.getElementById('hero-loader').style.display = 'none';
            document.getElementById('hero-content').style.display = 'block';

            document.getElementById('hero-title').innerText = article.title;
            document.getElementById('hero-desc').innerText = article.excerpt || article.content.substring(0, 200).replace(/<[^>]*>/g, '') + '...';
            document.getElementById('hero-category').innerText = article.category_name || 'Latest Content';
            document.getElementById('hero-link').href = `article.php?slug=${article.slug}`;

            // 🟡 FRONTEND FIXES 4: BACKGROUND IMAGE FIX
            const imgPath = article.featured_image ? 'uploads/' + article.featured_image : 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1600&q=90';
            document.getElementById('hero-bg').style.background = `url("${imgPath}") center/cover no-repeat`;
        }

        function renderGrid(articles) {
            const grid = document.getElementById('articles-grid');
            if (articles.length === 0) {
                grid.innerHTML = '<p style="color: grey;">Check back soon for more articles!</p>';
                return;
            }

            grid.innerHTML = articles.map(a => `
                <article class="blog-card" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 1rem; overflow: hidden; transition: transform 0.3s ease;">
                    <a href="article.php?slug=${a.slug}"><img src="${a.featured_image ? 'uploads/' + a.featured_image : 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=400&q=80'}" style="width: 100%; height: 200px; object-fit: cover;"></a>
                    <div style="padding: 1.5rem;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #4361ee; text-transform: uppercase;">${a.category_name || 'Article'}</span>
                        <h3 style="margin: 0.75rem 0; font-size: 1.25rem;"><a href="article.php?slug=${a.slug}" style="color: inherit; text-decoration: none;">${a.title}</a></h3>
                        <p style="font-size: 0.95rem; color: #64748b; line-height: 1.5; margin-bottom: 1.5rem;">${a.excerpt || a.content.substring(0, 100).replace(/<[^>]*>/g, '') + '...'}</p>
                        <a href="article.php?slug=${a.slug}" style="font-weight: 600; text-decoration: underline; color: #4361ee;">Read Full Post</a>
                    </div>
                </article>
            `).join('');
        }

        // Initialize
        fetchArticles();
    </script>

    <footer class="footer" style="padding: 4rem 0; border-top: 1px solid var(--border-color); margin-top: 5rem; text-align: center;">
        <p>&copy; <?php echo date('Y'); ?> Knowledge-hud. Powered by Dynamic API Sync.</p>
    </footer>

</body>
</html>
