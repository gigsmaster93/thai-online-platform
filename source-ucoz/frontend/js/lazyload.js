$(document).ready(function(){

  // start
  var pItem = document.getElementsByClassName('lazyload replacex'), timer;

 window.addEventListener('scroll', scroller, false);
   window.addEventListener('resize', scroller, false);
  inView();


  // throttled scroll/resize
  function scroller(e) {

    timer = timer || setTimeout(function() {
      timer = null;
     requestAnimationFrame(inView);
    }, 300);

  }


  // image in view?
  function inView() {

    var wT = window.pageYOffset, wB = wT + window.innerHeight, cRect, pT, pB, p = 0;
    while (p < pItem.length) {

      cRect = pItem[p].getBoundingClientRect();
      pT = wT + cRect.top;
      pB = pT + cRect.height;

      if (wT < pB + 400 && wB > pT -400) {
       loadFullImage(pItem[p]);
       pItem[p].classList.remove('replacex');
      }
      else p++;

    }

  }

  // replacex with full image
  function loadFullImage(item) {

if (item.src || item.style.background && !item.datasrc) return;

if(item.tagName.toLowerCase()=="img"){

if(getClientWidth()<=500 && $(item).is(".adopt")){
var xx=item.getAttribute("data-src").split(".");
item.src=xx[0]+"_500."+xx[1];
}else{
item.src=item.getAttribute("data-src");
}

/* mainCats
if(getClientWidth()<=768 && $(item).parent().parent().is(".catalog-item")){
var xx=item.getAttribute("data-src").split("mainCats");
item.src=xx[0]+"768px"+xx[1];
}else{
item.src=item.getAttribute("data-src");
}*/

}else if(item.tagName.toLowerCase()=="div" || item.tagName.toLowerCase()=="a" || item.tagName.toLowerCase()=="body"){

if(getClientWidth()<=500 && $(item).is(".adopt")){
var xx=item.getAttribute("data-src").split(".");
item.style.background="url("+xx[0]+"_500."+xx[1]+") no-repeat";
}else{
item.style.background="url("+item.getAttribute("data-src")+") no-repeat";
}


item.style.backgroundSize="110%";item.style.backgroundPosition="50% 50%";

if(getClientWidth()<=500){

if($(item).is("body")){item.style.backgroundSize="250%";item.style.backgroundAttachment="fixed";item.style.backgroundColor="#fff";}
if($(item).is(".videox")){item.style.backgroundSize="230%";item.style.backgroundAttachment="fixed";}
}else{

if($(item).is("body")){item.style.backgroundSize="100%";item.style.backgroundAttachment="fixed";item.style.backgroundColor="#fff";}
if($(item).is(".videox")){item.style.backgroundSize="100%";item.style.backgroundAttachment="fixed";}

}

}

}
});