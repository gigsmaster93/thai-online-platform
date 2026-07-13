// функции уведомления о сборе данных 
 if(!getCookie('notif')){
 $(".notification").show();
 $(".notification_close").click(function() {
 $(this).closest(".notification--cookie").length && setCookie("notif", 1, {
 expires: 365,path:"/"
 }), $(this).closest(".notification").addClass("hide")
})
 }else{$(".notification").hide();} 