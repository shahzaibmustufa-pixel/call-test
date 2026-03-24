import re

content = open('blog.html', 'r', encoding='utf-8').read()

images = [
    'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1639762681485-074b7f938ba0?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1507608616759-54f48f0af0ee?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80'
]

idx = 0

def replace_func(match):
    global idx
    img_url = images[idx] if idx < len(images) else images[0]
    idx += 1
    
    # Original match captures:
    # 1: `<article class="blog-card">\n                <img src="`
    # 2: `images/placeholder.svg`
    # 3: `" alt="Article Image" class="blog-image">\n                <div class="blog-content">\n                    <span class="blog-category">...</span>\n                    <h3 class="blog-title">`
    # 4: `Title`
    # 5: `</h3>`

    # Instead of fragile regex groups, replace specifically finding the placeholder and wrapping elements.
    # Group match approach:
    part1 = match.group(1).replace('<img src="', '<a href="article.html" class="blog-image-link" style="display: block;"><img src="')
    part3 = match.group(3).replace('class="blog-image">', 'class="blog-image"></a>')
    
    title_wrapped = f'<a href="article.html" style="color: inherit; text-decoration: none;">{match.group(4)}</a>'
    
    return part1 + img_url + part3 + title_wrapped + match.group(5)

pattern = r'(<article class="blog-card">\s*<img src=")(images/placeholder\.svg)(".*?<h3 class="blog-title">)(.*?)(</h3>)'
new_content = re.sub(pattern, replace_func, content, flags=re.DOTALL)

with open('blog.html', 'w', encoding='utf-8') as f:
    f.write(new_content)

print("Updated blog.html")
