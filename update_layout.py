import glob
import re

html_files = glob.glob('*.html')

new_nav_part = """<div class="nav-right" style="display: flex; gap: 1rem; align-items: center;">
                <div class="nav-search" style="position: relative; display: none;">
                    <input type="text" id="nav-search-input" placeholder="Search..." style="padding: 0.5rem 1rem; border-radius: 9999px; border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-primary); outline: none;">
                </div>
                <button class="search-toggle" aria-label="Toggle Search" style="background: transparent; border: none; color: var(--text-primary); cursor: pointer;" onclick="document.querySelector('.nav-search').style.display = document.querySelector('.nav-search').style.display === 'block' ? 'none' : 'block';">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle Dark Mode" style="width: auto; padding: 0 1rem; border-radius: 9999px;">
                    <svg id="theme-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <span id="theme-text" style="margin-left: 0.5rem; font-weight: 500; font-size: 0.9rem;">Dark Mode</span>
                </button>
                <button class="mobile-toggle" id="mobile-toggle" aria-label="Toggle Menu">
                    <svg id="menu-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>"""

new_footer = """<!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid" style="grid-template-columns: 1fr 1fr; gap: 4rem;">
                <div class="footer-col">
                    <a href="index.html" class="logo" style="margin-bottom: 1rem; display: block;">Knowledge<span>-hud</span></a>
                    <p>Your premier destination for high-quality articles, insightful guides, and stories that matter.</p>
                </div>
                <div class="footer-col" style="display: flex; gap: 4rem;">
                    <div>
                        <h4>Explore</h4>
                        <ul class="footer-links">
                            <li><a href="about.html">About Us</a></li>
                            <li><a href="contact.html">Contact</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4>Legal</h4>
                        <ul class="footer-links">
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Knowledge-hud. All rights reserved.</p>
            </div>
        </div>
    </footer>"""

for file in html_files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Replace nav right section
    nav_pattern = r'<div style="display: flex; gap: 1rem; align-items: center;">.*?</button>\s*</div>'
    content = re.sub(nav_pattern, new_nav_part, content, flags=re.DOTALL)
    
    # Replace footer
    footer_pattern = r'<!-- Footer -->\s*<footer class="footer">.*?</footer>'
    content = re.sub(footer_pattern, new_footer, content, flags=re.DOTALL)
    
    # Check if Categories link is in nav, if not add it
    if '<li><a href="categories.html" class="nav-link' not in content:
        content = content.replace('<li><a href="blog.html" class="nav-link">Blog</a></li>', '<li><a href="blog.html" class="nav-link">Blog</a></li>\n                <li><a href="categories.html" class="nav-link">Categories</a></li>')
        content = content.replace('<li><a href="blog.html" class="nav-link active">Blog</a></li>', '<li><a href="blog.html" class="nav-link active">Blog</a></li>\n                <li><a href="categories.html" class="nav-link">Categories</a></li>')
    
    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)

print("Updated nav and footer for all pages.")
