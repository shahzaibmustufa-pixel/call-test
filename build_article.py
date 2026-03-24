with open('article.html', 'r', encoding='utf-8') as f:
    content = f.read()

nav_end_idx = content.find('</nav>') + 6
footer_start_idx = content.find('<!-- Footer -->')

top_part = content[:nav_end_idx]
bottom_part = content[footer_start_idx:]

new_body = """

    <!-- Article Wrapper -->
    <main class="section-padding container" style="padding-top: 120px; max-width: 1000px;">
        
        <header class="article-header" style="text-align: left; margin-bottom: 2rem;">
            <span class="blog-category" style="margin-bottom: 1rem; display: inline-block;">Technology</span>
            <h1 style="margin-bottom: 1.5rem; font-size: 3rem; line-height: 1.2;">The Dawn of General Artificial Intelligence: A Comprehensive Exploration</h1>
            
            <div style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem; margin-bottom: 2rem;">
                <div class="article-meta" style="margin-bottom: 0; justify-content: flex-start; align-items: center; display: flex; gap: 1rem;">
                    <img src="https://ui-avatars.com/api/?name=Sarah+Jenkins&background=random" alt="Author" style="width: 48px; height: 48px; border-radius: 50%;">
                    <div>
                        <div style="font-weight: 600; color: var(--text-primary);">Sarah Jenkins</div>
                        <div style="color: var(--text-tertiary); font-size: 0.85rem;">Mar 18, 2026 &bull; 10 min read</div>
                    </div>
                </div>
                <!-- Share Buttons -->
                <div class="share-buttons" style="display: flex; gap: 0.5rem;">
                    <button style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-secondary); cursor: pointer; transition: 0.2s;" aria-label="Share on Twitter" onmouseover="this.style.color='var(--accent-primary)';" onmouseout="this.style.color='var(--text-secondary)';">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z"/></svg>
                    </button>
                    <button style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-secondary); cursor: pointer; transition: 0.2s;" aria-label="Share on LinkedIn" title="Share on LinkedIn" onmouseover="this.style.color='var(--accent-primary)';" onmouseout="this.style.color='var(--text-secondary)';">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/></svg>
                    </button>
                </div>
            </div>
        </header>

        <img src="https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1200&q=80" alt="The Future of AI in Daily Life" class="article-image-main">

        <div style="display: grid; grid-template-columns: 250px 1fr; gap: 3rem;" class="article-layout-grid">
            
            <!-- Table of Contents -->
            <aside class="toc-sidebar" style="position: sticky; top: 100px; height: fit-content; display: none; /* Make visible on large screens via CSS, handled globally approx */">
                <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border-color);">
                    <h4 style="margin-bottom: 1rem; font-size: 1.1rem;">Table of Contents</h4>
                    <ul style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <li><a href="#intro" style="color: var(--text-secondary); font-size: 0.95rem;">Introduction</a></li>
                        <li><a href="#definition" style="color: var(--text-secondary); font-size: 0.95rem;">What is AGI?</a></li>
                        <li><a href="#challenges" style="color: var(--text-secondary); font-size: 0.95rem;">Current Challenges</a></li>
                        <li><a href="#timeline" style="color: var(--text-secondary); font-size: 0.95rem;">Estimated Timeline</a></li>
                        <li><a href="#impact" style="color: var(--text-secondary); font-size: 0.95rem;">Societal Impact</a></li>
                        <li><a href="#conclusion" style="color: var(--text-secondary); font-size: 0.95rem;">Conclusion</a></li>
                    </ul>
                </div>
            </aside>

            <!-- Article Body -->
            <article class="article-content" style="max-width: 100%; margin: 0;">
                
                <style>
                    /* Inline style for the TOC responsiveness if needed */
                    @media (min-width: 992px) {
                        .toc-sidebar { display: block !important; }
                    }
                    @media (max-width: 991px) {
                        .article-layout-grid { grid-template-columns: 1fr !important; }
                    }
                </style>
                
                <div id="intro">
                    <p>Artificial intelligence is no longer just a concept relegated to science fiction novels or high-tech research labs. Today, AI is an integral part of our daily lives, quietly revolutionizing how we work, communicate, and navigate the world.</p>
                    
                    <p>From the moment you wake up to the time you go to sleep, algorithms are working behind the scenes to optimize your experience. Whether it's the personalized news feed keeping you informed or the smart thermostat adjusting your home's temperature, AI is everywhere.</p>
                    
                    <p class="dropcap-text" style="font-size: 1.2rem; font-style: italic; border-left: 4px solid var(--accent-primary); padding-left: 1rem; margin: 2rem 0; color: var(--text-primary);">"The development of full artificial intelligence could spell the end of the human race. It would take off on its own, and re-design itself at an ever increasing rate." — Stephen Hawking</p>

                    <p>But the real question on every expert's mind is no longer about Narrow AI—the kind of AI that plays chess, predicts weather, or translates text perfectly. The industry is looking toward **Artificial General Intelligence (AGI)**. Let's delve deep into what AGI means, where we stand, and what the future might hold for humanity.</p>
                </div>

                <h2 id="definition">What Exactly is AGI?</h2>
                <p>Artificial General Intelligence refers to a machine's ability to understand, learn, and apply knowledge across a wide variety of independent domains—much like a human being. The distinction is critical: while narrow AI is hyper-specialized, AGI is fundamentally adaptable.</p>

                <p>If you teach a modern AI system to play Go, it will beat the world champion. But if you ask that same system to summarize a novel or navigate a physical robot across a room, it will fail entirely. AGI, by contrast, would possess generalized cognitive abilities allowing it to transition seamlessly between completely unrelated tasks, using reasoning and context learned from one domain to solve problems in another.</p>
                
                <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80" alt="Tech circuit board" style="width: 100%; border-radius: 0.75rem; margin: 2rem 0;">

                <h2 id="challenges">The Monumental Current Challenges</h2>
                <p>Despite remarkable advances in Large Language Models (LLMs) and generative algorithms, the path to AGI is blocked by Several monumental engineering and philosophical challenges.</p>

                <h3>1. The Grounding Problem</h3>
                <p>Current models manipulate symbols and words according to statistical probabilities. They have no grounding in physical reality. They know what the word "apple" usually appears next to in a sentence, but they do not intuitively understand what an apple looks, feels, tastes, or smells like.</p>

                <h3>2. Energy Consumption</h3>
                <p>Training cutting-edge models currently requires enormous data centers burning megawatts of electricity. The human brain runs on about 20 watts of power—the equivalent of a dim lightbulb. Bridging this efficiency gap is perhaps the greatest hardware challenge of our era.</p>

                <h2 id="timeline">When Will AGI Arrive?</h2>
                <p>Predictions regarding the arrival timeline of AGI vary wildly among leading researchers.</p>
                <ul>
                    <li style="margin-bottom: 0.5rem; color: var(--text-secondary);"><strong style="color: var(--text-primary);">The Optimists (2028 - 2035):</strong> Some researchers believe that scaling up current Transformer architectures, combined with breakthroughs in reinforcement learning, will yield AGI within a decade.</li>
                    <li style="margin-bottom: 0.5rem; color: var(--text-secondary);"><strong style="color: var(--text-primary);">The Realists (2040 - 2060):</strong> The majority consensus posits that entirely new paradigms of computing (potentially neuromorphic chips or quantum computing) are required.</li>
                    <li style="margin-bottom: 0.5rem; color: var(--text-secondary);"><strong style="color: var(--text-primary);">The Skeptics (Never / >2100):</strong> A vocal minority arges that human consciousness and adaptability are fundamentally irreproducible in silicon.</li>
                </ul>

                <h2 id="impact">Societal and Economic Impact</h2>
                <p>If AGI is achieved, its impact will be more profound than the discovery of electricity or the invention of the internet. By outsourcing advanced cognitive labor, humanity could theoretically solve complex issues like climate change, disease, and resource scarcity in a fraction of the time.</p>

                <p>Conversely, the economic displacement would be unprecedented. The transition period would require radical societal restructuring—perhaps implementing global Universal Basic Income (UBI) frameworks or fundamentally redefining the human relationship with work.</p>

                <h2 id="conclusion">Concluding Thoughts</h2>
                <p>We stand on the precipice of the most important technological development in human history. Whether AGI arrives in five years or fifty, its approach is steady. As we build these increasingly capable systems, the focus must shift rapidly toward AI alignment—ensuring that the goals of a superintelligent machine remain inextricably linked with the flourishing of human life.</p>

                <!-- Tags -->
                <div style="margin-top: 3rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <span style="font-weight: 600; margin-right: 0.5rem;">Tags:</span>
                    <a href="categories.html" style="padding: 0.3rem 0.8rem; background: var(--bg-tertiary); color: var(--text-secondary); border-radius: 9999px; font-size: 0.85rem;">Technology</a>
                    <a href="categories.html" style="padding: 0.3rem 0.8rem; background: var(--bg-tertiary); color: var(--text-secondary); border-radius: 9999px; font-size: 0.85rem;">AI</a>
                    <a href="categories.html" style="padding: 0.3rem 0.8rem; background: var(--bg-tertiary); color: var(--text-secondary); border-radius: 9999px; font-size: 0.85rem;">Future</a>
                </div>

            </article>
        </div>
        
        <!-- Related Articles Divider -->
        <div style="border-top: 1px solid var(--border-color); margin: 4rem 0 3rem;"></div>

        <!-- Related Articles Section -->
        <section>
            <h3 style="margin-bottom: 2rem;">Related Articles</h3>
            <div class="blog-grid" style="margin-top: 0;">
                <!-- Article 1 -->
                <article class="blog-card">
                    <a href="article.html" class="blog-image-link" style="display: block;"><img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=400&q=80" alt="Remote Work" class="blog-image" style="height: 180px;"></a>
                    <div class="blog-content" style="padding: 1rem;">
                        <span class="blog-category">Business</span>
                        <h3 class="blog-title" style="font-size: 1.1rem;"><a href="article.html" style="color: inherit; text-decoration: none;">Mastering the Remote Workspace</a></h3>
                    </div>
                </article>

                <!-- Article 2 -->
                <article class="blog-card">
                    <a href="article.html" class="blog-image-link" style="display: block;"><img src="https://images.unsplash.com/photo-1639762681485-074b7f938ba0?auto=format&fit=crop&w=400&q=80" alt="Web3" class="blog-image" style="height: 180px;"></a>
                    <div class="blog-content" style="padding: 1rem;">
                        <span class="blog-category">Technology</span>
                        <h3 class="blog-title" style="font-size: 1.1rem;"><a href="article.html" style="color: inherit; text-decoration: none;">Understanding Web3 Fundamentals</a></h3>
                    </div>
                </article>

                <!-- Article 3 -->
                <article class="blog-card">
                    <a href="article.html" class="blog-image-link" style="display: block;"><img src="https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=400&q=80" alt="Privacy" class="blog-image" style="height: 180px;"></a>
                    <div class="blog-content" style="padding: 1rem;">
                        <span class="blog-category">Technology</span>
                        <h3 class="blog-title" style="font-size: 1.1rem;"><a href="article.html" style="color: inherit; text-decoration: none;">Data Privacy in the Modern Age</a></h3>
                    </div>
                </article>
            </div>
        </section>

    </main>

"""

with open('article.html', 'w', encoding='utf-8') as f:
    f.write(top_part + new_body + bottom_part)

print("Updated article.html")
