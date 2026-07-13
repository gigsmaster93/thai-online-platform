/* Создадим функцию запускающую слайдер */
 
 function slideLch(){
 
 (function ( $ ) {

"use strict";

$(function () {

var masterslider_a757 = new MasterSlider();

// slider controls

masterslider_a757.control('arrows' ,{ autohide:true, overVideo:true });

masterslider_a757.control('slideinfo' ,{ autohide:false, overVideo:true, dir:'h', align:'bottom',inset:false , margin:10 });

// slider setup

masterslider_a757.setup("ms-staff-1", {
width : 240,
height : 240,
space : 35,
start : 1,
grabCursor : true,
swipe : true,
mouse : true,
layout : "partialview",
wheel : false,
autoplay : false,
instantStartLayers:false,
loop : true,
shuffle : false,
preload : 4,
heightLimit : true,
autoHeight : false,
smoothHeight : true,
endPause : false,
overPause : true,
fillMode : "fill",
centerControls : true,
layersMode : "center",
hideLayers : false,
fullscreenMargin: 0,
speed : 20,
dir : "h",
parallaxMode : 'swipe',
view : "focus"
});

});

})(jQuery);
 
 }
 
 /* конец */