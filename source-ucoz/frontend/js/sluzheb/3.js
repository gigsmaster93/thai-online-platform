$("#order-field-1-block div.fw").prepend('<div class="jdivwrap"><jdiv class="flagIcon_Er" country="RU" style="background-image: url(&quot;//cdn-cis.jivosite.com/images/flags/RU.png&quot;);"></jdiv><jdiv class="flagArrow_GK"><jdiv class="iconSelect_vu default"></jdiv></jdiv><jdiv class="phoneCodeWrap_28">+7</jdiv><jdiv style="display: none;" class="country_wrap countryWrap_1Y _isScroll_1D"></div></div>');
$(".country_wrap").load('/jdiv/jdiv.html');

$(".flagIcon_Er, .flagArrow_GK").click(function(){
$(".country_wrap").toggle();
});

 setTimeout(function(){ $(".country_wrap .listItem_2i").click(function(){
 
 $(".jdivwrap>.flagIcon_Er").replaceWith($(this).find(".flagIcon_Er").clone());
 $(".jdivwrap>.phoneCodeWrap_28").text($(this).find(".countryCode_kE").text());
 $(".country_wrap").toggle();
 $(".jdivwrap>.flagIcon_Er").attr("country",$(this).attr("data-code"))
 $("#order-fld-1").attr("placeholder",$(this).attr("place").replace(/\s/g, '').replace(/-/g, '')).attr("maxlength",$(this).attr("maxdig"));
 $(this).find(".countryName_2v").attr("maxdig");
 
 if($(".flagIcon_Er").is(".flagIcon_Er[country=TH]")){
 
 if(!$(".thaiWarn").is("div") && !getCookie('thaiSeen')){
 $("#order-fld-1").after("<div class='thaiWarn'><i class='fa fa-close' onclick='setCookie(\"thaiSeen\",1,{path:\"/\"});$(this).parent().hide();'></i> <b>Important!!!</b> Check your entered number: <br> Thai numbers are entered without '0' before and without +66 (this part is filled already) </div>")
 }
 
 $("#order-fld-1").unbind('keypress').bind('keypress input', function(){
 if ($(this).val().substr(0,3)=="+66"){
 $(this).val($(this).val().substr(3, $(this).val().length-3))
 }else if($(this).val().substr(0,1)=="0") {
 $(this).val($(this).val().substr(1, $(this).val().length-1))
 }
 
 })

 }else{ $("#order-fld-1").unbind('keypress input')}
 
 });
 
 },1500);