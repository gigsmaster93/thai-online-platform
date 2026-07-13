 function stabilize(){
 if(getClientWidth()>=1024){
 
 var optA=$(".colIco").length;
 var optB=$(".infoblock .width").eq(1).width();
 
 var icoWidth=(optB/optA)-1;
 
 $(".colIco").each(function(){ $(this).css({"width":icoWidth,"margin-left":"0%","margin-right":"0%","padding":"0%"})});
 
 }else{
 
 var optA1=$(".colIco.left").length;
 var optA2=$(".colIco.right").length;
 
 var optB=$(".infoblock .width").eq(1).width();
 
 var icoWidth1=(optB/optA1)-1;
 var icoWidth2=(optB/optA2)-1; 
 
 $(".colIco.left").each(function(){ $(this).css({"width":icoWidth1,"margin-left":"0%","margin-right":"0%","padding":"0%"})});
 $(".colIco.right").each(function(){ $(this).css({"width":icoWidth2,"margin-left":"0%","margin-right":"0%","padding":"0%"})}); 
 
 }
 }
 
 /* Парсим инфо блоки */
 if(infoBl.length>0){
 infoBl=infoBl.split("#");
 
 for(i=0;i<infoBl.length;i++){
 var ll=infoBl[i].split("&");
 
hdrx=ll[0];txtx=ll[1]
 if(isEng()){hdrx=icoTrans(hdrx);txtx=icoTrans(txtx);}
 $(".infoblock .infoBl").append("<div class='colIco left'><i class='fa fa-"+ll[2]+"'></i><div class='hdr'>"+hdrx+"</div><div class='txt'>"+txtx+"</div></div>");
 
 }}
 
 /* Парсим инфо блоки */
 
 
 //спрячем неспрятанные варианты туров
 if(parseTop.indexOf("%")==-1){
 $(".tourVarS").hide(); 
 }
 setTimeout(function(){if(isEng()){
 $(".feedback .fa, #id-itemId-wish").hide(); 
 }},2000);

// бонусная система

/*$.get("/php/bonuses.php?act=readTours",function(data){

var totalVars = data;totalVars=totalVars.split(";");
 
 for(i=0;i<totalVars.length;i++){
 
 if(parseFloat(itemId)==parseFloat(totalVars[i])){
 
$(".bread").before('<a class="bonusLabel" href="/bonusprogram" target="_blank" title="Узнать подробнее">Выиграй бонус при заказе этой экскурсии!</a>'); // добавить ссылку на акцию с описанием её
 }

 }




})*/