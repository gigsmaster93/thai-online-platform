(function($){
'use strict';

function getCookie(name){
  var match=document.cookie.match(new RegExp('(?:^|; )'+name.replace(/([.$?*|{}()\[\]\\/+^])/g,'\\$1')+'=([^;]*)'));
  return match?decodeURIComponent(match[1]):undefined;
}

function setCookie(name,value,days){
  var expires='';
  if(days){
    var d=new Date();
    d.setTime(d.getTime()+days*24*60*60*1000);
    expires='; expires='+d.toUTCString();
  }
  document.cookie=name+'='+encodeURIComponent(value)+expires+'; path=/';
}

function bindCookieNotice(){
  var $notice=$('.notification--cookie');
  if(!$notice.length)return;

  if(!getCookie('notif')){
    $notice.show().removeClass('hide');
  }else{
    $notice.hide();
  }

  $notice.find('.notification_close').off('click.thaiCookie').on('click.thaiCookie',function(){
    setCookie('notif','1',365);
    $notice.addClass('hide');
    window.setTimeout(function(){$notice.hide();},250);
  });
}

function hidePreloader(){
  var p=document.getElementById('preloader');
  if(!p)return;
  p.classList.add('thai-preloader-hidden');
  window.setTimeout(function(){if(p&&p.parentNode)p.parentNode.removeChild(p);},250);
}

var legacyMediaObserver=null;

function applyLegacyMedia(el){
  var src=el.getAttribute('data-src');
  if(!src)return;

  if(el.tagName==='IMG'){
    if(!el.getAttribute('src'))el.setAttribute('src',src);
  }else{
    el.style.backgroundImage='url("'+src.replace(/"/g,'')+'")';
  }

  el.setAttribute('data-thai-lazy-loaded','1');
  if(legacyMediaObserver)legacyMediaObserver.unobserve(el);
}

function loadLegacyMedia(){
  var nodes=document.querySelectorAll('[data-src]:not([data-thai-lazy-loaded="1"])');

  if(!('IntersectionObserver' in window)){
    nodes.forEach(applyLegacyMedia);
    return;
  }

  if(!legacyMediaObserver){
    legacyMediaObserver=new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting)applyLegacyMedia(entry.target);
      });
    },{root:null,rootMargin:'250px 0px',threshold:0.01});
  }

  nodes.forEach(function(el){
    if(el.tagName==='IMG'&&el.closest('#main-product-page')){applyLegacyMedia(el);return;}
    if(el.getAttribute('data-thai-lazy-bound')==='1')return;
    el.setAttribute('data-thai-lazy-bound','1');
    legacyMediaObserver.observe(el);
  });
}

function updateLegacyTotal(showTotal){
  var total=0;
  var hasQty=false;

  $('.thai-person-qty').each(function(){
    hasQty=true;
    var qty=parseInt(this.value||'0',10);
    var price=parseFloat($(this).attr('data-price')||'0');
    if(!isFinite(qty)||qty<0)qty=0;
    if(!isFinite(price))price=0;

    var max=parseInt($(this).attr('max')||'0',10),min=parseInt($(this).attr('min')||'0',10);
    if(max>0)qty=Math.min(qty,max);if(min>0)qty=Math.max(qty,min);this.value=String(qty);
    var lineTotal=$(this).attr('data-fixed-price')==='1'?(qty>0?price:0):qty*price;
    total+=lineTotal;
    if(showTotal){
      $(this).closest('.numbers-row').find('.thai-line-total').text($(this).attr('data-transport')==='1'?String(price):lineTotal.toFixed(2));
    }
  });

  if(!hasQty){
    var $variant=$('#thai-price-group,#thai-tour-variant').first();
    if($variant.length){
      total=parseFloat($variant.val()||'0');
      if(!isFinite(total))total=0;
    }
  }

  if(showTotal){
    var formatted=(Math.round(total)===total)?String(total):total.toFixed(2);
    $('#total > span').text(formatted+' ฿');
    $('#total').stop(true,true).slideDown('slow');
    $('.thai-legacy-order-block .basket.now').stop(true,true).slideDown('slow');
  }
}

