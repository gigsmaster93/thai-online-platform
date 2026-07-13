function preLoader(){
var step=0.1;
xcntr=0;
 var falser=true;
 setTimeout(function(){
setInterval(function(){
 if(xcntr==9){falser=false;}else if(xcntr==0){falser=true;}
 
$(".div1").css("filter","grayscale("+(xcntr*step)+")");
 $(".div1").css("transform","scale3d("+(0.7+(xcntr*step)/4)+", "+(0.7+(xcntr*step/4))+", "+(0.7+(xcntr*step)/4)+")"); 
 
if(falser){xcntr++;}else{xcntr--;}

},100)
 },500);
} 
 

// переключатель языков
 function audio2(){ var audio2 = new Audio(); audio2.src = '/langClick.mp3'; audio2.autoplay = 'true'; }
 
 
 preLoader();
 setTimeout(function(){preLoaderOff()},3000);

 var mA=true;
var mA2=true;
 $("#uNMenuDiv1 li a").each(function(){
 if(location.href.indexOf($(this).attr("href"))!=-1 && mA){
$(this).addClass("uMenuItemA");mA=false;
}

if($(this).next().is(".subM")){
$(this).next().find("li a").each(function(){
if(location.href.indexOf($(this).attr("href"))!=-1 && mA2){
$(this).addClass("uMenuItemA");mA2=false;mA=false;

$(this).parent().parent().prev().addClass("uMenuItemA");

}
})
}
})