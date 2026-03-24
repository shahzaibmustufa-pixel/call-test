import re

with open('index.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Find everything between closing of nav and start of footer
nav_end_idx = content.find('</nav>') + 6
footer_start_idx = content.find('<!-- Footer -->')

top_part = content[:nav_end_idx]
bottom_part = content[footer_start_idx:]

new_body = """

    <!-- Featured Article Hero -->
    <header class="hero section-padding" style="min-height: 80vh; padding-top: 120px; display: flex; align-items: center; background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1600&q=80') center/cover;">
        <div class="container hero-content" style="text-align: left; max-width: 800px; margin: 0; color: white;">
            <span class="blog-category" style="color: #818cf8; margin-bottom: 1rem; display: inline-block;">Technology</span>
            <h1 style="color: white; margin-bottom: 1.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">The Dawn of General Artificial Intelligence</h1>
            <p style="color: #cbd5e1; margin-left: 0; max-width: 600px; margin-bottom: 2rem; font-size: 1.1rem;">A comprehensive exploration into how close we are to creating machines that can think, reason, and learn like humans do, and what it means for society.</p>
            <a href="article.html" class="btn btn-primary" style="background: #4f46e5; color: white; border: none;">Read Featured Article</a>
        </div>
    </header>

    <!-- Trending Articles Horizontal Scroll -->
    <section class="section-padding" style="background: var(--bg-secondary); border-bottom: 1px solid var(--border-color); padding-top: 4rem; padding-bottom: 4rem;">
        <div class="container">
            <h3 style="margin-bottom: 1.5rem; font-size: 1.5rem;">Trending Now</h3>
            <div style="display: flex; gap: 1.5rem; overflow-x: auto; padding-bottom: 1rem; scroll-snap-type: x mandatory; scrollbar-width: thin;">
                <!-- Trending 1 -->
                <a href="article.html" class="trending-card" style="min-width: 300px; scroll-snap-align: start; display: flex; align-items: center; gap: 1rem; background: var(--card-bg); padding: 1rem; border-radius: 0.75rem; border: 1px solid var(--border-color); color: inherit;">
                    <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=150&q=80" alt="Trending" style="width: 80px; height: 80px; object-fit: cover; border-radius: 0.5rem;">
                    <div>
                        <span style="font-size: 0.75rem; color: var(--accent-primary); font-weight: 600; text-transform: uppercase;">Business</span>
                        <h4 style="font-size: 1rem; margin-top: 0.25rem;">Remote Work Evolution</h4>
                    </div>
                </a>
                <!-- Trending 2 -->
                <a href="article.html" class="trending-card" style="min-width: 300px; scroll-snap-align: start; display: flex; align-items: center; gap: 1rem; background: var(--card-bg); padding: 1rem; border-radius: 0.75rem; border: 1px solid var(--border-color); color: inherit;">
                    <img src="https://images.unsplash.com/photo-1499209974431-9dddcece7f88?auto=format&fit=crop&w=150&q=80" alt="Trending" style="width: 80px; height: 80px; object-fit: cover; border-radius: 0.5rem;">
                    <div>
                        <span style="font-size: 0.75rem; color: var(--accent-primary); font-weight: 600; text-transform: uppercase;">Lifestyle</span>
                        <h4 style="font-size: 1rem; margin-top: 0.25rem;">Mindful Living Guide</h4>
                    </div>
                </a>
                <!-- Trending 3 -->
                <a href="article.html" class="trending-card" style="min-width: 300px; scroll-snap-align: start; display: flex; align-items: center; gap: 1rem; background: var(--card-bg); padding: 1rem; border-radius: 0.75rem; border: 1px solid var(--border-color); color: inherit;">
                    <img src="https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=150&q=80" alt="Trending" style="width: 80px; height: 80px; object-fit: cover; border-radius: 0.5rem;">
                    <div>
                        <span style="font-size: 0.75rem; color: var(--accent-primary); font-weight: 600; text-transform: uppercase;">Design</span>
                        <h4 style="font-size: 1rem; margin-top: 0.25rem;">UI Trends of 2026</h4>
                    </div>
                </a>
                <!-- Trending 4 -->
                <a href="article.html" class="trending-card" style="min-width: 300px; scroll-snap-align: start; display: flex; align-items: center; gap: 1rem; background: var(--card-bg); padding: 1rem; border-radius: 0.75rem; border: 1px solid var(--border-color); color: inherit;">
                    <img src="https://images.unsplash.com/photo-1639762681485-074b7f938ba0?auto=format&fit=crop&w=150&q=80" alt="Trending" style="width: 80px; height: 80px; object-fit: cover; border-radius: 0.5rem;">
                    <div>
                        <span style="font-size: 0.75rem; color: var(--accent-primary); font-weight: 600; text-transform: uppercase;">Technology</span>
                        <h4 style="font-size: 1rem; margin-top: 0.25rem;">Understanding Web3</h4>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content & Sidebar Layout -->
    <section class="section-padding container">
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 4rem;" class="main-layout-grid">
            
            <!-- Left Content: Latest Articles -->
            <div class="main-content">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 0;">Latest <span class="text-gradient">Articles</span></h2>
                    <a href="blog.html" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View All</a>
                </div>

                <div class="blog-grid" style="margin-top: 0; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                    <!-- Art 1 -->
                    <article class="blog-card">
                        <a href="article.html" class="blog-image-link" style="display: block;"><img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=800&q=80" alt="Startup" class="blog-image"></a>
                        <div class="blog-content">
                            <span class="blog-category">Business</span>
                            <h3 class="blog-title"><a href="article.html" style="color: inherit; text-decoration: none;">Building a Resilient Startup</a></h3>
                            <p class="blog-excerpt">Key strategies to keep your company agile and prepared for unpredictable shifts.</p>
                            <a href="article.html" style="margin-bottom: 1rem; font-weight: 600; font-size: 0.9rem; text-decoration: underline;">Read More</a>
                            <div class="blog-meta">
                                <span>By Jordan Lee</span>
                                <span>Feb 28</span>
                            </div>
                        </div>
                    </article>
                    <!-- Art 2 -->
                    <article class="blog-card">
                        <a href="article.html" class="blog-image-link" style="display: block;"><img src="https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?auto=format&fit=crop&w=800&q=80" alt="Sleep" class="blog-image"></a>
                        <div class="blog-content">
                            <span class="blog-category">Lifestyle</span>
                            <h3 class="blog-title"><a href="article.html" style="color: inherit; text-decoration: none;">Optimizing Your Sleep</a></h3>
                            <p class="blog-excerpt">Science-backed routines and habits that guarantee better rest and higher energy.</p>
                            <a href="article.html" style="margin-bottom: 1rem; font-weight: 600; font-size: 0.9rem; text-decoration: underline;">Read More</a>
                            <div class="blog-meta">
                                <span>By Rachel Green</span>
                                <span>Feb 22</span>
                            </div>
                        </div>
                    </article>
                    <!-- Art 3 -->
                    <article class="blog-card">
                        <a href="article.html" class="blog-image-link" style="display: block;"><img src="https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80" alt="Privacy" class="blog-image"></a>
                        <div class="blog-content">
                            <span class="blog-category">Technology</span>
                            <h3 class="blog-title"><a href="article.html" style="color: inherit; text-decoration: none;">Data Privacy in 2026</a></h3>
                            <p class="blog-excerpt">How to protect your personal information when everyone is tracking you.</p>
                            <a href="article.html" style="margin-bottom: 1rem; font-weight: 600; font-size: 0.9rem; text-decoration: underline;">Read More</a>
                            <div class="blog-meta">
                                <span>By Tech Analyst</span>
                                <span>Feb 18</span>
                            </div>
                        </div>
                    </article>
                    <!-- Art 4 -->
                    <article class="blog-card">
                        <a href="article.html" class="blog-image-link" style="display: block;"><img src="https://images.unsplash.com/photo-1507608616759-54f48f0af0ee?auto=format&fit=crop&w=800&q=80" alt="Design" class="blog-image"></a>
                        <div class="blog-content">
                            <span class="blog-category">Design</span>
                            <h3 class="blog-title"><a href="article.html" style="color: inherit; text-decoration: none;">The Psychology of Colors</a></h3>
                            <p class="blog-excerpt">How color choices in branding influence user behavior and perception.</p>
                            <a href="article.html" style="margin-bottom: 1rem; font-weight: 600; font-size: 0.9rem; text-decoration: underline;">Read More</a>
                            <div class="blog-meta">
                                <span>By Sam Wright</span>
                                <span>Feb 10</span>
                            </div>
                        </div>
                    </article>
                    <!-- Art 5 -->
                    <article class="blog-card">
                        <a href="article.html" class="blog-image-link" style="display: block;"><img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80" alt="Team" class="blog-image"></a>
                        <div class="blog-content">
                            <span class="blog-category">Business</span>
                            <h3 class="blog-title"><a href="article.html" style="color: inherit; text-decoration: none;">Scaling Your Team</a></h3>
                            <p class="blog-excerpt">Knowing when and how to hire the right personnel for your company.</p>
                            <a href="article.html" style="margin-bottom: 1rem; font-weight: 600; font-size: 0.9rem; text-decoration: underline;">Read More</a>
                            <div class="blog-meta">
                                <span>By CEO Monthly</span>
                                <span>Feb 05</span>
                            </div>
                        </div>
                    </article>
                    <!-- Art 6 -->
                    <article class="blog-card">
                        <a href="article.html" class="blog-image-link" style="display: block;"><img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=800&q=80" alt="Productivity" class="blog-image"></a>
                        <div class="blog-content">
                            <span class="blog-category">Lifestyle</span>
                            <h3 class="blog-title"><a href="article.html" style="color: inherit; text-decoration: none;">Deep Work Habits</a></h3>
                            <p class="blog-excerpt">Mastering the art of focus in an age of constant digital distraction.</p>
                            <a href="article.html" style="margin-bottom: 1rem; font-weight: 600; font-size: 0.9rem; text-decoration: underline;">Read More</a>
                            <div class="blog-meta">
                                <span>By Alex Chen</span>
                                <span>Jan 29</span>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <!-- Right Sidebar -->
            <aside class="sidebar">
                
                <!-- Popular Posts Widget -->
                <div class="widget" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 1rem; padding: 1.5rem; margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-color);">Popular Posts</h3>
                    <ul style="display: flex; flex-direction: column; gap: 1rem;">
                        <li style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--border-color); line-height: 1;">1</div>
                            <div>
                                <h4 style="font-size: 1rem; margin-bottom: 0.25rem;"><a href="article.html" style="color: inherit;">The Future of AI in Daily Life</a></h4>
                                <span style="font-size: 0.8rem; color: var(--text-tertiary);">Mar 18, 2026</span>
                            </div>
                        </li>
                        <li style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--border-color); line-height: 1;">2</div>
                            <div>
                                <h4 style="font-size: 1rem; margin-bottom: 0.25rem;"><a href="article.html" style="color: inherit;">Mastering the Remote Workspace</a></h4>
                                <span style="font-size: 0.8rem; color: var(--text-tertiary);">Mar 15, 2026</span>
                            </div>
                        </li>
                        <li style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--border-color); line-height: 1;">3</div>
                            <div>
                                <h4 style="font-size: 1rem; margin-bottom: 0.25rem;"><a href="article.html" style="color: inherit;">UI Trends to Watch in 2026</a></h4>
                                <span style="font-size: 0.8rem; color: var(--text-tertiary);">Mar 08, 2026</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter Widget -->
                <div class="widget newsletter-widget" style="background: var(--accent-gradient); color: white; border-radius: 1rem; padding: 2rem 1.5rem; text-align: center; box-shadow: var(--shadow-md);">
                    <h3 style="color: white; font-size: 1.5rem; margin-bottom: 1rem;">Subscribe to Weekly Insights</h3>
                    <p style="color: rgba(255,255,255,0.9); font-size: 0.95rem; margin-bottom: 1.5rem;">Get the latest articles and exclusive content delivered straight to your inbox.</p>
                    <form onsubmit="event.preventDefault(); alert('Subscribed successfully!');">
                        <input type="email" placeholder="Your email address..." required style="width: 100%; padding: 0.75rem 1rem; border: none; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.95rem; outline: none; color: #0f172a;">
                        <button type="submit" style="width: 100%; padding: 0.75rem; background: #0f172a; color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: background 0.2s;">Subscribe Now</button>
                    </form>
                </div>

                <!-- Categories Widget -->
                <div class="widget" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 1rem; padding: 1.5rem; margin-top: 2rem; box-shadow: var(--shadow-sm);">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-color);">Explore Tags</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <a href="categories.html" style="padding: 0.4rem 0.8rem; background: var(--bg-tertiary); color: var(--text-secondary); border-radius: 9999px; font-size: 0.85rem; border: 1px solid var(--border-color);">Technology</a>
                        <a href="categories.html" style="padding: 0.4rem 0.8rem; background: var(--bg-tertiary); color: var(--text-secondary); border-radius: 9999px; font-size: 0.85rem; border: 1px solid var(--border-color);">Lifestyle</a>
                        <a href="categories.html" style="padding: 0.4rem 0.8rem; background: var(--bg-tertiary); color: var(--text-secondary); border-radius: 9999px; font-size: 0.85rem; border: 1px solid var(--border-color);">Business</a>
                        <a href="categories.html" style="padding: 0.4rem 0.8rem; background: var(--bg-tertiary); color: var(--text-secondary); border-radius: 9999px; font-size: 0.85rem; border: 1px solid var(--border-color);">Design</a>
                        <a href="categories.html" style="padding: 0.4rem 0.8rem; background: var(--bg-tertiary); color: var(--text-secondary); border-radius: 9999px; font-size: 0.85rem; border: 1px solid var(--border-color);">AI</a>
                        <a href="categories.html" style="padding: 0.4rem 0.8rem; background: var(--bg-tertiary); color: var(--text-secondary); border-radius: 9999px; font-size: 0.85rem; border: 1px solid var(--border-color);">Remote Work</a>
                    </div>
                </div>

            </aside>
        </div>
    </section>

"""

# Write it back
with open('index.html', 'w', encoding='utf-8') as f:
    f.write(top_part + new_body + bottom_part)
print("index.html rewritten successfully.")
