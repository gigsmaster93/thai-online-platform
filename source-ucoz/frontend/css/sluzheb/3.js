 var vichet=parseFloat($(".order-item-sum").text().split("฿")[0]);
 $(".order-item-sum, .order_topay_curr").text(parseInfo[0] + "฿"); 
 var itog=parseFloat(parseInfo[0])-vichet; 