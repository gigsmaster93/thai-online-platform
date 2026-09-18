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

function loadLegacyMedia(){
  document.querySelectorAll('[data-src]').forEach(function(el){
    var src=el.getAttribute('data-src');
    if(!src)return;
    if(el.tagName==='IMG'){
      if(!el.getAttribute('src'))el.setAttribute('src',src);
    }else{
      el.style.backgroundImage='url("'+src.replace(/"/g,'')+'")';
    }
  });
}

function updateLegacyTotal(){
  var total=0;
  var hasQty=false;

  $('.thai-person-qty').each(function(){
    hasQty=true;
    var qty=parseInt(this.value||'0',10);
    var price=parseFloat($(this).attr('data-price')||'0');
    if(!isFinite(qty)||qty<0)qty=0;
    if(!isFinite(price))price=0;
    total+=qty*price;
  });

  if(!hasQty){
    var $variant=$('#thai-tour-variant');
    if($variant.length){
      total=parseFloat($variant.val()||'0');
      if(!isFinite(total))total=0;
    }
  }

  if(total>0 || hasQty){
    $('#total > span').text(total.toFixed(2)+'฿');
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

function bindLegacyProduct(){
  $('.contact-messenger').off('click.thai').on('click.thai',function(e){
    e.preventDefault();
    var $item=$(this);
    $('.contact-messenger-chosen').attr('src',$item.attr('data-icon')||'');
    $('.contact').attr('href',$item.attr('data-href')||'#');
  });

  $('#tabContainer > div').off('click.thai').on('click.thai',function(){
    $(this).toggleClass('tabActive').siblings().removeClass('tabActive');
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

  $('#thai-tour-variant').off('change.thai').on('change.thai',updateLegacyTotal);

  $('.thai-person-qty').off('change.thai input.thai').on('change.thai input.thai',function(){
    var n=parseInt(this.value||'0',10);
    this.value=String(isFinite(n)&&n>=0?n:0);
    updateLegacyTotal();
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
    updateLegacyTotal();
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

  updateLegacyTotal();
}

$(function(){
  bindCookieNotice();
  loadLegacyMedia();
  legacyGridWidth();
  bindLegacyProduct();

  $('#mobile-navigation-button').on('click',function(){$('#navigation').toggleClass('top-menu-open');});
  $('#tellnk').on('click',function(e){e.preventDefault();$('#telblock').stop(true,true).toggle();});
  $('#shop-header-currency > a,#shop-header-profile > a').on('click',function(e){e.preventDefault();$(this).siblings('.drop-area').stop(true,true).toggle();});
  $('#navigation li').on('mouseenter',function(){$(this).children('.subM,.sub-menu').stop(true,true).show();}).on('mouseleave',function(){$(this).children('.subM,.sub-menu').stop(true,true).hide();});
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
  resizeTimer=window.setTimeout(legacyGridWidth,120);
});

})(jQuery);
