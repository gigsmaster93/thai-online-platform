(function($){
'use strict';

function renumber($table){
  $table.find('tbody tr[data-row]').each(function(i){
    $(this).find('.top-admin-row-number').text(i+1);
  });
}

function addRow(type){
  var $tpl=$('template[data-row-template="'+type+'"]');
  var $table=$('table[data-repeatable="'+type+'"]');
  if(!$tpl.length||!$table.length)return;
  var index=Date.now().toString()+Math.floor(Math.random()*1000);
  var html=$tpl.html().replaceAll('__INDEX__',index);
  var $row=$(html);
  $table.find('tbody').append($row);
  renumber($table);
  $row.find('input,textarea,select').first().trigger('focus');
}

$(document).on('click','[data-add-row]',function(e){
  e.preventDefault();
  addRow($(this).data('add-row'));
});

$(document).on('click','[data-remove-row]',function(e){
  e.preventDefault();
  var $table=$(this).closest('table');
  var $body=$table.find('tbody');
  $(this).closest('tr').remove();
  if(!$body.find('tr[data-row]').length){
    var type=$table.data('repeatable');
    addRow(type);
  }
  renumber($table);
});

$(document).on('click','[data-media-pick]',function(e){
  e.preventDefault();
  var $field=$(this).closest('[data-image-field]');
  var frame=wp.media({
    title:'Выберите изображение',
    button:{text:'Использовать изображение'},
    multiple:false,
    library:{type:'image'}
  });
  frame.on('select',function(){
    var item=frame.state().get('selection').first().toJSON();
    if(!item||!item.url)return;
    var url=item.url;
    try{
      var parsed=new URL(url,window.location.origin);
      if(parsed.origin===window.location.origin)url=parsed.pathname+parsed.search;
    }catch(err){}
    $field.find('input[type="text"]').val(url).trigger('change');
    var $preview=$field.find('.top-admin-image-preview');
    $preview.html($('<img>',{src:item.url,alt:''}));
  });
  frame.open();
});

$(document).on('click','[data-media-clear]',function(e){
  e.preventDefault();
  var $field=$(this).closest('[data-image-field]');
  $field.find('input[type="text"]').val('');
  $field.find('.top-admin-image-preview').empty();
});

$(function(){
  $('.top-admin-repeatable').each(function(){renumber($(this));});
});
})(jQuery);