function legacyGridWidth(){
  var W=window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth;
  var H=window.innerHeight||document.documentElement.clientHeight||document.body.clientHeight;
  var par1=360,par2=320,newW,newH;
  var $items=$('.list-item').not('.catalog-item');

  $items.removeClass('fixed');

  if(W>=1260){
    newW=1200*0.3;
    newH=(par2/par1)*newW;

    $items.each(function(i){
      var col=i%3;
      var ml=col===0?1:0;
      var mr=col===2?1:2.5;
      var w=newW-(newW*0.025);
      var h=newH-(newH*0.025);
      $(this).css({width:w,height:h,marginRight:mr+'%',marginLeft:ml+'%'}).addClass('fixed');
      $(this).find('.product-url').css({width:w,height:h});
      $(this).find('.sml-img').css({height:(newH-2)});
    });
  }else if(W>=768){
    var baseW=$('.width').first().width()||W;
    newW=baseW*0.305;
    newH=(par2/par1)*newW;

    $items.each(function(i){
      var col=i%3;
      var ml=col===0?1:0;
      var mr=col===2?1:2.5;
      $(this).css({width:newW,height:newH,marginRight:mr+'%',marginLeft:ml+'%'}).addClass('fixed');
      $(this).find('.product-url').css({width:newW,height:newH});
      $(this).find('.sml-img').css({height:(newH-2)});
    });
  }else if(W>=600){
    newW=(W-(W*0.05))*0.45;
    newH=(par2/par1)*newW;

    $items.each(function(){
      $(this).css({width:newW,height:newH,margin:'2.5%'}).addClass('fixed');
      $(this).find('.product-url').css({width:newW,height:newH});
      $(this).find('.sml-img').css({height:(newH-2)});
    });
  }else{
    newW=W-(W*0.1);
    newH=(par2/par1)*newW;

    $items.each(function(){
      $(this).css({width:newW,height:newH,margin:'auto'}).addClass('fixed');
      $(this).find('.product-url').css({width:newW,height:newH});
      $(this).find('.sml-img').css({height:(newH-2)});
    });
  }
}

function bindLegacyTables(){
  if(window.innerWidth>=768)return;
  $('#dscr table').each(function(i){
    var table=this;if(table.getAttribute('data-thai-table-bound'))return;table.setAttribute('data-thai-table-bound','1');
    var link=document.createElement('a');link.href='#';link.className='tbls thai-table-open';link.textContent='Открыть таблицу ↓';link.setAttribute('role','button');table.before(link);
    link.addEventListener('click',function(e){e.preventDefault();var d=document.createElement('dialog');d.className='thai-table-dialog';var close=document.createElement('button');close.type='button';close.textContent='Закрыть';close.addEventListener('click',function(){d.close();});var wrap=document.createElement('div');wrap.className='thai-table-scroll';var copy=table.cloneNode(true);copy.removeAttribute('id');copy.querySelectorAll('[id]').forEach(function(n){n.removeAttribute('id');});wrap.append(copy);d.append(close,wrap);document.body.append(d);d.addEventListener('close',function(){d.remove();});d.addEventListener('click',function(ev){if(ev.target===d)d.close();});d.showModal();});
  });
}

