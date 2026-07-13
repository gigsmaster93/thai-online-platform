
 function shEvOrd(type,obj,act){
 if($('#checkout-form').length){
 $('#'+type+'_id').attr('value',obj.value);
 if((type == 'payment') || (type == 'delivery')){
 $('span.osum').html('<img alt="" src="/.s/img/icon/ajsml.gif" style="vertical-align:-4px;">');
 $('#checkout-form-mode').attr('value','change');
 _uPostForm('checkout-form');
 }
 }
 }

 function shopCheckOut(){
 if(_shopLockButtons()) return false;
 _shopFadeControl('cont-shop-checkout');
 $('#checkout-form-mode').attr('value','order');
 _uPostForm('checkout-form');
 ga_event('checkout_done'); 
 
 return false;
 }

 ga_event('checkout');