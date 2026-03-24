import re

content = open('index.html', 'r', encoding='utf-8').read()

images = [
    'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?auto=format&fit=crop&w=800&q=80'
]

idx = 0

def replace_func(match):
    global idx
    if idx < len(images):
        img_url = images[idx]
    else:
        img_url = images[0]
    idx += 1
    
    part1 = match.group(1).replace('<img src="', '<a href="article.html" class="blog-image-link" style="display: block;"><img src="')
    part3 = match.group(3).replace('class="blog-image">', 'class="blog-image"></a>')
    title_wrapped = f'<a href="article.html" style="color: inherit; text-decoration: none;">{match.group(4)}</a>'
    
    return part1 + img_url + part3 + title_wrapped + match.group(5)

pattern = r'(<article class="blog-card">\s*<img src=")(images/placeholder\.svg)(".*?<h3 class="blog-title">)(.*?)(</h3>)'
new_content = re.sub(pattern, replace_func, content, flags=re.DOTALL)

with open('index.html', 'w', encoding='utf-8') as f:
    f.write(new_content)

print("Updated index.html")
