from pathlib import Path
from datetime import datetime,timezone
from concurrent.futures import ThreadPoolExecutor
from html.parser import HTMLParser
import urllib.request, urllib.parse, json,hashlib,sys
root=Path(sys.argv[1]) if len(sys.argv)>1 else Path('/home/thaionline/tmp-parity/release-close-'+datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%SZ'))
assert root.resolve().is_relative_to(Path('/home/thaionline/tmp-parity')), 'Evidence must stay in tmp-parity'
assert not root.exists(), 'Never overwrite a source bundle; use a new directory'
root.mkdir(mode=0o700,parents=True)
urls={
158:'https://thai-online.org/shop/158/desc/siamskij-proliv-ekskursiya-v-taylande-v-2026',
170:'https://thai-online.org/shop/170/desc/siam-park-akvapark-v-bangkoke-parki-razvlecheniy-v-taylande-v-2026',
491:'https://thai-online.org/shop/491/desc/gorod-grekha-2026',
510:'https://thai-online.org/shop/510/desc/top-bangkok-2026'}

class Links(HTMLParser):
 def __init__(self):super().__init__();self.products={};self.pages=set()
 def handle_starttag(self,tag,attrs):
  if tag!='a':return
  import re
  h=dict(attrs).get('href','')
  m=re.search(r'/shop/(\d+)/desc/',h)
  if m:self.products[int(m[1])]=h
  m=re.search(r'/shop/all/(\d+)',h)
  if m:self.pages.add(int(m[1]))
def fetch(pair):
 name,url=pair
 req=urllib.request.Request(url,method='GET',headers={'User-Agent':'Mozilla/5.0 ThaiOnline readonly migration audit'})
 with urllib.request.urlopen(req,timeout=20) as r:
  assert r.status==200
  assert urllib.parse.urlparse(r.url).hostname in ('thai-online.org','www.thai-online.org')
  body=r.read()
  assert len(body)>1000
  path=root/(name+'.html');path.write_bytes(body);path.chmod(0o600)
  return {'name':name,'url':url,'final_url':r.url,'status':r.status,'fetched_at':datetime.now(timezone.utc).isoformat(),'path':str(path),'sha256':hashlib.sha256(body).hexdigest(),'bytes':len(body),'tls_verified':True}
pairs=[('old-product'+str(i),url) for i,url in urls.items()]
pairs += [('old-catalog'+str(p),'https://thai-online.org/shop/all'+('' if p==1 else '/'+str(p))) for p in range(1,11)]
with ThreadPoolExecutor(max_workers=4) as pool:rows=list(pool.map(fetch,pairs))
products={};pages=set();catalog=[]
for row in rows:
 if row['name'].startswith('old-catalog'):
  parser=Links();parser.feed(Path(row['path']).read_text())
  products.update(parser.products);pages.update(parser.pages)
  catalog.append({**row,'host':'thai-online.org','products':sorted(parser.products)})
catalog.sort(key=lambda r:int(r['name'].removeprefix('old-catalog')))
assert len(catalog)==10 and sorted(pages)==list(range(2,11))
assert not ({170,491}&products.keys()),'Previously cancelled products now listed; stop'
out={'checked_at':datetime.now(timezone.utc).isoformat(),'old_catalog_pages':catalog,'old_discovered_pages':sorted(pages),'old_catalog_count':len(products),'old_product_paths':products,'errors':[],'sources':{int(r['name'].removeprefix('old-product')):r for r in rows if r['name'].startswith('old-product')}}
path=root/'sources.json';path.write_text(json.dumps(out,ensure_ascii=False,indent=2));path.chmod(0o600)
print(json.dumps({'source_products':len(out['sources']),'catalog_pages':len(catalog),'active_products':len(products),'cancelled_absent':[170,491],'tls_verified':True,'at':out['checked_at']},ensure_ascii=False),flush=True)
