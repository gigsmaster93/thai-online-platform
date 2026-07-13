 // start если категория транспорта
 
function btnsIni(){ 
 var ttlVar="";
 $("#taxiVars .varTd input").unbind("keyup").keyup(function(){
var ttlVar="";
 $("#taxiVars .varTd").each(function(){ 
 ttlVar+=$(this).find(".lwInputNm").val()+"&"+$(this).find(".lwInputVl").val();
 
 if(!$(this).is(".varTd:last")){
 ttlVar+="#";}
 
 }); 
$("#taxField").val(ttlVar);

 }); 
 

 $("#taxiVars .varTd").each(function(){ 
 ttlVar+=$(this).find(".lwInputNm").val()+"&"+$(this).find(".lwInputVl").val();
 
 if(!$(this).is(".varTd:last")){
 ttlVar+="#";}
 
 }); 
 console.log(ttlVar)
 $("#taxField").val(ttlVar);}
 
 function isTaxi(){ // выбран ли раздел такси

var taxiArrs=[4,28,29,30,31,32,33];
for(l=0;l<taxiArrs.length;l++){
if(parseFloat($("#cat-select option:selected").val())==taxiArrs[l]){taxiArrs=true;return taxiArrs;break;}
if(l==taxiArrs.length-1){taxiArrs=false;return taxiArrs}
}}
 
 
 function taxiInitiate(){ 
isTaxi()
 if(isTaxi()){ 
 $("#tb_other3").css("display","none").hide();
 
 // добавим человеческий механизм работы с матрицей вариантов Такси и др
 
 if(!$("#taxiVars").is("div")){
 $("#tb_other2 input").hide().attr("id","taxField").before('<div id="taxiVars"><input type="button" id="addTopStr" value="Добавить вариант"></div>'); }
 
 $("#addTopStr").unbind("click").click(function(){
 $(this).parent().append('<div class="varTd"><input type="text" class="lwInputNm" placeholder="Название" value=""><input type="text" class="lwInputVl" placeholder="Стоимость" value=""><input type="button" class="delBtmM" value="X"></div>'); 
 
 $("#taxiVars .delBtmM").unbind("click").click(function(){ $(this).parent().remove();isTaxi();btnsIni()}); // удаление строки
 isTaxi();btnsIni()
 }); 
 
 // распарсим уже добавленные варианты Такси
 
 var parseTax=$("#taxField").val().split("#");
 if(parseTax.length>1 && parseTax[0]!=""){
 for(l=0;l<parseTax.length;l++){
 $("#taxiVars").append('<div class="varTd"><input type="text" class="lwInputNm" placeholder="Название" value="'+parseTax[l].split("&")[0]+'"><input type="text" class="lwInputVl" placeholder="Стоимость" value="'+parseTax[l].split("&")[1]+'"><input type="button" class="delBtmM" value="X"></div>'); 
 
 $("#taxiVars .delBtmM").unbind("click").click(function(){ $(this).parent().remove(); btnsIni()}); // удаление строки
 }
 }
 
 // сохраним данные в поле 
 /* Функция изменения значения поля с данными */
 
 btnsIni();
 
 /* Функция изменения значения поля с данными */
 
 }else{$("#tb_other2").hide();vartypesIni();} // end если категория Транспорта

 btnsIni();

 }
 taxiInitiate(); // инициализируем проверку и установку функций для категории Транспорта
 
 
 
 // добавим человеческий механизм работы с матрицей вариантов цен
 
 // функция сохранения значений 
 function saveResult(){ 

var ttlVar="";var ttlMinMax="";
$("#priceVars .varTr").each(function(){
 $(this).find(".varTd").each(function(){ 
 ttlVar+=$(this).find(".lwInputNm").val()+"&"+$(this).find(".lwInputVl").val(); // сохраним значения цены 
 
 ttlMinMax+=$(this).find(".minval").val().replace(/10/g,"-11")+"&"+$(this).find(".maxval").val().replace(/%10/g,"%-11"); // сохраним мин и макс кол-во человек
 console.log("minval: "+$(this).find(".minval").val().replace(/10/g,"-11"))
 if(!$(this).is(".varTd:last")){
 ttlVar+="#";ttlMinMax+="#";}
 
 });
 
 if(!$(this).is(".varTr:last")){
 ttlVar+="%";ttlMinMax+="%";}
 
 
 
 

});

 $("#valField").val(ttlVar.replace(/#%/g,"%"));
 ttlMinMax=ttlMinMax.replace(/#%/g,"%");
 
 if(ttlVar.lastIndexOf("%")+1==ttlVar.length){ttlVar=ttlVar.substring(0, ttlVar.length - 1);
 ttlMinMax=ttlMinMax.substring(0, ttlMinMax.length - 1);
 $("#valField").val(ttlVar)}
 console.log(encodeURIComponent(ttlMinMax));
 if(saveCntr==0){ // чтоб не спамить
 saveCntr++;
 setTimeout(function(){$.get("/php/extrafields.php?act=wr&itemId="+goodId+"&content="+encodeURIComponent(ttlMinMax)),function(data){}
 setTimeout(function(){ saveCntr=0;console.log(saveCntr);},500);
 },2000); // сохраним мин и макс значения в базе
 }
} 
 // конец функции сохранения значений
 
 function vartypesIni(){
 if(cntr==0){
 if(isTaxi()){ $("#tb_other3").css("display","none").hide();return false;}
 if(!$("#priceVars").is("div")){ // проверим был ли контейнер добавлен 
 
 $("#tb_other3 input").eq(0).hide().attr("id","valField").before('<div id="priceVars"><input type="button" id="addTopM" value="Добавить блок"></div>');
 }
 $("#addTopM").unbind("click").click(function(){
 $("#priceVars").append('<div class="varTr"><input type="button" class="addMiddleM" value="Добавить вариант"><input type="button" class="delMiddleM" value="X"></div>'); 
 
 $("#priceVars .delMiddleM").unbind("click").click(function(){
 $(this).parent().remove();saveResult() });
 
$("#priceVars .addMiddleM").unbind("click").click(function(){ 
 $(this).parent().append('<div class="varTd"><input type="text" class="lwInputNm" placeholder="Название" value=""><input type="text" class="lwInputVl" placeholder="Стоимость" value=""><input class="minval" placeholder="Min" type="number"><input class="maxval" placeholder="Max" type="number"><input type="button" class="delBtmM" value="X"></div>'); 
 
 $("#priceVars .delBtmM").unbind("click").click(function(){
 $(this).parent().remove();saveResult() });
 
 

 
 /* Функция изменения значения поля с данными */

 $("#priceVars .varTd input").unbind("keyup").keyup(function(){ 
 saveResult(); 
 });
 
 $(".minval,.maxval").unbind("change").change(function(){ // поля мин и макс
 saveResult(); 
 });
 
 /* Функция изменения значения поля с данными */
 
}); 
 
 });
 
 
 
 /* Экспорт данных из поля */
 
 function clckIniHandler(){ 
 setTimeout(function(){ 
 $("#addTopM").unbind("click").click(function(){
 $("#priceVars").append('<div class="varTr"><input type="button" class="addMiddleM" value="Добавить вариант"><input type="button" class="delMiddleM" value="X"></div>'); 
 clckIniHandler()});
 
 $("#priceVars .delMiddleM").unbind("click").click(function(){
 $(this).parent().remove(); clckIniHandler();saveResult()});
 
$("#priceVars .addMiddleM").unbind("click").click(function(){ 
 $(this).parent().append('<div class="varTd"><input type="text" class="lwInputNm" placeholder="Название" value=""><input type="text" class="lwInputVl" placeholder="Стоимость" value=""><input class="minval" placeholder="Min" type="number"><input class="maxval" placeholder="Max" type="number"><input type="button" class="delBtmM" value="X"></div>'); 
clckIniHandler() }); 
 
 $("#priceVars .delBtmM").unbind("click").click(function(){
 $(this).parent().remove(); clckIniHandler();saveResult()});
 
 /* Функция изменения значения поля с данными */
// начало функции полей
 
 $("#priceVars .varTd input").unbind("keyup").keyup(function(){
 saveResult(); 
 });
 
 $(".minval,.maxval").unbind("change").change(function(){ // поля мин и макс
 saveResult(); 
 });
 
 /* Функция изменения значения поля с данными */
 ttlVar=$("#valField").val();
 if(ttlVar.lastIndexOf("%")+1==ttlVar.length){ttlVar=ttlVar.substring(0, ttlVar.length - 1);
 $("#valField").val(ttlVar)}
 },500);
 } // конец функции полей 

 // добавить исключения если поле одно (без % на конце)
 var parseTop=$("#valField").val().replace(/#%/g,"%").split("%");
 var minmax=""; var minmax2="";
 $.get("/php/extrafields.php?act=get&itemId="+goodId,function(data){ 
 if(data.length>0){minmax=data.replace(/%-11/,'%10').split("%");} 

 
 if(parseTop.length>1){
 for(l=0;l<parseTop.length;l++){
 $("#priceVars").append('<div class="varTr"><input type="button" class="addMiddleM" value="Добавить вариант"><input type="button" class="delMiddleM" value="X"></div>'); 
 
 var parseMid=parseTop[l].split("#");
 
 if(data.length>0){ var minmax2=minmax[l];
 if(minmax.toString().indexOf("#")!=-1){minmax2=minmax[l].split("#");}
 } 
 
 for(i=0;i<parseMid.length;i++){
 minval=0;maxval=0; 
 if(data.length>0){ if(minmax2[i].toString().indexOf("&")!=-1 && data!=""){minval=minmax2[i].split("&")[0];maxval=minmax2[i].split("&")[1];} //!!!! 
 } 
 
 $("#priceVars .varTr:last").append('<div class="varTd"><input type="text" class="lwInputNm" placeholder="Название" value="'+parseMid[i].split("&")[0]+'"><input type="text" class="lwInputVl" placeholder="Стоимость" value="'+parseMid[i].split("&")[1]+'"><input class="minval" placeholder="Min" type="number" value="'+minval+'"><input class="maxval" placeholder="Max" type="number" value="'+maxval+'"><input type="button" class="delBtmM" value="X"></div>'); 
 clckIniHandler();
 } }
 
 }else{
 
 $("#priceVars").append('<div class="varTr"><input type="button" class="addMiddleM" value="Добавить вариант"><input type="button" class="delMiddleM" value="X"></div>'); 
 
 var parseMid=parseTop[0].split("#");
 if(data.length>0){
if(minmax.toString().indexOf("#")!=-1 && data!=""){minmax=minmax[0].split("#");}
 } 
 for(i=0;i<parseMid.length;i++){
 minval=0;maxval=0;
 if(data.length>0){
 if(minmax.toString().indexOf("&")!=-1 && data.length>0){minval=minmax[i].split("&")[0];maxval=minmax[i].split("&")[1];} //!!!! 
 }
 $("#priceVars .varTr:last").append('<div class="varTd"><input type="text" class="lwInputNm" placeholder="Название" value="'+parseMid[i].split("&")[0]+'"><input type="text" class="lwInputVl" placeholder="Стоимость" value="'+parseMid[i].split("&")[1]+'"><input class="minval" placeholder="Min" type="number" value="'+minval+'"><input class="maxval" placeholder="Max" type="number" value="'+maxval+'"><input type="button" class="delBtmM" value="X"></div>'); 
 clckIniHandler();
 } 
 }
 

 
 clckIniHandler();
 
 
 });
 
 cntr++;}

 /* Экспорт данных из поля */
 
 }
 
 /* Модель для обработки Инфо блоков */ 
 
 if(!$("#infoBlocks").is("div")){
 $("#tb_other6 input").hide().attr("id","infoBl").before('<div id="infoBlocks"><input type="button" id="addTopStr" value="Добавить вариант"></div>'); }
 
 // парс блоков информации 
 var infoBlPars=$("#infoBl").val().split("#"); 
 if(infoBlPars.length>1){
 for(i=0;i<infoBlPars.length;i++){ 
 $("#infoBlocks").append('<div class="varTr"><input type="text" class="lwInputNm" placeholder="Название" value="'+infoBlPars[i].split("&")[0]+'"><input type="text" class="lwInputVl" placeholder="Значение" value="'+infoBlPars[i].split("&")[1]+'"><input type="text" class="lwInputVl" placeholder="Иконка" value="'+infoBlPars[i].split("&")[2]+'"><input type="button" class="delBtmM" value="X"></div>'); 
 } 
 }
 
 
 // функция добавления блока и информации 
 
 $("#infoBlocks #addTopStr").click(function(){
$("#infoBlocks").append('<div class="varTr"><input type="text" class="lwInputNm" placeholder="Название" value=""><input type="text" class="lwInputVl" placeholder="Значение" value=""><input type="text" class="lwInputVl" placeholder="Иконка" value=""><input type="button" class="delBtmM" value="X"></div>'); 
 $("#infoBlocks .delBtmM").unbind("click").click(function(){ $(this).parent().remove();upDateBl();$("#infoBlocks input").unbind("keyup").keyup(function(){upDateBl();})});
 $("#infoBlocks input").unbind("keyup").keyup(function(){ upDateBl(); })
 });
 
 // функция обновления значения в поле
 
 function upDateBl(){
 var ttl="";
 $("#infoBlocks .varTr").each(function(){
 
 ttl+=$(this).find("input").eq(0).val()+"&"+$(this).find("input").eq(1).val()+"&"+$(this).find("input").eq(2).val()+"#";
 
 
 });
 
 if($("#infoBlocks .varTr").length>1){ttl=ttl.substring(0, ttl.length - 1);} 
 
 $("#infoBl").val(ttl); } 
 
 upDateBl(); // вызовем функцию
 
 $("#infoBlocks input").unbind("keyup").keyup(function(){ upDateBl(); })
$("#infoBlocks .delBtmM").unbind("click").click(function(){ $(this).parent().remove();upDateBl();$("#infoBlocks input").unbind("keyup").keyup(function(){upDateBl();})}); 