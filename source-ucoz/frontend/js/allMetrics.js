function setFormHndlr(a,b){
 $(a).unbind("click").closest('form').submit(function(){
yaCounter40147910.reachGoal(b);
ga('send', 'event', 'submit', b); });}
// повесим цели на формы
// Контакты
$("#submitCont").click(function(){
setFormHndlr("#submitCont","tour2formcompl");});
// Страница туров ТИП 2
$("#submitOrdType2").click(function(){
setFormHndlr("#submitOrdType2","tour2formcompl");
});
function ga(a,b,c,d){} 
// повесим цели на клики
//шапка
$("#telblock > a").click(function(){b='toptels';
yaCounter40147910.reachGoal(b);ga('send', 'event', 'submit', b);});
$(".topmail").click(function(){b='topmail';
yaCounter40147910.reachGoal(b);ga('send', 'event', 'submit', b);});
$("#pricelist").click(function(){b='pricelist';
yaCounter40147910.reachGoal(b);ga('send', 'event', 'submit', b);});
//подвал
$(".btmtels").click(function(){b='btmtels';
yaCounter40147910.reachGoal(b);ga('send', 'event', 'submit', b);});
$(".btmmail").click(function(){b='btmmail';
yaCounter40147910.reachGoal(b);console.log('btmmail');ga('send', 'event', 'submit', b);}); 
//старая версия сайта
$(".oldver").click(function(){b='oldver';
yaCounter40147910.reachGoal(b);ga('send', 'event', 'submit', b);});
//контакты
$(".conttels").click(function(){b='conttels';
yaCounter40147910.reachGoal(b);ga('send', 'event', 'submit', b);});
$(".contmail").click(function(){b='contmail';
yaCounter40147910.reachGoal(b);ga('send', 'event', 'submit', b);});
//Страница туров ТИП 1
$(".basket.now").click(function(){b='tour1btord';
yaCounter40147910.reachGoal(b);ga('send', 'event', 'submit', b);});
$("#order-submit").click(function(){b='tour1btcompl';
yaCounter40147910.reachGoal(b);ga('send', 'event', 'submit', b);});

// цели на галерее
$("#adv").click(function(){b='gallerytour';
yaCounter40147910.reachGoal(b);ga('send', 'event', 'submit', b);});




 (function (d, w, c) {
 (w[c] = w[c] || []).push(function() {
 try {
 w.yaCounter40147910 = new Ya.Metrika({
 id:40147910,
 clickmap:true,
 trackLinks:true,
 accurateTrackBounce:true,
 webvisor:true,
 trackHash:true,
 ecommerce:"dataLayer"
 });
 } catch(e) { }
 });

 var n = d.getElementsByTagName("script")[0],
 s = d.createElement("script"),
 f = function () { n.parentNode.insertBefore(s, n); };
 s.type = "text/javascript";
 s.async = true;
 s.src = "https://cdn.jsdelivr.net/npm/yandex-metrica-watch/watch.js";

 if (w.opera == "[object Opera]") {
 d.addEventListener("DOMContentLoaded", f, false);
 } else { f(); }
 })(document, window, "yandex_metrika_callbacks");
