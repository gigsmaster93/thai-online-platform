 $(document).ready(function(){
 setTimeout(function(){
 if(isEng()){
 
 var ll=setInterval(function(){ 
 
 $("jdiv .callText_3Z > jdiv").html("Need a help? <br>We'll call back in<b>24</b> seconds!");
 $(".button_Jn .text_Xc").text('Call me');
$(".bottomBox_Og .link_24").text('Chat with us');

$(".jivo-label-buttons .title_yB").text("Message in Facebook");
$(".jivo-label-buttons .info_CT").text("Instant reply usually");

$("#jcont .main_1I .agentName_3R").text("Amigo Novak");
$("#jcont .main_1I .content_oc .text_14").eq(0).text("Greetings! Happy to see you on our platform where you can simply book excursions by the most advantageous prices! You may ask any question in english and even make fast order via online-chat!");
$(".agentName_1D").text("Amigo Novak");
$(".title_1g").text("Consultant");
$(".inputField_2G").attr("placeholder","Type your message and click Enter");
$(".main_21 .text_2i").text("Wait please, currently all the consultants are busy. We'll answer you shortly!");

 $(".lbContainer_2X .labelButton_2w .title_yB").eq(0).text("Message at Facebook");
$(".lbContainer_2X .labelButton_2w .info_CT").eq(0).text("Moment reply usually");
$(".lbContainer_2X .labelButton_2w .title_yB").eq(1).text("We'll call you back in 24 seconds!");
$(".lbContainer_2X .labelButton_2w .title_yB").eq(2).text("Chat with us!"); 
 if($(".hoverl_6R").is("jdiv")){$(".text_eD").text("Online consultation")}
 $("#jcont_content_wrapper .popup_1H .main_1c .call-form .text_1I").text("Enter your phone and we'll call you back within 24 seconds!")
 },100);
 
 $('#newVals li[data-val="Рубли"]').hide();
 $('.btmtels').eq(0).parent().hide();
 $("body").append('<style>.footer ul.uMenuRoot > li > a::before {content:"";}</style>');

 
 $(".infoblockOl .bonusLabel").text("Buy this excurssion and win a bonus!")
 $(".footer .sn").hide();
 
 } // end IsEng
 },1000)
});