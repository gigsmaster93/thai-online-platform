 if(turn==1){
 var page=1;
 var ttlpages=1;
 
 $.get(lnk+"-1",function(data){
 
 ttlpages=Math.ceil(parseFloat($("#ttlkol",data).text())/50);
 
 var crcntr=0;
 var toG="К Галерее";var addG="Добавить";
 if(isEng()){toG="Visit Galary";addG="Add";}
 
 $("#allEntries .ph-link",data).each(function(){
 
 if(crcntr<count){
 var a=$(this).attr("href");var b=$(this).attr("data-url"); 
 $("#ms-staff-1").append('<div style="background-image: url(\''+a+'\');background-size: 171% auto;background-repeat: no-repeat;background-position:50%;cursor:pointer;" class="ms-slide"><div class="hider"><a href="'+lnk+'"><i class="fa fa-image"></i><br>'+toG+'</a><a href="https://thai-online.org/photo/0-0-0-1-2" target="_blank"><i class="fa fa-camera"></i><br>'+addG+'</a></div></div>');
 crcntr++;
 }
 
 })
 
 slideLch() //запустим слайдер
 
 
 });
 
 
 
 }
 
 