var kolvo="";
 var prs=parseInfo[2].split("&");
 for(i=0;i<prs.length;i++){ 
 kolvo+=prs[i].split("=")[0]+": "+prs[i].split("=")[1]+"\n";}
 
 if(parseInfo.length>3){kolvo+="\nТариф: "+parseInfo[3];}
 if($("#order-fld-4").val()==""){$("#order-fld-4").val("");}
 
 /* Повесим перенос кол-ва людей в примечание при клике по Оформить заказ */ 
 var saveFld4text=0;
 $("#order-button").removeAttr("onclick").click(function(){
 
 if($("#order-fld-1").val().length>0){
 $("#order-fld-1").attr("maxlength","").val($(".jdivwrap>.phoneCodeWrap_28").text()+" "+$("#order-fld-1").val());}
 
 $("#order-fld-21").val($("#prePay input:checked").val())
 
 if(saveFld4text===0){saveFld4text=$("#order-fld-4").val();}

 if($("#order-fld-4").val()==""){$("#order-fld-4").val("");
 $("#order-fld-4").val("Телефон: "+$(".jdivwrap>.flagIcon_Er").attr("country")+" "+$("#order-fld-1").val()+"\n\nСпособ связи: "+$(".selected .option").attr('callvar') + "\n\n" +$("#order-fld-4").val()+"\n\n Информация о количестве человек: \n\n"+kolvo);
 }else{ $("#order-fld-4").val("");
 
 $("#order-fld-4").val("Телефон: "+$(".jdivwrap>.flagIcon_Er").attr("country")+" "+$("#order-fld-1").val()+"\n\nСпособ связи: "+$(".selected .option").attr('callvar') + "\n\n" +saveFld4text+"\n\n Информация о количестве человек: \n\n"+kolvo); 
 
 }
 
 
shopCheckOut();
 
 });
 
 
 $("#order-table table").attr("cellspacing","0").attr("cellpadding","0");