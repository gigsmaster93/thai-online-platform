// массив с транспортом
var transp = new Map([["Седан" , [1,3]], ["Минивэн" , [1,6]],["Минибас" , [1,13]]]);

// получить ширину рабочей области
 function getClientWidth(){ 
 var width=(window.innerWidth)?window.innerWidth:
 ((document.all)?document.body.offsetWidth:null);
 return width;} 
     
function minmaxCheck(a){
	 if(parseFloat(a.attr("min"))>0 && parseFloat(a.val())<parseFloat(a.attr("min"))){a.val(a.attr("min"));}
	 if(parseFloat(a.attr("max"))>0 && parseFloat(a.val())>parseFloat(a.attr("max"))){a.val(a.attr("max"));}	
}

function decrMinMax(a){ // если текущие значения отличаются от минмакса новой опции
$(a).each(function(){

minmaxCheck($(this));


//var b=parseFloat($(this).attr("min"));
//var c=parseFloat($(this).attr("max"));

//if(parseFloat($(this).val())<b && parseFloat($(this).attr("min"))!=0){$(this).val(b)} // min
//if(parseFloat($(this).val())>c && parseFloat($(this).attr("max"))!=0){$(this).val(c)} // max

});
}   



      
 /* функция поиска по массиву */
function find(array, value) {
 if (array.indexOf) { // если метод существует
 return array.indexOf(value)+1;
 }

 for (var i = 0; i < array.length; i++) {
 if (array[i] === value) return i;
 }
 return -1;
}
 /* функция поиска по массиву */

