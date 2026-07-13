$("#slider-range").slider({
 range: true,
 min: shopFilterMinPrice,
 max: shopFilterMaxPrice,
 values: [ shopFilterMinPrice, shopFilterMaxPrice ],
 slide: function( event, ui ) {
 
 $("#price_min").val(ui.values[0])
 $("#price_max").val(ui.values[1])
 setPriceFilter();
 }
});
 $("#flist-label-price").text("Цена, ฿: "); 
 $("#flist-item-price").find("button").eq(1).remove();


// функция для плавного перемещения к элементу
 
function moveTo(a){
 setTimeout(function(){
 $('html, body').stop().animate({
 scrollTop: $(a).offset().top-100
 }, 800);
 },700); // задержка перед переходом 
}

// функция парсинга меток


 var QueryString = function () {
 // This function is anonymous, is executed immediately and 
 // the return value is assigned to QueryString!
 var query_string = {};
 var query = window.location.search.substring(1);
 var vars = query.split("&");
 for (var i=0;i<vars.length;i++) {
 var pair = vars[i].split("=");
 // If first entry with this name
 if (typeof query_string[pair[0]] === "undefined") {
 query_string[pair[0]] = decodeURIComponent(pair[1]);
 // If second entry with this name
 } else if (typeof query_string[pair[0]] === "string") {
 var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
 query_string[pair[0]] = arr;
 // If third or later entry with this name
 } else {
 query_string[pair[0]].push(decodeURIComponent(pair[1]));
 }
 } 
 return query_string;
}();
 

 // функция для вывода совпадений
 
function con(c,d){
 var cntr=0;c=c.replace(/^[^.?!]+[.?!]\s*(.*)$/, '$1');d=d.replace(/^[^.?!]+[.?!]\s*(.*)$/, '$1');a=c.split(" ");b=d.split(" ");
 for(i=0;i<a.length;i++){
 for(l=0;l<b.length;l++){ 
if(a[i].toLowerCase()==b[l].toLowerCase()){cntr++;}}}
 return cntr;}
 
 
// получение максимального элемента массива
function getMaxValue(array){
 var max = array[0]; // берем первый элемент массива
 var indx=0;
 for (var i = 0; i < array.length; i++) { // переберем весь массив
 // если элемент больше, чем в переменной, то присваиваем его значение переменной
 if (max < array[i]){ max = array[i]; indx=i;}
 }
 // возвращаем индекс максимального значения
 return indx;}
 
 $(document).ready(function(){
if(QueryString.utm_term){

 
 /* Динамический заголовок */

$(".topbar").before('<div class="curzap">Вы искали:<b> "'+QueryString.utm_term.toUpperCase()+'"</b>. Перейти -></div>');
 
 
 $(".curzap").click(function(){

var curZap=$(".sml-title:contains('"+QueryString.utm_term+"')");
 
 
 var zcnt=0; 
 var moveArr=new Array();
 
 
 
 
 $(".product-url .sml-title").each(function(){ 
 
 // console.log(con(QueryString.xxx,$(this).text()) + " " + $(this).text() + " " +QueryString.xxx)
 con(QueryString.utm_term,$(this).text())
 moveArr.push(con(QueryString.utm_term,$(this).text()));
 // zcnt++;
 
 });
 
 
 var curSel=$(".product-url .sml-title").eq(getMaxValue(moveArr));
 
 moveTo(curSel.parent().parent()); // переместить к 
 
 console.log(getMaxValue(moveArr))

 setTimeout(function(){curSel.closest(".list-item").addClass("searched");},1200); // добавим класс с выделением
 setTimeout(function(){curSel.closest(".list-item").removeClass("searched");},4800); // уберём этот класс
}); 
} 
 });

 // бонусная система
 
 

$.get("/php/bonuses.php?act=readTours",function(data){

var totalVars = data;totalVars=totalVars.split(";");
 
 var uuu=setInterval(function(){
 
$(".list-item").each(function(){
 
 for(i=0;i<totalVars.length;i++){
 
 if(parseFloat($(this).find(".product-url").attr("varId"))==parseFloat(totalVars[i]) && !$(this).find(".bonusLabel").is("a")){

$(this).prepend('<a class="bonusLabel" href="/bonusprogram" target="_blank" title="Узнать подробнее">Выиграй Бонус!</a>'); // добавить ссылку на акцию с описанием её
 }

 }

});

},1200);

})
 