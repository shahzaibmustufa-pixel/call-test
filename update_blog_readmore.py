with open('blog.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Make sure we don't duplicate if script runs twice
if '>Read More</a>' not in content:
    content = content.replace(
        '<div class="blog-meta">',
        '<a href="article.html" style="margin-bottom: 1rem; font-weight: 600; font-size: 0.9rem; text-decoration: underline; display: inline-block;">Read More</a>\n                    <div class="blog-meta">'
    )
    with open('blog.html', 'w', encoding='utf-8') as f:
        f.write(content)
    print("Added Read More to blog.html")
else:
    print("Read More already present in blog.html")
