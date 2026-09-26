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
/* Native product gallery editor */
(function($){
'use strict';

function galleryLocalUrl(url){
  if(!url)return '';
  try{
    var parsed=new URL(url,window.location.origin);
    if(parsed.origin===window.location.origin)return parsed.pathname+parsed.search;
  }catch(err){}
  return url;
}

function gallerySetMode($editor,mode){
  var managed=mode==='managed';
  $editor.attr('data-gallery-source',managed?'managed':'legacy');
  $editor.find('[data-gallery-mode]').val(managed?'managed':'legacy');
  $editor.find('[data-gallery-mode-label]').text(managed?'управляемая галерея WordPress':'исходная legacy-галерея');
  var $buttons=$editor.find('.top-admin-gallery-buttons');
  var $reset=$buttons.find('[data-gallery-reset]');
  if(managed&&!$reset.length){
    $('<button>',{type:'button','class':'button','data-gallery-reset':'',text:'Вернуть исходную legacy-галерею'}).insertBefore($buttons.find('[data-gallery-clear]'));
  }else if(!managed){
    $reset.remove();
  }
}

function galleryToggleEmpty($editor){
  var has=$editor.find('[data-gallery-grid] [data-gallery-item]').length>0;
  $editor.find('[data-gallery-empty]').toggle(!has);
}

function galleryTemplate($editor,index,item){
  var tpl=$editor.find('template[data-gallery-item-template]').html()||'';
  var $item=$(tpl.replaceAll('__INDEX__',index));
  item=item||{};
  $item.find('[data-gallery-attachment]').val(item.attachment_id||0);
  $item.find('[data-gallery-src]').val(item.src||'');
  $item.find('[data-gallery-alt]').val(item.alt||'');
  $item.find('[data-gallery-title]').val(item.title||'');
  var preview=item.preview||item.url||item.src||'';
  if(preview){
    var abs=preview;
    if(preview.charAt(0)==='/')abs=window.location.origin+preview;
    $item.find('[data-gallery-drag]').html($('<img>',{src:abs,alt:''})).append($('<span>',{'class':'top-admin-gallery-grip dashicons dashicons-move'}));
  }
  return $item;
}

function gallerySync($editor){
  var items=[];
  $editor.find('[data-gallery-grid] [data-gallery-item]').each(function(){
    var $item=$(this);
    var src=String($item.find('[data-gallery-src]').val()||'').trim();
    if(!src)return;
    items.push({
      attachment_id:parseInt($item.find('[data-gallery-attachment]').val()||'0',10)||0,
      src:src,
      alt:String($item.find('[data-gallery-alt]').val()||''),
      title:String($item.find('[data-gallery-title]').val()||'')
    });
  });
  $editor.find('[data-gallery-json]').val(JSON.stringify(items));
}

function galleryAddItems($editor,items){
  var $grid=$editor.find('[data-gallery-grid]');
  (items||[]).forEach(function(item){
    var index='g'+Date.now().toString()+Math.floor(Math.random()*100000).toString();
    $grid.append(galleryTemplate($editor,index,item));
  });
  gallerySetMode($editor,'managed');
  gallerySync($editor);
  galleryToggleEmpty($editor);
}

function initGalleryEditor($editor){
  if($editor.attr('data-gallery-ready')==='1')return;
  $editor.attr('data-gallery-ready','1');
  var $grid=$editor.find('[data-gallery-grid]');
  if($.fn.sortable){
    $grid.sortable({
      items:'>[data-gallery-item]',
      handle:'[data-gallery-drag]',
      placeholder:'top-admin-gallery-item ui-sortable-placeholder',
      forcePlaceholderSize:true,
      update:function(){gallerySetMode($editor,'managed');gallerySync($editor);}
    });
  }
  galleryToggleEmpty($editor);
}

$(document).on('click','[data-gallery-add]',function(e){
  e.preventDefault();
  var $editor=$(this).closest('[data-gallery-editor]');
  var frame=wp.media({
    title:'Добавить фотографии товара',
    button:{text:'Добавить в галерею'},
    multiple:true,
    library:{type:'image'}
  });
  frame.on('select',function(){
    var items=[];
    frame.state().get('selection').each(function(model){
      var x=model.toJSON();
      if(!x||!x.url)return;
      items.push({
        attachment_id:x.id||0,
        src:galleryLocalUrl(x.url),
        preview:(x.sizes&&x.sizes.thumbnail&&x.sizes.thumbnail.url)||x.url,
        alt:x.alt||'',
        title:x.title||''
      });
    });
    galleryAddItems($editor,items);
  });
  frame.open();
});

$(document).on('click','[data-gallery-remove]',function(e){
  e.preventDefault();
  var $editor=$(this).closest('[data-gallery-editor]');
  $(this).closest('[data-gallery-item]').remove();
  gallerySetMode($editor,'managed');
  gallerySync($editor);
  galleryToggleEmpty($editor);
});

$(document).on('click','[data-gallery-clear]',function(e){
  e.preventDefault();
  var $editor=$(this).closest('[data-gallery-editor]');
  if($editor.find('[data-gallery-item]').length&&!window.confirm('Очистить фотографии товара? Изменение вступит в силу после сохранения.'))return;
  $editor.find('[data-gallery-grid]').empty();
  gallerySetMode($editor,'managed');
  gallerySync($editor);
  galleryToggleEmpty($editor);
});

$(document).on('click','[data-gallery-reset]',function(e){
  e.preventDefault();
  var $editor=$(this).closest('[data-gallery-editor]');
  if(!window.confirm('Вернуть исходную legacy-галерею этого товара? Управляемый список будет удалён после сохранения.'))return;
  var raw=$editor.find('[data-gallery-legacy-json]').text()||'[]',items=[];
  try{items=JSON.parse(raw)||[];}catch(err){items=[];}
  var $grid=$editor.find('[data-gallery-grid]').empty();
  items.forEach(function(item,i){$grid.append(galleryTemplate($editor,'legacy'+i,item));});
  gallerySetMode($editor,'legacy');
  gallerySync($editor);
  galleryToggleEmpty($editor);
});

$(document).on('input change','[data-gallery-item] input',function(){
  var $editor=$(this).closest('[data-gallery-editor]');
  if($(this).is('[data-gallery-src]')&&!$(this).is(':focus'))return;
  gallerySetMode($editor,'managed');
  if($(this).is('[data-gallery-src]'))$(this).closest('[data-gallery-item]').find('[data-gallery-attachment]').val('0');
  gallerySync($editor);
});

$(document).on('submit','form#post',function(){
  $('[data-gallery-editor]').each(function(){gallerySync($(this));});
});

$(function(){
  $('[data-gallery-editor]').each(function(){initGalleryEditor($(this));});
});
})(jQuery);