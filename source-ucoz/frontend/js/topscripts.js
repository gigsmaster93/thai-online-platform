$("#tellnk").click(function(){$("#telblock").toggle()});

$("#newVals > ul li[data-val='"+$("#shop-currency-select option:selected").text()+"']").addClass("selected");

$("#newVals > ul li").click(function(){
  location.href=$(this).find("a").attr("href");console.log($(this).find("a").attr("href"));
if($("#newVals > ul li.selected").is("selected")){$(this).addClass("selected");
                                                
                                                 }

$("#shop-currency-select option").each(function(){$(this).removeProp("selected").removeAttr("selected");});
  
  

//$("#shop-currency-select option:contains('"+$(this).attr("data-val")+"')").attr("selected","selected").prop("selected","selected");
//$("#shop-currency-form").submit();
});


function soundClick() {
  var audio = new Audio(); // Создаём новый элемент Audio
  audio.src = '/clicksound.mp3'; // Указываем путь к звуку "клика"
  audio.autoplay = true; // Автоматически запускаем
}



function shwNotify(a,b){

if(!$("#notifyMsg").is("div")){$("body").append('<div id="notifyMsg"><div class="close"><i class="fa fa-close"></i></div>'+a+'</div>');}


/*soundClick();*/

$("#notifyMsg .close").click(function(){ setCookie("lastId", b, {expires: 365,path:"/"});$("#notifyMsg .close").parent().remove();});

}

 function moveTo(a){
 setTimeout(function(){
 $('html, body').stop().animate({
 scrollTop: $(a).offset().top-100
 }, 800);
 },700); // задержка перед переходом 
} 

// определение версии
function isEng(){ if(location.href.indexOf("en.")!=-1){ return true;}else{return false;}}

// перевод основных иконок 
function icoTrans(a){
return a.replace(/Любой отель Паттайи/g,"Any Pattaya hotel").replace(/Выезд/g,"Departure").replace(/Плавать с дельфинами/g,"Swimming with Dolphins").replace(/Место Делюкс/g,"Deluxe place").replace(/Место ВИП/g,"VIP place").replace(/Место Стандарт/g,"Standard place").replace(/Шоу и плаванье/g,"Show & Swimming").replace(/Только шоу/g,"Only Show").replace(/Только плаванье/g,"Only Swimming").replace(/Сопровождающий/g,"Accompanying").replace(/Катание/g,"Riding").replace(/час/g,"hour(s)").replace(/мин/g,"min(s)").replace(/Экскурсия/g,"Excurssion").replace(/В одну сторону/g,"One way").replace(/Туда-обратно/g,"Both way").replace(/300 куб. с утра/g,"300cc from the morning").replace(/150 куб. с утра/g,"150cc from the morning").replace(/300 куб. с обеда/g,"300cc from lunch").replace(/150 куб. с обеда/g,"150cc from lunch").replace(/Туда и обратно/g,"Both way").replace(/Человек/g,"Persons").replace(/любое время/g,"any time").replace(/С ожиданием/g,"With waiting").replace(/Без ожидания/g,"Without waiting").replace(/Пакет/g,"Pack").replace(/Эконом/g,"Economy").replace(/Без питания/g,"No meals").replace(/Без обеда/g,"No lunch").replace(/Из Бангкока/g,"From Bangkok").replace(/Без квадроциклов/g,"Without ATV").replace(/С квадроциклами/g,"With ATV").replace(/Стандарт/g,"Standard").replace(/С шоу Сиам Нирамит/g,"With Siam Niramit show").replace(/С океанариумом и шоу/g,"With aquarium and show").replace(/С океанариумом/g,"With aquarium").replace(/С аквапарком, обед/g,"Aquapark, with lunch").replace(/С аквапарком, без обеда/g,"Aquapark, without lunch").replace(/Сколько чел/g,"Persons").replace(/С пляжем, обед/g,"Beach, with lunch").replace(/С пляжем, без обеда/g,"Beach, without lunch").replace(/Остров Любви/g,"Love Island").replace(/Обед с курицей/g,"Lunch with chicken").replace(/Понедельник/g,"Monday").replace(/Вторник/g,"Tuesday").replace(/Среда/g,"Wednesday").replace(/Четверг/g,"Thursday").replace(/Пятница/g,"Friday").replace(/Суббота/g,"Saturday").replace(/Воскресенье/g,"Sunday").replace(/Пн./g,"Mon").replace(/Вт./g,"Tue").replace(/Ср./g,"Wed").replace(/Чт./g,"Thu").replace(/Пт./g,"Fri").replace(/Сб./g,"Sat").replace(/Вс./g,"Sun").replace(/Нужна/g,"Required").replace(/Не обязательна/g,"Optional").replace(/Да/g,"Yes").replace(/Нет/g,"No").replace(/Ежедневно/g,"Daily").replace(/Взрослый/g,"Adult").replace(/Взрослые/g,"Adult").replace(/Дети/g,"Children").replace(/Младенцы/g,"Infant").replace(/Предоплата/g,"Prepay").replace(/Шопинг/g,"Shopping").replace(/Русский гид/g,"Russian guide").replace(/Дни и время/g,"Date & Time").replace(/Без ужина/g,"Without supper").replace(/до/g,"below").replace(/см/g,"cm").replace(/Ежд./g,"Daily").replace(/ежд/g,"daily").replace(/кроме/g,"except for").replace(/понедельника/g,"monday").replace(/вторника/g,"tuesday").replace(/среды/g,"wednesday").replace(/четверга/g,"thursday").replace(/пятницы/g,"friday").replace(/субботы/g,"saturday").replace(/воскресения/g,"sunday").replace(/воскресенья/g,"sunday").replace(/любое/g,"any").replace(/Седан/g,"Sedan").replace(/Минибас/g,"Minibus").replace(/Минивэн/g,"Minivan").replace(/Трансфер/g,"Transfer").replace(/Откуда/g,"Your location").replace(/Заказ/g,"Order").replace(/В обе стороны/g,"Both way").replace(/Туда/g,"One way").replace(/Групповой тур/g,"Group tour").replace(/В Бангкоке/g,"At Bangkok").replace(/В Паттайе/g,"At Pattaya").replace(/Индив./g,"Personal").replace(/Приостановлен насезон/g,"Stopped for a season").replace(/Из Паттайи/g,"From Pattaya").replace(/Из Бан Пхе/g,"From Ban Phe").replace(/Дни выезда/,"Days of departure").replace(/ и /," and ").replace(/Индивидуально/g,"Personaly").replace(/Группой/g,"In group").replace(/Есть/g,"Yes").replace(/человек/g,"persons").replace(/от /g,"from ").replace(/ утра/g," morning").replace(/лет/g,"years").replace(/Депозит/g,"Deposit").replace(/бат\/чел/g,"bat/person").replace(/c обеда/g,"after lunch").replace(/Курсы/g,"Courses").replace(/дней/g,"days").replace(/дня/g,"days").replace(/день/g,"day").replace(/Зона/g,"Zone").replace(/Гид/g,"Guide")

}

