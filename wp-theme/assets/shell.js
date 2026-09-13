(function($){
'use strict';
function hidePreloader(){var p=document.getElementById('preloader');if(!p)return;p.classList.add('thai-preloader-hidden');window.setTimeout(function(){if(p&&p.parentNode)p.parentNode.removeChild(p);},250);}
function loadLegacyMedia(){document.querySelectorAll('[data-src]').forEach(function(el){var src=el.getAttribute('data-src');if(!src)return;if(el.tagName==='IMG'){if(!el.getAttribute('src'))el.setAttribute('src',src);}else{el.style.backgroundImage='url("'+src.replace(/"/g,'')+'")';}});}
$(function(){
  loadLegacyMedia();
  $('#mobile-navigation-button').on('click',function(){$('#navigation').toggleClass('top-menu-open');});
  $('#tellnk').on('click',function(e){e.preventDefault();$('#telblock').stop(true,true).toggle();});
  $('#shop-header-currency > a,#shop-header-profile > a').on('click',function(e){e.preventDefault();$(this).siblings('.drop-area').stop(true,true).toggle();});
  $('#navigation li').on('mouseenter',function(){$(this).children('.subM,.sub-menu').stop(true,true).show();}).on('mouseleave',function(){$(this).children('.subM,.sub-menu').stop(true,true).hide();});
  $('.notification_close').on('click',function(){$(this).closest('.notification').hide();});
  $('#up-me').on('click',function(){window.scrollTo({top:0,behavior:'smooth'});});
  $('.goods-tab > ul a').on('click',function(e){e.preventDefault();var id=$(this).attr('href');$('.goods-tab > ul li').removeClass('active');$(this).parent().addClass('active');$('.goods-tab > .tab-body').hide();$(id).show();loadLegacyMedia();});
  window.setTimeout(hidePreloader,800);
});
window.addEventListener('load',function(){loadLegacyMedia();hidePreloader();});
})(jQuery);
