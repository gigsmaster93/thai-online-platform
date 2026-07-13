from pathlib import Path
import re, json

root = Path('/home/thaionline/thai-platform/source-ucoz/frontend')

files = list(root.rglob('*'))
html = [p for p in files if p.suffix.lower() in ('.html', '.htm')]
css = [p for p in files if p.suffix.lower() == '.css']
js = [p for p in files if p.suffix.lower() == '.js']
fonts = [p for p in files if p.suffix.lower() in ('.woff','.woff2','.ttf','.eot','.svg')]

macros = {}
links = {}

for p in html + css + js:
    txt = p.read_text(errors='ignore')
    found_macros = sorted(set(re.findall(r'\$[A-Z0-9_]+(?:\([^)]*\))?\$', txt)))
    found_urls = sorted(set(re.findall(r'''(?:src|href)=["']([^"']+)["']''', txt, flags=re.I)))
    if found_macros:
        macros[str(p.relative_to(root))] = found_macros
    if found_urls:
        links[str(p.relative_to(root))] = found_urls[:50]

report = {
    'html_count': len(html),
    'css_count': len(css),
    'js_count': len(js),
    'font_count': len(fonts),
    'html_files': [str(p.relative_to(root)) for p in html[:100]],
    'css_files': [str(p.relative_to(root)) for p in css[:100]],
    'js_files': [str(p.relative_to(root)) for p in js[:100]],
    'files_with_ucoz_macros': macros,
    'html_asset_links': links,
}

out = Path('/home/thaionline/thai-platform/docs/ucoz-frontend-map.json')
out.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')

print(json.dumps({
    'html_count': report['html_count'],
    'css_count': report['css_count'],
    'js_count': report['js_count'],
    'font_count': report['font_count'],
    'files_with_macros': len(macros),
    'report': str(out),
}, ensure_ascii=False, indent=2))
