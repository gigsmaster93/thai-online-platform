var saveCntr=0;
 setTimeout(function(){
$("#tb-tags,#tb_exclude_from_yml,#yml_is_adult, #yml_is_delivery, #yml_is_pickup, #yml_is_store, #tb_hide, #tb_exclude_from_yml, #yml_is_adult ,#yml_is_delivery, #yml_is_pickup,#yml_is_store,#tb_hide").hide();
 },1000);
 // заменим h1 заголовок
 $("#cont-shop-add h1").text("Добавление Тура");
 
 // переименуем "Категория"
 $("#tb_category .manTd1").html('Выберите категорию <font class="manStar" color="red">*</font>:');
 
 // заменим название "Наименование"
$("#tb_name .manTd1").html('Название тура <font class="manStar" color="red">*</font>:');
 
 // заменим название "Вариант Тура / Места"
 $("#tb_other3 .manTd1").html('Варианты Туров / Мест:');
 
 // заменим название "Варианты Оплаты"
 $("#tb_other4 .manTd1").html('Варианты Оплаты:');
 
 // заменим название "С этим товаром покупают "
 $("#tb_recommended_products .manTd1").html('Похожие предложения:');
 
 
 // заменим название "Ссылка на галерею"
 $("#tb_other7 .manTd1").html('Ссылка на Галерею:<br>В формате: <i>ссылка</i>;<i>вкл/выкл</i>;<i>кол-во фото</i>');
 
 
 
 // добавим человеческий механизм работы с матрицей вариантов Такси и др
 $("#tb_other4 input").hide().attr("id","payField").before('<div id="payVars"><label><input type="checkbox" value="1">Оплата на счет в банке Таиланда</label><label><input type="checkbox" value="2">Оплата на счет в Российском банке</label><label><input type="checkbox" value="3">В офисе</label><label><input type="checkbox" value="4">В автобусе</label><label><input type="checkbox" value="5">Вызов представителя в отель</label></div>'); 
 // обработчик изменения данных из поля оплаты
 
 $("#payVars input").change(function(){
 var payVars="";
 $("#payVars input").each(function(){
 if($(this).is(":checked")){payVars+=$(this).val()+";"}
 });
 $("#payField").val(payVars.substring(0, payVars.length - 1));
 }); 
 
 // парсим уже внесённые данные из поля оплаты
 if($("#payField").val().length>1){
 var payVrnts=$("#payField").val().split(";");
 $("#payVars input").each(function(){ 
 for(l=0;l<payVrnts.length;l++){
 if(payVrnts[l]==$(this).val()){$(this).prop("checked","checked")} } 
 }); }
 
 // добавим возможность частичной оплаты
 
 $("#tb_other8 input").hide().attr("id","sepPayment").before('<label><input type="checkbox" value="1" id="sepPaymentInp"> Активировать</label> <span class="sepPaymentVal"><input type="number" value="50" max="100" min="0"> %</span>'); 
 
 // обработка флажка и поля с процентами
 $("#sepPaymentInp").click(function(){$(".sepPaymentVal").toggle();$("#sepPayment").val(50);if(!$(this).is(":checked")){$("#sepPayment").val("");console.log(1)}});
 $(".sepPaymentVal input").change(function(){$("#sepPayment").val($(this).val()) })
 $(".sepPaymentVal input").unbind("keyup").keyup(function(){$("#sepPayment").val($(this).val()) })
 
 if($("#sepPayment").val()!="" && $("#sepPayment").val().length>0){
 
 $("#sepPaymentInp").prop("checked","checked").parent().next().show().find("input").val(parseFloat($("#sepPayment").val()));
 }
 
 
 // добавим VAT Refund
 
 $("#tb_other9 input").hide().attr("id","vatRefund").before('<label><input type="checkbox" value="1" id="vatInp"> Активировать</label> '); 
 $("#vatInp").click(function(){if($(this).is(":checked")){$("#vatRefund").val($(this).val())}else{$("#vatRefund").val("")}});
 
 if($("#vatRefund").val()!=""){ $("#vatInp").prop("checked","checked"); }
 
 
 // перенесём второстепенные пункты вниз
 $("#tb_recommended_products").after($("#tb-tags, #tb_url, #tb_meta"));
 
 // спрячем заголовок яндекс импорта
 $("#tb_exclude_from_yml").prev().hide();
 
 
 // проверим выбрана ли КАТЕГОРИЯ и повесим обработчик
if($("#cat-select option:selected").val()!=0){$("#tb_name").show();}
 
$("#cat-select").change(function(){
$("#tb_name").slideDown('slow');taxiInitiate();});
 
 // проверим заполнено ли НАЗВАНИЕ и повесим обработчик
if($("#editf-name").val().length>0){$("#tb_dscr, #tb_img, #tb_price, #tb_price_old, #tb_other1").show();}
$("#editf-name").keyup(function(){
$("#tb_dscr, #tb_img, #tb_price, #tb_price_old, #tb_other1").slideDown('slow');});
 
 // проверим заполнена ли Цена и повесим обработчик
if($("#tb_price input").val().length>0 || $("#tb_price_old input").val().length>0){$("#tb_other2, #tb_other3,#tb_other4,#tb_other5,#tb_recommended_products").show();}
$("#tb_price input, #tb_price_old input").keyup(function(){
$("#tb_other2, #tb_other3,#tb_other4,#tb_other5,#tb_other6,#tb_other7,#tb_other8, #tb_recommended_products").slideDown('slow');});
 
 var cntr=0;
 
 // проверим заполнены ли Доп. Поля и повесим обработчик
if($("#tb_other2 input").val() || $("#tb_other3 input").val() || $("#tb_other4 input").val() || $("#tb_other5 input").val() || $("#tb_recommended_products input").val()){$("#tb-tags, #tb_url, #tb_meta, #tb_undisc,#tb_exclude_from_yml,#yml_is_adult, #yml_is_delivery, #yml_is_pickup, #yml_is_store, #tb_hide,#tb_url, #tb_meta, #tb_undisc, #tb_exclude_from_yml, #yml_is_adult ,#yml_is_delivery, #yml_is_pickup,#yml_is_store,#tb_hide").show();}
$("#tb_other2 input, #tb_other3 input,#tb_other4 input,#tb_other5 input,#tb_recommended_products input").keyup(function(){
$("#tb-tags, #tb_url, #tb_meta, #tb_undisc,#tb_exclude_from_yml,#yml_is_adult, #yml_is_delivery, #yml_is_pickup, #yml_is_store, #tb_hide,#tb_url, #tb_meta, #tb_undisc, #tb_exclude_from_yml, #yml_is_adult ,#yml_is_delivery, #yml_is_pickup,#yml_is_store,#tb_hide").slideDown('slow');

 
});