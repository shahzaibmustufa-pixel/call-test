import os
import glob
import re

html_files = glob.glob('*.html')
missing_meta = []
broken_links = []

for file in html_files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()

    # Check Title
    if '<title>' not in content.lower():
        missing_meta.append(f"{file}: Missing <title>")

    # Check Meta Description
    if '<meta name="description"' not in content.lower() and "<meta name='description'" not in content.lower():
        missing_meta.append(f"{file}: Missing meta description")

    # Extract all local hrefs and srcs
    link_patterns = re.findall(r'(href|src)=["\'](.*?)["\']', content)
    for attr, link in link_patterns:
        # Ignore external links or empty links or anchor links
        if link.startswith('http') or link.startswith('//') or link.startswith('#') or link == '':
            continue
        
        # Resolve to a local path (relative to the html file)
        local_path = os.path.join(os.path.dirname(file), link)
        if not os.path.exists(local_path):
             broken_links.append(f"{file}: Broken link '{link}'")

print("--- SEO Meta Check ---")
if not missing_meta:
    print("All pages have titles and meta descriptions.")
else:
    for m in missing_meta:
        print(m)

print("\n--- Broken Link Check ---")
if not broken_links:
    print("No broken local links found.")
else:
    for bl in broken_links:
        print(bl)
