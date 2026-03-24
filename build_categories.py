with open('blog.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Make it the Categories page
content = content.replace('<title>Blog - Knowledge-hud</title>', '<title>Categories - Knowledge-hud</title>')
content = content.replace('<li><a href="blog.html" class="nav-link active">Blog</a></li>', '<li><a href="blog.html" class="nav-link">Blog</a></li>')
content = content.replace('<li><a href="categories.html" class="nav-link">Categories</a></li>', '<li><a href="categories.html" class="nav-link active">Categories</a></li>')

content = content.replace('<h1>Our Latest <span class="text-gradient">Articles</span></h1>', '<h1>Explore by <span class="text-gradient">Category</span></h1>')
content = content.replace('<p style="max-width: 600px; margin: 1rem auto 0;">Explore our collection of articles carefully written to provide insight, knowledge, and actionable strategies.</p>', '<p style="max-width: 600px; margin: 1rem auto 0;">Find exactly what you are looking for by filtering our comprehensive collection of articles by topic.</p>')

filter_buttons = """
        <!-- Category Filter Buttons -->
        <div class="category-filters" style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 3rem;">
            <button class="btn btn-outline filter-btn active" data-filter="all" style="border-color: var(--accent-primary); color: var(--accent-primary) !important;">All</button>
            <button class="btn btn-outline filter-btn" data-filter="technology">Technology</button>
            <button class="btn btn-outline filter-btn" data-filter="business">Business</button>
            <button class="btn btn-outline filter-btn" data-filter="lifestyle">Lifestyle</button>
            <button class="btn btn-outline filter-btn" data-filter="design">Design</button>
        </div>
"""

# Insert filter buttons before search-container
content = content.replace('<div class="search-container">', filter_buttons + '\n        <div class="search-container">')

with open('categories.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("Created categories.html")
