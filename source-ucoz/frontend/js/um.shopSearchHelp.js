if(typeof um == 'undefined'){ var um = {}} else if(typeof um != 'object' || um instanceof Array){ throw new Error('Конфликт переменных (um определен и не является объектом)');}
um.products = [];
um.searchProducts = function(query, p) {
	var getPrInt, i = 0, 
	results = [],
	minChar = p.minChar ? p.minChar : 3,
	numResults = p.numResults ? p.numResults : 0
	;
	if($('#search-results').length == 0){
		$('.searchForm').after('<ul id="search-results"></ul>');				
	}
	if(query.length<minChar){um.showMessage('Введите минимум 3 символа'); return false}				
	if(um.products.length == 0){
		getPrInt = setInterval(function(){
			var t = 0;
			if(um.products.length == 0){
				um.showMessage('<img alt="" src="http://s56.ucoz.net/img/icon/ajsml.gif" style="vertical-align:-4px;"> Поиск товаров');
				} else {
				for(i;i<um.products.length;i++){
					if(um.products[i][0].toLowerCase().indexOf(query.toLowerCase()) != '-1'){
						results.push(i);
					}
				}
				um.showMessage(results);
				clearInterval(getPrInt);
			}
			t++;
		}, 2000);
		} else {
		for(i;i<um.products.length;i++){
			if(um.products[i][0].toLowerCase().indexOf(query.toLowerCase()) != '-1'){
				results.push(i);
				if(numResults>0 && results.length == numResults){break}
			}
		}
		um.showMessage(results, p);
	}	
}
um.showMessage = function(m, p){
	var onclick = '';
	$('#search-results').empty();
	if(m.length == 0){$('#search-results').html('<li>Ничего не найдено</li>')}
	if(typeof m == 'object') {
		onclick = p.yaMetrika? ('onclick="'+p.yaMetrika+'"'):'';
		for(var i=0;i<m.length;i++){
			$('#search-results').append('<li><a href="'+um.products[m[i]][1]+'" target="_blank" '+onclick+'><img src="'+um.products[m[i]][2]+'"><b>'+um.products[m[i]][3]+'</b><span> '+um.products[m[i]][0]+'</span></a></li>');
		}
		} else {
		$('#search-results').html('<li>'+m+'</li>');
	}
	$("#search-results").mouseleave();
	$("#search-results").show();
};
$.get('/export.xml', function(data){
	$('item',data).each(function(){
		var 
		title = $(this).children('title').text(),
		url = $(this).children('link').text(),
		price = $(this).children('g\\:price').text(),
		img = $(this).children('g\\:image_link').text();
		um.products.push([title,url,img,price]);
	});
});
$("#search-results").bind('mouseenter',function(){}).bind('mouseleave',function(){
	$("*").click(function(){
		$("#search-results").hide();					
	});				
});
$('input[name="query"]').attr('autocomplete','off');
$("input[name='query']").bind('click',function(){
	$("#search-results").show();
});