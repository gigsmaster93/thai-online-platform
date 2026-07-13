// для ПРО

 var vichet=parseFloat($(".order-item-sum").text().split("฿")[0]);
 if($(".order-item-other9").text()==1){
var xvichet=(vichet*0.07);
 
 $(".order-item-sum, .order_topay_curr").text(parseInfo[0] + "฿"); 
var itog=parseFloat(parseInfo[0])-vichet;
 
 
 }else{
 
 var vichet=parseFloat($(".order-item-sum").text().split("฿")[0]);
 $(".order-item-sum, .order_topay_curr").text(parseInfo[0] + "฿"); 
 var itog=parseFloat(parseInfo[0])-vichet; 
 
 }