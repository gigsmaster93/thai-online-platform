$.get("/php/bonuses.php?act=readTours",function(data){

var totalVars = data;totalVars=totalVars.split(";");
 
 for(i=0;i<totalVars.length;i++){
 
 var bitemId=$(".order-item-name a").attr("href").split("/shop/")[1].split("/desc")[0];
 
 if(parseFloat(bitemId)==parseFloat(totalVars[i])){
 
 $.get("/php/bonuses.php?act=wrBonus&userId=user_id&orderId=order_id",function(data){
 
 console.log(data);
 
 if(data==301 || data==401 || data=="lvl1" || data=="lvl2" || data=="lvl3"){
/*$("h1").before('<a class="bonusLabel" href="/bonusprogram" target="_blank" title="Перейти к получению бонуса">Получить бонус!</a>');*/ // добавить ссылку на акцию с описанием её
 }
 });
 
 
 }

 }
});
 
 function moveTo(a){
 setTimeout(function(){
 $('html, body').stop().animate({
 scrollTop: $(a).offset().top-70
 }, 1100);
 },300); // задержка перед переходом 
}
 
 
moveTo(".payBl")