// Run with fixture, candidate or live; never solves a CAPTCHA or submits a live form.
const fs=require('fs'),path=require('path');
const puppeteer=require(process.env.THAI_QA_PUPPETEER||'/home/thaionline/.npm/_npx/4b4c857f6efdfb61/node_modules/puppeteer');
const repo=path.resolve(__dirname,'..'),mode=process.argv[2]||'fixture',dir=process.argv[3];
if(!['fixture','candidate','live'].includes(mode)||!dir)throw new Error('Specify mode and evidence directory');
fs.mkdirSync(dir,{recursive:true});
const js=fs.readFileSync(path.join(repo,'wp-plugin/assets/recaptcha.js'),'utf8');
const css=fs.readFileSync(path.join(repo,'wp-plugin/assets/recaptcha.css'),'utf8');
const widths=[1440,390,320],checks=[];
function add(width,name,pass,detail){checks.push({width,name,pass:!!pass,detail});if(!pass)throw new Error(name);}
const wait=ms=>new Promise(r=>setTimeout(r,ms));
(async()=>{const browser=await puppeteer.launch({headless:true,executablePath:process.env.THAI_QA_CHROME||'/home/thaionline/.cache/puppeteer/chrome/linux-153.0.8010.36/chrome-linux64/chrome',args:['--no-sandbox']});
try{for(const width of widths){const page=await browser.newPage(),errors=[],blocked=[];
page.on('pageerror',e=>errors.push(e.message));await page.setViewport({width,height:1000});await page.setRequestInterception(true);
page.on('request',req=>{if(mode==='fixture')return req.respond({status:200,contentType:'text/html',body:'<html><body>Fixture</body></html>'});if(!['GET','HEAD'].includes(req.method())){blocked.push(new URL(req.url()).pathname);return req.abort();}return req.continue();});
try{
if(mode==='fixture'){
 await page.setContent('<button id="foundCheaper">Нашли дешевле?</button><dialog id="foundCheaperDialog" class="thai-found-cheaper-dialog" aria-labelledby="foundCheaperTitle"><button type="button" data-thai-dialog-close aria-label="Закрыть">×</button><h2 id="foundCheaperTitle">Нашли дешевле?</h2><form><input type="hidden" name="action" value="thai_found_cheaper"><input type="hidden" name="product" value="484"><input type="hidden" name="_wpnonce" value="fixture-nonce"><p hidden><input name="website"></p><input name="offer_url" type="url" required><input name="offer_price" required><input name="email" type="email" required><input name="phone" required><div class="thai-recaptcha" data-thai-recaptcha data-sitekey="6LesJOMtAAAAACLMzyJ18Z4MMAeqVGxCY71YzxNW"></div><p data-thai-recaptcha-status hidden role="status"></p><button type="submit" disabled>Отправить</button></form></dialog>');
 await page.addStyleTag({content:fs.readFileSync(path.join(repo,'wp-theme/style.css'),'utf8')});
 await page.evaluate(()=>{window.fixture={token:'',renders:0,resets:0,options:null,allowed:false};window.grecaptcha={render:(c,options)=>{fixture.options=options;fixture.renders++;const f=document.createElement('iframe');f.title='Fixture checkbox';f.src='about:blank';f.style.width=(options.size==='normal'?304:158)+'px';f.style.height=(options.size==='normal'?78:136)+'px';c.append(f);const token=document.createElement('textarea');token.name='g-recaptcha-response';token.hidden=true;c.append(token);return 0;},getResponse:()=>fixture.token,reset:()=>{fixture.token='';fixture.resets++;}};const d=document.getElementById('foundCheaperDialog');d.querySelector('[data-thai-dialog-close]').onclick=()=>d.close();});
 await page.addStyleTag({content:css});await page.addScriptTag({content:js});
 await page.evaluate(()=>document.querySelector('form').addEventListener('submit',event=>{fixture.allowed=!event.defaultPrevented;event.preventDefault();}));
}else{
 const response=await page.goto('https://new.thai-online.org/shop/484/desc/black-pearl-2026',{waitUntil:'networkidle2',timeout:30000});add(width,'HTTP',response.status()===200,response.status());
 if(mode==='candidate'){
  await page.evaluate(()=>{const form=document.querySelector('#foundCheaperDialog form'),button=form.querySelector('[type=submit]');const c=document.createElement('div');c.className='thai-recaptcha';c.setAttribute('data-thai-recaptcha','');c.setAttribute('data-sitekey','6LesJOMtAAAAACLMzyJ18Z4MMAeqVGxCY71YzxNW');button.before(c);const s=document.createElement('p');s.setAttribute('data-thai-recaptcha-status','');s.setAttribute('role','status');s.hidden=true;button.before(s);button.disabled=true;});
  await page.addStyleTag({content:css});await page.addScriptTag({content:js});
 }
 await page.waitForSelector('#foundCheaperDialog[data-thai-recaptcha-dialog]');
 const lazy=await page.evaluate(()=>!document.querySelector('script[src*="google.com/recaptcha/api.js"]'));add(width,'Google lazy until open',lazy);
 await page.evaluate(()=>{const qty=document.querySelector('.thai-person-qty');if(qty){qty.value='2';qty.dispatchEvent(new Event('input',{bubbles:true}));}});await wait(700);
}
add(width,'submit initially blocked',await page.$eval('#foundCheaperDialog [type=submit]',e=>e.disabled));
await page.click('#foundCheaper');await page.waitForFunction(()=>document.querySelector('#foundCheaperDialog').open);
if(mode!=='fixture')await page.waitForSelector('[data-thai-recaptcha] iframe[src*="/anchor"]',{timeout:25000});
const layout=await page.evaluate(()=>{const d=document.getElementById('foundCheaperDialog'),a=d.getBoundingClientRect(),r=d.querySelector('[data-thai-recaptcha] iframe')?.getBoundingClientRect();return{modal:d.matches(':modal'),x:a.x,y:a.y,w:a.width,h:a.height,widget:r?{x:r.x,y:r.y,w:r.width,h:r.height}:null,overflow:document.documentElement.scrollWidth>innerWidth,shade:!document.querySelector('.thai-recaptcha-shade').hidden,locked:document.documentElement.classList.contains('thai-recaptcha-dialog-open'),focus:document.activeElement.name,parent:d.parentElement.tagName,z:getComputedStyle(d).zIndex};});
add(width,'Google challenge avoids native modal layer',!layout.modal&&layout.parent==='BODY',layout);
add(width,'dialog inside viewport',layout.x>=0&&layout.y>=0&&layout.x+layout.w<=width+.5&&layout.y+layout.h<=1000+.5,layout);
add(width,'widget fits dialog',layout.widget&&layout.widget.x>=layout.x&&layout.widget.x+layout.widget.w<=layout.x+layout.w+.5,layout);
add(width,'no overflow',!layout.overflow,layout);add(width,'shade and scroll lock',layout.shade&&layout.locked);add(width,'initial focus',layout.focus==='offer_url',layout.focus);

if(mode==='fixture'){
 add(width,'one widget rendered',await page.evaluate(()=>fixture.renders===1));
 add(width,'compact only when necessary',await page.evaluate(w=>fixture.options.size===(w===320?'compact':'normal'),width));
 // Isolated callback simulation; these values never reach a live server.
 await page.evaluate(()=>{const f=document.querySelector('form');f.dispatchEvent(new Event('submit',{bubbles:true,cancelable:true}));});
 add(width,'empty response rejected',await page.evaluate(()=>document.querySelector('[type=submit]').disabled&&!fixture.allowed));
 await page.evaluate(()=>{fixture.token='fixture-response';fixture.options.callback(fixture.token);});
 add(width,'callback enables submit',await page.$eval('[type=submit]',e=>!e.disabled));
 await page.evaluate(()=>{document.querySelector('form').dispatchEvent(new Event('submit',{bubbles:true,cancelable:true}));});
 // The fixture transport observes cancellation after the application listener.
 add(width,'verified response allowed and duplicate locked',await page.evaluate(()=>fixture.allowed&&document.querySelector('[type=submit]').disabled));
 await page.evaluate(()=>fixture.options['expired-callback']());add(width,'expiry blocks submit',await page.$eval('[type=submit]',e=>e.disabled));
 await page.evaluate(()=>fixture.options['error-callback']());add(width,'provider error blocks submit',await page.$eval('[type=submit]',e=>e.disabled));
 await page.evaluate(()=>{const pop=document.createElement('div');pop.id='fixtureChallenge';pop.style.cssText='position:fixed;top:0;left:0;z-index:2000000000';const frame=document.createElement('iframe');frame.src='https://www.google.com/recaptcha/api2/bframe?fixture-only';pop.append(frame);document.body.append(pop);});
 await page.keyboard.press('Escape');add(width,'Escape respects visible Google challenge',await page.$eval('#foundCheaperDialog',e=>e.open));
 await page.evaluate(()=>document.getElementById('fixtureChallenge').remove());
}else{
 await page.waitForSelector('iframe[src*="/recaptcha/api2/bframe"]',{timeout:15000});const anchorHandle=await page.$('[data-thai-recaptcha] iframe[src*="/anchor"]');const anchorFrame=await anchorHandle.contentFrame();await anchorFrame.waitForFunction(()=>document.body&&(/Я не робот|ERROR|ОШИБКА|Invalid/i.test(document.body.textContent)),{timeout:15000});const text=await anchorFrame.evaluate(()=>document.body.textContent);
 add(width,'real v2 checkbox loaded',text.includes('Я не робот')&&!/ERROR|ОШИБКА|Invalid/i.test(text),text);
 add(width,'unsolved response cannot submit',await page.$eval('#foundCheaperDialog [type=submit]',e=>e.disabled));
 const popup=await page.evaluate(()=>{const f=document.querySelector('iframe[src*="/recaptcha/api2/bframe"]');return f?{outside:!document.getElementById('foundCheaperDialog').contains(f),z:parseInt(getComputedStyle(f.parentElement).zIndex,10)}:null;});
 add(width,'Google popup layer is above form',popup&&popup.outside&&popup.z>Number(layout.z),popup);
}
await page.screenshot({path:path.join(dir,mode+'-open-'+width+'.png')});
await page.keyboard.press('Escape');await page.waitForFunction(()=>!document.getElementById('foundCheaperDialog').open&&!document.documentElement.classList.contains('thai-recaptcha-dialog-open'));
add(width,'Escape closes and restores focus',await page.evaluate(()=>document.activeElement.id==='foundCheaper'&&document.querySelector('.thai-recaptcha-shade').hidden));
await page.click('#foundCheaper');await page.waitForFunction(()=>document.getElementById('foundCheaperDialog').open);add(width,'reopen requires verification',await page.$eval('#foundCheaperDialog [type=submit]',e=>e.disabled));
await page.click('[data-thai-dialog-close]');await page.waitForFunction(()=>!document.getElementById('foundCheaperDialog').open&&document.querySelector('.thai-recaptcha-shade').hidden);add(width,'close control works',true);
await page.click('#foundCheaper');await page.waitForFunction(()=>document.getElementById('foundCheaperDialog').open);await page.mouse.click(1,500);await page.waitForFunction(()=>!document.getElementById('foundCheaperDialog').open&&document.querySelector('.thai-recaptcha-shade').hidden);add(width,'backdrop closes',true);
if(mode==='fixture')add(width,'close resets response',await page.evaluate(()=>fixture.resets===3&&fixture.token===''));
add(width,'no JS errors',errors.length===0,errors);
add(width,'no live application POST',!blocked.some(x=>/admin-post|wp-comments-post/.test(x)),blocked);
console.log(JSON.stringify({mode,width,pass:checks.filter(x=>x.width===width&&x.pass).length}));
}catch(error){const diagnostic=await page.evaluate(()=>({dialogOpen:document.getElementById('foundCheaperDialog')?.open,shadeHidden:document.querySelector('.thai-recaptcha-shade')?.hidden,framePaths:[...document.querySelectorAll('iframe')].map(e=>{const r=e.getBoundingClientRect();let path;try{path=new URL(e.src).pathname;}catch(x){path=e.src;}return{path,w:r.width,h:r.height,visibility:getComputedStyle(e).visibility};})})).catch(()=>null);console.log(JSON.stringify({mode,width,diagnostic}));await page.screenshot({path:path.join(dir,mode+'-failed-'+width+'.png')}).catch(()=>{});checks.push({width,name:'completed',pass:false,detail:String(error)});console.log(JSON.stringify({mode,width,error:String(error)}));}
finally{await page.close();}}
}finally{await browser.close();}
const result={mode,checks:checks.length,pass:checks.filter(x=>x.pass).length,failed:checks.filter(x=>!x.pass),captcha_solved:false,live_form_submitted:false};
fs.writeFileSync(path.join(dir,mode+'-checks.json'),JSON.stringify(result,null,2));console.log(JSON.stringify(result));if(result.failed.length)process.exitCode=1;
})().catch(e=>{console.error(String(e));process.exitCode=1;});
