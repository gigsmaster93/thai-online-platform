(function($){
'use strict';

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

  $('#thai-tour-variant').off('change.thai').on('change.thai',function(){
    var n=parseFloat(this.value||0);
    if(!isFinite(n))return;
    $('#total > span').text(n.toFixed(2)+'฿');
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
}

$(function(){
  loadLegacyMedia();
  bindLegacyProduct();

  $('#mobile-navigation-button').on('click',function(){$('#navigation').toggleClass('top-menu-open');});
  $('#tellnk').on('click',function(e){e.preventDefault();$('#telblock').stop(true,true).toggle();});
  $('#shop-header-currency > a,#shop-header-profile > a').on('click',function(e){e.preventDefault();$(this).siblings('.drop-area').stop(true,true).toggle();});
  $('#navigation li').on('mouseenter',function(){$(this).children('.subM,.sub-menu').stop(true,true).show();}).on('mouseleave',function(){$(this).children('.subM,.sub-menu').stop(true,true).hide();});
  $('.notification_close').on('click',function(){$(this).closest('.notification').hide();});
  $('#up-me').on('click',function(){window.scrollTo({top:0,behavior:'smooth'});});
  $('.goods-tab > ul a').on('click',function(e){e.preventDefault();var id=$(this).attr('href');$('.goods-tab > ul li').removeClass('active');$(this).parent().addClass('active');$('.goods-tab > .tab-body').hide();$(id).show();loadLegacyMedia();});
  window.setTimeout(hidePreloader,800);
});

window.addEventListener('load',function(){
  loadLegacyMedia();
  bindLegacyProduct();
  hidePreloader();
});

})(jQuery);
