$("#calVars .selected").click(function(){
$(".optholder").show();
});

$("#calVars .option").click(function(){
$(".selected").html($(this).clone());$(".optholder").hide();
});

 // функция округления до 10
 function fn(a, num) {return a % num ? a + num - a % num : a };
 var hasDone=0;
 $('#order-field-22-block').hide()
 $("#thai-banks > div").click(function(){ 
 
 $(this).find(".payIco input").prop("checked","checked");
 $("#thai-banks > div").each(function(){$(this).removeClass("sel")});
 $(this).addClass("sel");
 $('#order-field-22-block input').val($(this).find('b').text())
 })