function bindProductGallery(){
  var $sidebar=$('.thai-product-page .slideout-sidebar').first();
  if(!$sidebar.length)return;

  var albumUrl=$('.thai-product-page').first().attr('data-gallery-url')||'';
  if(!albumUrl&&typeof window.a_href==='string'&&window.a_href)albumUrl=window.a_href;
  if(!albumUrl){
    $sidebar.find('script').each(function(){
      var m=(this.textContent||'').match(/a_href\s*=\s*['"]([^'"]+)['"]/);
      if(m&&!albumUrl)albumUrl=m[1];
    });
  }
  if(!albumUrl){
    var firstSrc=$sidebar.find('img').first().attr('src')||'';
    var albumMatch=firstSrc.match(/\/_ph\/([0-9]+)\//);
    if(albumMatch)albumUrl='/photo/'+albumMatch[1];
  }

  var $images=$('.thai-product-page .shop-itempage-images').first();
  if($images.length&&!$images.find('.thai-product-actions').length){
    var $photo=$images.children('.photobuttonhidden').first();
    var $review=$images.children('a[href="#feedback"]').filter(function(){return $(this).find('.feedback').length;}).first();
    var $contact=$images.children('.contact-wrap').first();
    if($photo.length||$review.length||$contact.length){
      var $actions=$('<div>',{'class':'thai-product-actions'});
      $actions.insertBefore($photo.length?$photo:($review.length?$review:$contact));
      if($photo.length)$actions.append($photo);
      if($review.length)$actions.append($review.addClass('thai-product-review-action'));
      if($contact.length)$actions.append($contact);
    }
  }

  var $actionHost=$images.find('.thai-product-actions').first();
  if(albumUrl&&$actionHost.length&&!$images.find('.thai-product-gallery-link-wrap').length){
    var $galleryLink=$('<a>',{'class':'gall-icon thai-product-gallery-link',title:'Смотреть все фотографии экскурсии',href:albumUrl,target:'_blank',rel:'noopener','aria-label':'Перейти в галерею'});
    $actionHost.after($('<div>',{'class':'thai-product-gallery-link-wrap'}).append($galleryLink));
  }

  if($sidebar.attr('data-thai-gallery-bound')==='1')return;
  $sidebar.attr('data-thai-gallery-bound','1');

  var $slideout=$('.thai-product-page .slideout').first();
  var $toggle=$('#menu-toggle');
  if(!$toggle.length){
    $toggle=$('<input>',{type:'checkbox',id:'menu-toggle','aria-label':'Открыть фотогалерею'});
    $sidebar.before($toggle);
    $toggle.after($('<label>',{'for':'menu-toggle','class':'menu-icon',title:'Галерея','aria-hidden':'true'}));
    $sidebar.before($('<div>',{'class':'slideout-sidebar-shade','aria-hidden':'true'}));
  }
  if(!$sidebar.find('.go-gall').length&&albumUrl){
    var $all=$('<div>',{'class':'go-gall'}).css('height','60px');
    $all.append($('<a>',{'class':'gall-icon',title:'Смотреть все фотографии экскурсии',href:albumUrl,target:'_blank',rel:'noopener'}));
    $sidebar.append($all);
  }

  function warmImages(limit){
    $sidebar.find('img').slice(0,limit).each(function(){
      this.loading='eager';
      var src=this.currentSrc||this.getAttribute('src')||this.getAttribute('data-src')||'';
      if(src){var preload=new Image();preload.src=src;}
    });
  }
  warmImages(2);

  $slideout.remove();

  function setOpen(open){
    if(open)warmImages(6);
    $toggle.prop('checked',!!open);
    $('html,body').toggleClass('body-overflow',!!open);
  }

  $toggle.off('change.thaiGallery').on('change.thaiGallery',function(){setOpen(this.checked);});
  $('.slideout-sidebar-shade').off('click.thaiGallery').on('click.thaiGallery',function(){setOpen(false);});
  $(document).off('keydown.thaiGallery').on('keydown.thaiGallery',function(e){if(e.key==='Escape'&&$toggle.prop('checked'))setOpen(false);});

  var $triggers=$('label[for="menu-toggle"]').not('.menu-icon');
  var hasVisibleTrigger=$triggers.filter(function(){
    var visibleChild=this.querySelector('.feedback');
    var target=visibleChild||this;
    var r=target.getBoundingClientRect(),c=window.getComputedStyle(target);
    return r.width>0&&r.height>0&&c.display!=='none'&&c.visibility!=='hidden';
  }).length>0;
  if(!hasVisibleTrigger){
    var $host=$('.shop-itempage-images').first();
    if($host.length){
      var $fallback=$('<button>',{type:'button','class':'feedback thai-gallery-open'}).text('Смотреть фото');
      $fallback.on('click',function(){setOpen(true);});
      $host.prepend($fallback);
    }
  }
}

function bindFloatingOrderBlock(){
  var $box=$('.thai-product-page .rightbl .right').first();
  var $main=$('#main-product-page');
  if(!$box.length||!$main.length)return;

  $(window).off('scroll.thaiFloatOrder').on('scroll.thaiFloatOrder',function(){
    var viewport=window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth;
    var scrollTop=$(window).scrollTop();
    var headerH=$('.header').outerHeight()||0;
    var $page=$('#maincont');
    var pageW=$page.outerWidth()||0;
    var boxW=$box.parent().width()||$box.outerWidth();

    if(viewport>1028&&scrollTop>headerH+450){
      var rightPos=(viewport-pageW)/2-8;
      var stopAt=(headerH+450+$main.outerHeight())-$box.outerHeight()-100;
      if(scrollTop>stopAt){
        $box.css({
          position:'absolute',
          top:(headerH+500+$main.outerHeight())-$box.outerHeight()-95,
          right:rightPos,
          width:boxW
        });
      }else{
        $box.css({position:'fixed',top:'75px',right:rightPos,width:boxW});
      }
    }else{
      $box.css({position:'initial',top:'',right:'',width:'100%'});
    }
  }).triggerHandler('scroll.thaiFloatOrder');
}

function bindFoundCheaper(){
  var dialog=document.getElementById('foundCheaperDialog');
  if(!dialog)return;
  $('#foundCheaper').off('click.thaiCheaper').on('click.thaiCheaper',function(e){
    e.preventDefault();
    if(typeof dialog.showModal==='function')dialog.showModal();
    else dialog.setAttribute('open','open');
  });
  $(dialog).find('[data-thai-dialog-close]').off('click.thaiCheaper').on('click.thaiCheaper',function(){if(typeof dialog.close==='function')dialog.close();else dialog.removeAttribute('open');});
  $(dialog).off('click.thaiCheaper').on('click.thaiCheaper',function(e){if(e.target===dialog){if(typeof dialog.close==='function')dialog.close();else dialog.removeAttribute('open');}});
}

function bindLegacyProduct(){
  bindLegacyTables();
  bindProductGallery();
  bindFloatingOrderBlock();
  bindFoundCheaper();
  $('.contact-messenger').off('click.thai').on('click.thai',function(e){
    e.preventDefault();
    var $item=$(this);
    $('.contact-messenger-chosen').attr('src',$item.attr('data-icon')||'');
    $('.contact').attr('href',$item.attr('data-href')||'#');
  });

  $('#tabContainer > div').off('click.thai').on('click.thai',function(){
    $(this).addClass('tabActive').siblings().removeClass('tabActive');
  });

  $('#goToCalc,.priceupperbutton a').off('click.thai').on('click.thai',function(e){
    var target=document.getElementById('calculatey');
    if(!target)return;
    e.preventDefault();
    target.scrollIntoView({behavior:'smooth',block:'start'});
  });

  $('.thai-review-resource').off('change.thai').on('change.thai',function(){
    if(this.value)window.location.href=this.value;
  });

  $('#thai-transport').off('change.thai').on('change.thai',function(){
    var $opt=$(this).find(':selected');$('.thai-person-qty[data-transport]').attr('data-price',this.value).attr('max',$opt.attr('data-capacity')||13);updateLegacyTotal(true);
  });
  $('#thai-price-group').off('change.thai').on('change.thai',function(){
    var prices=JSON.parse($(this).find(':selected').attr('data-prices')||'[]');
    $('.thai-person-qty').each(function(i){$(this).attr('data-price',prices[i]||0);});
    updateLegacyTotal(true);
  });

  $('#thai-tour-variant').off('change.thai').on('change.thai',function(){ updateLegacyTotal(true); });

  $('.thai-person-qty').off('change.thai input.thai').on('change.thai input.thai',function(){
    var n=parseInt(this.value||'0',10);
    this.value=String(isFinite(n)&&n>=0?n:0);
    updateLegacyTotal(true);
  });

  $('.thai-qty-row .button_inc').off('click.thai keydown.thai').on('click.thai keydown.thai',function(e){
    if(e.type==='keydown' && e.key!=='Enter' && e.key!==' ')return;
    if(e.type==='keydown')e.preventDefault();

    var $input=$(this).siblings('.thai-person-qty');
    var n=parseInt($input.val()||'0',10);
    if(!isFinite(n)||n<0)n=0;

    if($(this).hasClass('inc'))n++;
    if($(this).hasClass('dec'))n=Math.max(0,n-1);

    $input.val(n);
    updateLegacyTotal(true);
  });

  $('#select-options').off('change.thai').on('change.thai',function(){
    var value=this.value;
    if(!value)return;

    var links={
      telegram:'tg://resolve?domain=thaionlinetours',
      whatsapp:'https://wa.me/66838383539',
      viber:'viber://chat?number=66838383539',
      line:'https://line.me/ti/p/~explosivepage',
      phone:'tel:+66838383539'
    };

    var href=links[value];
    if(!href)return;

    $('.altorder').attr('href',href).show();
    $('.basket.now').hide();
    $(this).closest('.type-select').addClass('hidden').removeClass('visible');
  });

  updateLegacyTotal(false);
}

$(function(){
  bindCookieNotice();
  loadLegacyMedia();
  legacyGridWidth();
  bindLegacyProduct();

  var $navigation=$('#navigation');
  var $mobileMenuButton=$('#mobile-navigation-button');
  var closeMobileMenu=function(){
    $navigation.removeClass('active-mobile top-menu-open');
    $mobileMenuButton.attr('aria-expanded','false');
    $('#navigation .uMenuRoot > li').removeClass('thai-submenu-open').children('.subM,.sub-menu').hide();
    $('#navigation .uMenuRoot > li > a').attr('aria-expanded','false');
  };
  var toggleMobileMenu=function(){
    var open=!$navigation.hasClass('active-mobile');
    if(open){
      $navigation.addClass('active-mobile top-menu-open');
      $mobileMenuButton.attr('aria-expanded','true');
    }else{
      closeMobileMenu();
    }
  };
  $mobileMenuButton.off('click.thaiMenu keydown.thaiMenu')
    .on('click.thaiMenu',toggleMobileMenu)
    .on('keydown.thaiMenu',function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();toggleMobileMenu();}});

  $('#navigation .uMenuRoot > li').has('> .subM, > .sub-menu').addClass('uWithSubmenu').each(function(){
    $(this).children('a').attr({'aria-haspopup':'true','aria-expanded':'false'});
  });
  $('#navigation .uMenuRoot > li > a').off('click.thaiSubmenu').on('click.thaiSubmenu',function(e){
    if((window.innerWidth||document.documentElement.clientWidth)>760)return;
    var $item=$(this).parent();
    var $submenu=$item.children('.subM,.sub-menu').first();
    if(!$submenu.length)return;
    e.preventDefault();
    var open=!$item.hasClass('thai-submenu-open');
    $item.toggleClass('thai-submenu-open',open).siblings().removeClass('thai-submenu-open').children('.subM,.sub-menu').hide();
    $submenu.stop(true,true).toggle(open);
    $(this).attr('aria-expanded',open?'true':'false');
    $item.siblings().children('a').attr('aria-expanded','false');
  });

  $('#tellnk').on('click',function(e){e.preventDefault();$('#telblock').stop(true,true).toggle();});

  var closeHeaderMenus=function(except){
    $('#shop-header-currency,#shop-header-profile').each(function(){
      if(except&&this===except)return;
      $(this).removeClass('show-dropmenu').children('a').attr('aria-expanded','false');
      $(this).children('.drop-area').attr('aria-hidden','true');
    });
  };
  $('#shop-header-currency > a,#shop-header-profile > a').off('click.thaiHeader').on('click.thaiHeader',function(e){
    e.preventDefault();
    var host=this.parentElement;
    var open=!host.classList.contains('show-dropmenu');
    closeHeaderMenus(host);
    $(host).toggleClass('show-dropmenu',open);
    $(this).attr('aria-expanded',open?'true':'false');
    $(host).children('.drop-area').attr('aria-hidden',open?'false':'true');
  });
  $(document).off('click.thaiHeaderDismiss keydown.thaiHeaderDismiss')
    .on('click.thaiHeaderDismiss',function(e){if(!$(e.target).closest('#shop-header-currency,#shop-header-profile').length)closeHeaderMenus();})
    .on('keydown.thaiHeaderDismiss',function(e){if(e.key==='Escape'){closeHeaderMenus();closeMobileMenu();}});

  $('#navigation li').on('mouseenter',function(){if((window.innerWidth||document.documentElement.clientWidth)>760)$(this).children('.subM,.sub-menu').stop(true,true).show();}).on('mouseleave',function(){if((window.innerWidth||document.documentElement.clientWidth)>760)$(this).children('.subM,.sub-menu').stop(true,true).hide();});
  $('#up-me').on('click',function(){window.scrollTo({top:0,behavior:'smooth'});});
  $('.goods-tab > ul a').on('click',function(e){e.preventDefault();var id=$(this).attr('href');$('.goods-tab > ul li').removeClass('active');$(this).parent().addClass('active');$('.goods-tab > .tab-body').hide();$(id).show();loadLegacyMedia();});
  window.setTimeout(hidePreloader,800);
});

