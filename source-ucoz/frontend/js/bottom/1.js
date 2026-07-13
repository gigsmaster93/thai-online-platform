$(".innerMenu").each(function(){
$(this).after('<ul class="lowestMenu"></ul>');
});
 $(".lowestMenu").each(function(){$(this).hide();});
 $(".innerMenu").each(function(){$(this).hide()});
$(".innerMenu > li a").mouseover(function(){

 //if(!$(this).is(".loadded")){
var thisLink=$(this);thisLink.addClass('loadded');
 
 $(".innerMenu").each(function(){$(this).hide()});
 
 $(this).closest(".innerMenu").addClass("active").show();
 $(".lowestMenu").each(function(){$(this).hide();});
 
thisLink=thisLink.closest(".innerMenu").next();
 thisLink.html("");thisLink.show();
var xx=$(this).attr("href");
$.get(xx,function(data){
var a="<img src='"+$("#catImgX",data).attr("data-src")+"'>";

 thisLink.html("");
for(i=0;i<3;i++){
 var b=$("#goods_cont .list-item",data).eq(i).find(" > a.product-url");
 a+='<li><a href="'+b.attr("href")+'" target="_blank">'+b.find(".sml-title").text()+'</a>';}
a+='<li><a href="'+xx+'" target="_blank">Посмотреть все</a></li>';
thisLink.append(a);
});

 //}

 
 
 });