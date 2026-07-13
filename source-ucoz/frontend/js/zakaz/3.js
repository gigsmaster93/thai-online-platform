 $("#order-table > table > tbody").eq(1).remove();
 
 $(".order-item-sum").text($("#order_topay").text());
 
 $("#order_topay").parent().hide();
 
 if($("#paytype").text()=="Оплата в офисе компании"){
$("#paytype").append('<div class="payIco" style="background:url(/img/payments/OfficePay.png) no-repeat;background-position: 0%;"></div>');
}

if($("#paytype").text()=="Онлайн через Яндекс.Деньги"){
$("#paytype").append('<div class="payIco" style="background: url(/img/payments/Yandex.png) no-repeat;background-position: 0%;"></div>');
}

if($("#paytype").text()=="Онлайн через PayPal"){
$("#paytype").append('<div class="payIco" style="background: url(/img/payments/PayPal.png) no-repeat;background-position: 0%;"></div>');
}

if($("#paytype").text()=="Оплата в автобусе"){
$("#paytype").append('<div class="payIco" style="background: url(/img/payments/BusPay.png) no-repeat;background-position: 0%;"></div>');
}
 if($("#paytype").text()=="Вызов представителя в отель"){
$("#paytype").append('<div class="payIco" style="background:url(/img/payments/Representative.png?v=2) no-repeat; background-position: 50%; background-size: 36%;"></div>');
} 

 $("#order-field-21-block").hide().next().hide();
 
 
 $("#refrVat").click(function(){

$('#shop-order-tax-value').val(0);
$('#shop-order-tax-form').focus().submit()

})


$("#decVat").click(function(){

$('#shop-order-tax-value').val(order_total_raw*parseFloat($('#percVat').val())/100*-1);
$('#shop-order-tax-form').focus().submit()

})