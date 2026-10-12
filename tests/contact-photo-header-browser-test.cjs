const fs=require('fs'),path=require('path');
const puppeteer=require('/home/thaionline/.npm/_npx/4b4c857f6efdfb61/node_modules/puppeteer');
const mode=process.argv[2],out=process.argv[3],root='/home/thaionline/tmp-parity/contact-photo-header-20261012';
const own=path.resolve(__dirname,'..');
if(!['fixture','candidate','live'].includes(mode)||!out)throw Error('fixture|candidate|live OUTPUT required');
fs.mkdirSync(out,{recursive:true,mode:0o700});
const checks=[],failed=[],pages=[];
function check(name,pass,actual){const c={name,pass:!!pass,actual};checks.push(c);if(!pass)failed.push(c);}
async function inject(page,kind){
 if(mode!=='candidate')return;
 await page.addStyleTag({path:path.join(own,'wp-theme/style.css')});
 if(kind==='home')await page.evaluate(html=>document.querySelector('.topButtons .sn').outerHTML=html,fs.readFileSync(root+'/candidate-social.html','utf8'));
 if(kind==='contact'){
  await page.evaluate(html=>document.querySelector('#support').outerHTML=html,fs.readFileSync(root+'/candidate-contact.html','utf8'));
  await page.addStyleTag({path:path.join(own,'wp-plugin/assets/recaptcha.css')});
  await page.addScriptTag({path:path.join(own,'wp-plugin/assets/recaptcha.js')});
 }
 if(kind==='photo'||kind==='detail'){
  await page.evaluate(html=>document.querySelector('.thai-gallery-page').outerHTML=html,fs.readFileSync(root+'/candidate-photo'+(kind==='detail'?'-detail':'')+'.html','utf8'));
  await page.addScriptTag({path:path.join(own,'wp-theme/assets/photo-viewer.js')});
 }
}
(async()=>{const b=await puppeteer.launch({executablePath:'/home/thaionline/.cache/puppeteer/chrome/linux-153.0.8010.36/chrome-linux64/chrome',args:['--no-sandbox','--disable-dev-shm-usage'],headless:true});
try{
 if(mode==='fixture'){
  for(const width of [1440,402,320]){
   const p=await b.newPage();await p.setViewport({width,height:874});
   await p.setContent(fs.readFileSync(root+'/candidate-contact.html','utf8'));
   await p.evaluate(()=>{window.fixtureToken='';window.grecaptcha={render:(container,options)=>{window.fixtureOptions=options;return 0;},getResponse:()=>window.fixtureToken,reset:()=>{window.fixtureToken='';}};});
   await p.addScriptTag({path:path.join(own,'wp-plugin/assets/recaptcha.js')});
   check('contact fixture '+width+' initially locked',await p.$eval('button[type=submit]',e=>e.disabled));
   check('contact fixture '+width+' empty response blocked',await p.$eval('form',f=>!f.dispatchEvent(new Event('submit',{cancelable:true,bubbles:true}))));
   await p.evaluate(()=>{window.fixtureToken='fixture-token';window.fixtureOptions.callback(window.fixtureToken);});
   check('contact fixture '+width+' verified callback enables',await p.$eval('button[type=submit]',e=>!e.disabled));
   await p.evaluate(()=>window.fixtureOptions['expired-callback']());
   check('contact fixture '+width+' expiry disables',await p.$eval('button[type=submit]',e=>e.disabled));
   await p.evaluate(()=>window.fixtureOptions['error-callback']());
   check('contact fixture '+width+' provider error disables and explains',await p.$eval('form',f=>f.querySelector('button').disabled&&!f.querySelector('[data-thai-recaptcha-status]').hidden));
   await p.evaluate(()=>{window.fixtureToken='fixture-token';window.fixtureOptions.callback(window.fixtureToken);});
   check('contact fixture '+width+' first verified submit allowed',await p.$eval('form',f=>f.dispatchEvent(new Event('submit',{cancelable:true,bubbles:true}))));
   check('contact fixture '+width+' duplicate submit blocked',await p.$eval('form',f=>!f.dispatchEvent(new Event('submit',{cancelable:true,bubbles:true}))));
   await p.evaluate(()=>{const e=new Event('pageshow');Object.defineProperty(e,'persisted',{value:true});window.dispatchEvent(e);});
   check('contact fixture '+width+' back-forward cache resets challenge',await p.$eval('button[type=submit]',e=>e.disabled)&&await p.evaluate(()=>window.fixtureToken===''));
   await p.close();
  }
 }else{
  const render=JSON.parse(fs.readFileSync(root+'/candidate-render.json','utf8'));
  for(const width of [1440,402,320]){
   for(const kind of (process.argv[4]==='visual'?['home','photo']:process.argv[4]==='contact'?['contact']:process.argv[4]==='detail'?['detail']:['home','contact','photo'])){
    const p=await b.newPage();await p.setViewport({width,height:874});const errors=[],nonReads=[],blockedOther=[];
    p.on('pageerror',e=>errors.push(e.message));await p.setRequestInterception(true);
    p.on('request',r=>{
     if(!['GET','HEAD'].includes(r.method())){(r.method()==='OPTIONS'?blockedOther:nonReads).push({method:r.method(),url:r.url()});return r.abort();}
     if(mode==='candidate'&&r.url().includes('/assets/social-icons.svg'))return r.respond({status:200,contentType:'image/svg+xml',body:fs.readFileSync(path.join(own,'wp-theme/assets/social-icons.svg'),'utf8')});
     r.continue();
    });
    const url='https://new.thai-online.org/'+(kind==='home'?'':kind==='contact'?'contact/':kind==='detail'?render.photo_detail_url.replace(/^\//,''):'photo/');
    const resp=await p.goto(url,{waitUntil:'networkidle2',timeout:60000});await p.waitForFunction(()=>{const pre=document.getElementById('preloader');return !pre||getComputedStyle(pre).visibility==='hidden'||getComputedStyle(pre).display==='none';},{timeout:15000});await inject(p,kind);
    check(kind+' '+width+' HTTPS 200',resp.status()===200);
    if(kind==='home'){
     await p.waitForSelector('.thai-social-icon');
     const s=await p.evaluate(()=>{
      const nav=document.querySelector('#mobile-navigation-button').getBoundingClientRect(),sn=document.querySelector('.topButtons .sn').getBoundingClientRect();
      return {links:[...document.querySelectorAll('.topButtons .sn a')].map(a=>({href:a.href,label:a.getAttribute('aria-label'),w:a.getBoundingClientRect().width,h:a.getBoundingClientRect().height})),icons:[...document.querySelectorAll('.topButtons .sn svg')].map(s=>({w:s.getBoundingClientRect().width,h:s.getBoundingClientRect().height,href:s.querySelector('use').getAttribute('href')})),nav:{top:nav.top,bottom:nav.bottom},social:{top:sn.top,bottom:sn.bottom}};
     });
     check('home '+width+' all nine original social links restored',s.links.length===9&&s.icons.length===9,s);
     check('home '+width+' accessible vector icons have consistent scale',s.links.every(a=>a.label&&a.w>=32&&a.h>=40)&&s.icons.every(a=>a.w===22&&a.h===22));
     if(width<761){
      check('home '+width+' menu and socials do not overlap',s.social.top>=s.nav.bottom+8,s);
      await p.click('#mobile-navigation-button');
      check('home '+width+' menu opens',await p.$eval('#mobile-navigation-button',e=>e.getAttribute('aria-expanded')==='true'));
      await p.click('#mobile-navigation-button');
      check('home '+width+' menu closes',await p.$eval('#mobile-navigation-button',e=>e.getAttribute('aria-expanded')==='false'));
     }
     await p.screenshot({path:path.join(out,'home-'+width+'.png')});
    }
    if(kind==='contact'){
     await p.$eval('#support [data-thai-recaptcha]', e=>e.scrollIntoView({block:'center'}));
     await p.waitForSelector('#support iframe[src*="/recaptcha/"][src*="/anchor"]',{timeout:30000});
     const frame=await (await p.$('#support iframe[src*="/recaptcha/"][src*="/anchor"]')).contentFrame();
     await frame.waitForSelector('#recaptcha-anchor-label',{timeout:30000});
     const state=await p.$eval('#support',f=>{
      const c=f.querySelector('[data-thai-recaptcha]'),i=f.querySelector('iframe'),r=i.getBoundingClientRect(),message=f.querySelector('textarea[name=message]').getBoundingClientRect(),button=f.querySelector('button').getBoundingClientRect();
      return {locked:f.querySelector('button').disabled,bodyAbove:message.bottom<=r.top,buttonBelow:button.top>=r.bottom,x:r.x,right:r.right,w:r.width,h:r.height,client:c.clientWidth};
     });
     check('contact '+width+' real Google v2 checkbox under message and above send',state.locked&&state.bodyAbove&&state.buttonBelow,state);
     check('contact '+width+' widget fits viewport',state.x>=0&&state.right<=width+1,state);
     check('contact '+width+' Google language correct',await frame.$eval('#recaptcha-anchor-label',e=>e.textContent.replace(/\s+/g,' ').trim()==='Я не робот'));
     await p.$eval('[data-thai-recaptcha]',e=>e.scrollIntoView({block:'center'}));await p.screenshot({path:path.join(out,'contact-'+width+'.png')});
    }
    if(kind==='photo'){
     const first=await p.$eval('.thai-gallery a.thai-lightbox',a=>({image:a.href,booking:a.dataset.bookingUrl,detail:a.dataset.url}));
     check('photo '+width+' published-tour mapping present',first.booking.includes('/shop/169/desc/'),first);
     await p.click('.thai-gallery a.thai-lightbox');
     await p.waitForSelector('dialog.thai-photo-viewer[open]');await p.waitForFunction(()=>{const i=document.querySelector('.thai-photo-viewer-image');return i.complete&&i.naturalWidth>0;});
     check('photo '+width+' only new viewer opens',await p.$$eval('dialog[open]',els=>els.length===1));
     check('photo '+width+' booking transition restored',await p.$eval('.thai-photo-viewer-footer',(a,url)=>a.href===url&&a.textContent.includes('Забронировать'),first.booking));
     check('photo '+width+' canonical photo transition restored',await p.$eval('.thai-photo-viewer-detail',(a,url)=>a.href===new URL(url,location.href).href,first.detail));
     const geom=await p.evaluate(()=>{
      const selectors=['.thai-photo-viewer-close','.thai-photo-viewer-toolbar','.thai-photo-viewer-frame','.thai-photo-viewer-footer'];
      return selectors.map(selector=>{const r=document.querySelector(selector).getBoundingClientRect();return {selector,x:r.x,y:r.y,right:r.right,bottom:r.bottom,w:r.width,h:r.height};});
     });
     check('photo '+width+' close/image/CTA fit with viewport margins',geom.every(g=>g.x>=6&&g.y>=6&&g.right<=width-6&&g.bottom<=874-6),geom);
     await p.screenshot({path:path.join(out,'photo-'+width+'.png')});
     await p.click('.thai-photo-viewer-next');check('photo '+width+' next works',await p.$eval('.thai-photo-viewer-image',(i,src)=>i.src!==src,first.image));
     await p.keyboard.press('ArrowLeft');check('photo '+width+' keyboard previous works',await p.$eval('.thai-photo-viewer-image',(i,src)=>i.src===src,first.image));
     await p.click('.thai-photo-viewer-zoom');check('photo '+width+' original size works',await p.$eval('.thai-photo-viewer-zoom',e=>e.getAttribute('aria-pressed')==='true'));
     check('photo '+width+' original-size controls stay on screen',await p.$$eval('.thai-photo-viewer-toolbar,.thai-photo-viewer-footer',els=>els.every(e=>{const r=e.getBoundingClientRect();return r.left>=6&&r.right<=innerWidth-6&&r.top>=6&&r.bottom<=innerHeight-6;})));
     await p.click('.thai-photo-viewer-zoom');check('photo '+width+' fit size restored',await p.$eval('.thai-photo-viewer-zoom',e=>e.getAttribute('aria-pressed')==='false'));
     await p.click('.thai-photo-viewer-close');check('photo '+width+' close and cleanup work',await p.$$eval('dialog[open]',e=>e.length===0));
     check('photo '+width+' opener focus restored',await p.$eval('.thai-gallery a.thai-lightbox',a=>document.activeElement===a));
     await p.click('.thai-gallery a.thai-lightbox');await p.keyboard.press('Escape');check('photo '+width+' Escape closes',await p.$$eval('dialog[open]',e=>e.length===0));
    }
    if(kind==='detail'){
     await p.click('.thai-gallery a.thai-lightbox');await p.waitForSelector('dialog.thai-photo-viewer[open]');
     await p.waitForFunction(()=>{const i=document.querySelector('.thai-photo-viewer-image');return i.complete&&i.naturalWidth>0;});
     check('detail '+width+' booking CTA mapped to published tour',await p.$eval('.thai-photo-viewer-footer',e=>e.href.includes('/shop/169/desc/')&&e.textContent.includes('Забронировать')));
     check('detail '+width+' single-photo arrows hidden',await p.$$eval('.thai-photo-viewer-prev,.thai-photo-viewer-next',e=>e.every(x=>x.hidden)));
     check('detail '+width+' image fits viewport',await p.$eval('.thai-photo-viewer-image',e=>{const r=e.getBoundingClientRect();return r.width>100&&r.left>=6&&r.right<=innerWidth-6&&r.top>=6&&r.bottom<=innerHeight-6;}));
     await p.screenshot({path:path.join(out,'detail-'+width+'.png')});await p.click('.thai-photo-viewer-close');
     check('detail '+width+' close works',await p.$$eval('dialog[open]',e=>e.length===0));
    }
    check(kind+' '+width+' no horizontal overflow',await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
    check(kind+' '+width+' no JS error',errors.length===0,errors);check(kind+' '+width+' no forms submitted',nonReads.length===0,nonReads);
    pages.push({kind,width,errors,nonReads,blockedOther});await p.close();
   }
  }
 }
}finally{await b.close();}
const result={mode,tests:checks.length,passed:checks.filter(c=>c.pass).length,failed,checks,pages,real_submissions:0,captcha_solved:false};
fs.writeFileSync(path.join(out,'results.json'),JSON.stringify(result,null,2));console.log(JSON.stringify({mode,tests:result.tests,passed:result.passed,failed}));if(failed.length)process.exitCode=1;
})().catch(e=>{console.error(e.stack);process.exitCode=1});
