function add_script(txt) {
 var newScript = document.createElement("script");
 newScript.type = "text/javascript";
 newScript.src = txt;
 document.getElementsByTagName('body')[0].appendChild(newScript);} 
 
 function add_style(txt) {
 var newScript = document.createElement("link");
 newScript.type = "text/css";
newScript.rel = "stylesheet";
 newScript.href = txt;
 
 document.getElementsByTagName('head')[0].appendChild(newScript);
} 