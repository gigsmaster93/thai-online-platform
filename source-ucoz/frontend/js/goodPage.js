$(".gphoto").click(function(){
 $(".gphoto").each(function(){if($(this).is(".selph")){$(this).toggleClass('selph');}});
 $(this).toggleClass('selph');
 });
 
 

 /* Плавающий правый блок */ 
 function floatBlock(){ 
 var rightblW=$(".rightbl .right").width(); // ширина правого блока 
 
 $(window).scroll(function(){ 
 if(getClientWidth()>1028 && $(this).scrollTop()>$(".header").height()+450){
 var rightblpos=(getClientWidth()-$("#maincont").width())/2-8; // right значение

 // проверим достигли ли мы низа
 if($(this).scrollTop()>($(".header").height()+450+$("#main-product-page").height())-$(".rightbl .right").height()-100){
 $(".rightbl .right").css({"position":"absolute","top":($(".header").height()+500+$("#main-product-page").height())-$(".rightbl .right").height()-95,"right":rightblpos,"width":rightblW});
 }else{
 
 $(".rightbl .right").css({"position":"fixed","top":"75px","right":rightblpos,"width":rightblW});} //13.9%
 }else{
 
 $(".rightbl .right").css({"position":"initial","width":"100%"}); 
 
 }

}); 
 }
 /* Плавающий правый блок */ 
 
 
$(window).bind('resize',function(){ stabilize();floatBlock() });
floatBlock();
 
 /* Оптимизируем таблицы для Моб. версии */
 var cntr=0;
 if(getClientWidth()<768){
 
 $("#dscr table").each(function(){
 
 $(this).attr("id","tbl"+cntr);
 
 $(this).before("<a href='javascript://' tblid='tbl"+cntr+"' class='tbl"+cntr+" tbls'>Открыть таблицу ↓</a>");
 
 
 cntr++;
 });
 
 
 setTimeout(function(){ $(".tbls").click(function(){
 $.fancybox($("#"+$(this).attr("tblid")).clone());
 });
 
 },1000); 
 }
 /* Оптимизируем таблицы для Моб. версии */
 
 

 
 
 $(".tabs").tabs();
 
 
 /* Сделаем возможность расширения правого блока */
 
 $("#order-form input").focus(function(){
 if( $("#order-form").css("height")!="auto"){
 $("#order-form").css({"height":"auto","overflow":"visible"});}
 });
 
 $("#order-form").mouseover(function(){
 if($(this).css("height")!="auto"){$("#order-form").css({"height":"auto","overflow":"visible"});}
 });
 
 $("#order-form").focusout(function(){ 
 $("#order-form").css({"height":"335px","overflow":"hidden"}); 
 });
 /* Сделаем возможность расширения правого блока */ 
 
 
/* Работа табов */
$("#tabContainer>div").click(function(){if($(".tabActive").is("div")){$(".tabActive").removeClass("tabActive")}$(this).addClass("tabActive")});
