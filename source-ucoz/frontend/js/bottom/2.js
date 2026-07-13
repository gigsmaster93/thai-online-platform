 function km(a){
 a=parseFloat(a);
 if(a>1000 && a<1000000){return (a/1000).toFixed(0)+"K"}else
 if(a>1000 && a<1000000){return (a/1000).toFixed(0)+"M"}else{
 return a;
 } 
 }
 function transL(){
 $(".list-item .sml-price.right:not(.transed)").each(function(){
 
 var a=$(this).find("span").eq(0).text();
 $(this).find("span").eq(0).html('<i class="fa fa-eye"></i> '+km(a)) 
 
 
 var b=$(this).find("span").eq(1).text();
 $(this).find("span").eq(1).html('<i class="fa fa-cart-arrow-down"></i> '+km(b)) 
 $(this).addClass("transed")
 
 });
 }