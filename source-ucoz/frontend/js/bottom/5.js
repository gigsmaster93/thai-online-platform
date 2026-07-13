 function getClientWidth(){ 
 var width=(window.innerWidth)?window.innerWidth:
 ((document.all)?document.body.offsetWidth:null);
 return width;}
 
 function getClientHeight(){ 
 var Height=(window.innerHeight)?window.innerHeight:
 ((document.all)?document.body.offsetHeight:null);
 return Height;} 
 

 
 if(getClientWidth()<835){
 // выпадающее меню моб версия 
 $("#uNMenuDiv1 > ul > li:nth-child(2) > a, #uNMenuDiv1 > ul > li:nth-child(3) > a").click(function(t){
 t.preventDefault();$(this).parent().find("ul").css("position","relative").toggle();}); 
 }else{
 // выпадающее меню не моб версия
$("#uNMenuDiv1 li").mouseover(function(){if($("ul.subM:visible").is("ul")){$("ul.subM:visible").hide()}});
$("#uNMenuDiv1 li:nth-child(2) , #uNMenuDiv1 li:nth-child(3)").mouseover(function(){
$(this).find(">ul").show();});
$("ul.subM").mouseout(function(){$(this).hide();}) 
 
 // cashback всплывающее инфо
 $(".cashback").mouseover(function(){
$(this).prev().show();
 });
 $(".list-item").mouseover(function(){if(!$(this).find(".cashbackTitle:visible").is("span")){$(".cashbackTitle:visible").hide();}})
 }
 
 
 
 
function gridWidth(a){

var par1=360;
var par2=320;
var baseW=1200;var baseFont=24;var baseFont2=14;var baseFW=150;
 var W=getClientWidth();
 var H=getClientHeight();

var proc=((par1*100)/baseW)/100;
var newW;
var newH;var pW=150;
var newFont;var newFont2;
var newFW;
 if(getClientWidth()>=1260){
 
 
 baseW=1200;
 proc=0.3; 
 
newW=(baseW*proc);
newH=(par2/par1)*newW;

var cntrY=0;
 var tgy=10;
 var tgx=25;
$(a).each(function(){
 
 if(!$(this).is(".catalog-item") && !$(this).is(".fixed")){
 
 
 if(cntrY==0){tgy=1;tgx=2.5;}else if(cntrY==2){tgx=1;tgy=0;}else{tgy=0;tgx=2.5;}
 
 
$(this).css({"width":newW-(newW*0.025),"height":newH-(newH*0.025),"margin-right":tgx+"%","margin-left":tgy+"%"}).addClass("fixed");
$(this).find(".product-url").css({"width":newW-(newW*0.025),"height":newH-(newH*0.025)});
$(this).find(".sml-img").css({"height":(newH-2)});
 
 
 if(cntrY==2){cntrY=-1;tgx=2.5;tgy=1;}

 cntrY++;
} 
 
 
}); 
 }else if(getClientWidth()<1260 && getClientWidth()>=768){
 
 baseW=$(".width").width();
 proc=0.305; 
 
newW=(baseW*proc);
newH=(par2/par1)*newW;

var cntrY=0;
 var tgy=10;
 var tgx=25;
$(a).each(function(){
 
 if(!$(this).is(".catalog-item") && !$(this).is(".fixed")){
 
 
 if(cntrY==0){tgy=1;tgx=2.5;}else if(cntrY==2){tgx=1;tgy=0;}else{tgy=0;tgx=2.5;}
 
 
 $(this).css({"width":newW,"height":newH,"margin-right":tgx+"%","margin-left":tgy+"%"}).addClass("fixed");
$(this).find(".product-url").css({"width":newW,"height":newH});
$(this).find(".sml-img").css({"height":(newH-2)});
 
 
 if(cntrY==2){cntrY=-1;tgx=2.5;tgy=1;}

 cntrY++;
} 
 
 
});

}else if(getClientWidth()>=600 && getClientWidth()<768){

 W=W-(W*0.05);
 proc=0.45;
 
newW=(W*proc);
newH=(par2/par1)*newW;

 
$(a).each(function(){
 
 
 
 if(!$(this).is(".catalog-item") && !$(this).is(".fixed")){
 
$(this).css({"width":newW,"height":newH,"margin":"2.5%"}).addClass("fixed");
$(this).find(".product-url").css({"width":newW,"height":newH});
$(this).find(".sml-img").css({"height":(newH-2)});
 
$(this).find(".sml-meta").css({"font-size":newFW});
}
 
 
 
}); 
 
}else{ 
newW=(W-(W*0.1));
newH=(par2/par1)*newW;
 // console.log('else' + " w: " +newW)
$(a).each(function(){
 
if(!$(this).is(".catalog-item") && !$(this).is(".fixed")){
$(this).css({"width":newW,"height":newH,"margin":"auto"}).addClass("fixed");
$(this).find(".product-url").css({"width":newW,"height":newH});
$(this).find(".sml-img").css({"height":(newH-2)});
$(this).find(".sml-price").css({"font-size":newFont});
$(this).find(".sml-price.right").css({"font-size":newFont2}); 
$(this).find(".sml-meta").css({"font-size":newFW});
} 
});

}




}
 
 
 $(document).ready(function(){setInterval(function(){gridWidth(".list-item");transL()},1000);});
 
 $(window).resize(function(){
 $(".list-item").each(function(){
 if($(this).is(".fixed")){
 $(this).removeClass('fixed');}
 });
 
 gridWidth(".list-item");/*if($("#navigation").is(".active-mobile")){$("#navigation").removeClass("active-mobile");}*/
}); 
 