window.addEventListener('load',function(){
  loadLegacyMedia();
  legacyGridWidth();
  bindLegacyProduct();
  hidePreloader();
});

var resizeTimer;
window.addEventListener('resize',function(){
  window.clearTimeout(resizeTimer);
  resizeTimer=window.setTimeout(function(){
    if((window.innerWidth||document.documentElement.clientWidth)>760){
      $('#navigation').removeClass('active-mobile top-menu-open');
      $('#mobile-navigation-button').attr('aria-expanded','false');
      $('#navigation .uMenuRoot > li').removeClass('thai-submenu-open').children('.subM,.sub-menu').removeAttr('style');
      $('#navigation .uMenuRoot > li > a').attr('aria-expanded','false');
    }
    legacyGridWidth();
    bindLegacyTables();
    bindFloatingOrderBlock();
  },120);
});

})(jQuery);

// Native image viewer for migrated photo albums.
document.addEventListener('click',function(e){var a=e.target.closest('a.thai-lightbox, .thai-forum a.ulightbox');if(!a||typeof HTMLDialogElement==='undefined')return;e.preventDefault();var d=document.createElement('dialog');d.className='thai-lightbox-dialog';var close=document.createElement('button');close.type='button';close.textContent='Закрыть';close.onclick=function(){d.close();};var img=document.createElement('img');img.src=a.href;img.alt=a.querySelector('img')?.alt||'';d.append(close,img);document.body.appendChild(d);d.addEventListener('close',function(){d.remove();});d.addEventListener('click',function(ev){if(ev.target===d)d.close();});d.showModal();});