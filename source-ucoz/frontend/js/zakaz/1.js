 function prsDate(a){
 
 var x = new Date();
 
 x=x.setTime(a); 
 
var d = {
 day: new Date(x).getDate(),
 month: new Date(x).getMonth(),
 year: new Date(x).getFullYear(),
 hour: new Date(x).getHours(),
 minute: new Date(x).getMinutes(),
 second: new Date(x).getSeconds()
}
var D = {};
for (var n in d) {
 //D[n] = (parseInt(d[n], 10) < 10 ) ? ('0'+d[n]) : (d[n]);
 
 D[n] = parseFloat(d[n]);
 
}
 
 
 var z = /*D.day + '.' + D.month + '.' + String(D.year).substr(2,2);*/
 z = /*z + ' ' +*/ D.hour /*+ ':' + D.minute*/;
 return z;
 }

function fulltime(a) {
var time=new Date();
var newYear=new Date(); 
var newYear=newYear.setTime(a); 
var totalRemains=(newYear-time.getTime());
 console.log(newYear + " " +time.getTime() + " " + totalRemains)
if (totalRemains>1){
 
var RemainsSec = (parseInt(totalRemains/1000));
var RemainsFullDays=(parseInt(RemainsSec/(24*60*60)));
var secInLastDay=RemainsSec-RemainsFullDays*24*3600;
var RemainsFullHours=(parseInt(secInLastDay/3600));
if (RemainsFullHours<10){RemainsFullHours="0"+RemainsFullHours};
var secInLastHour=secInLastDay-RemainsFullHours*3600;
var RemainsMinutes=(parseInt(secInLastHour/60));
if (RemainsMinutes<10){RemainsMinutes="0"+RemainsMinutes};
var lastSec=secInLastHour-RemainsMinutes*60;
if (lastSec<10){lastSec="0"+lastSec};
 
 //document.getElementById("RemainsFullDays").innerHTML=RemainsFullDays+"<span id='Rem'> дн</span>";
document.getElementById("RemainsFullHours").innerHTML=RemainsFullHours+"<span id='Rem'> час</span>";
document.getElementById("RemainsMinutes").innerHTML=RemainsMinutes+"<span id='Rem'> мин</span>";
document.getElementById("lastSec").innerHTML=lastSec+"<span id='Rem'> сек</span>"; 
setTimeout('fulltime()',10000) 
}
 /*
else{
document.getElementById("clock").innerHTML="C НОВЫМ ГОДОМ !!!";
} */
}

