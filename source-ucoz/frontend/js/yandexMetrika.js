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