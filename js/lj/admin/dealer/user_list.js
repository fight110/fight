

define(['jquery'], function(require, exports, module) {
	if($('.searchSelects').length){
		require.async(['app/selects'], function(Selects){
	        new Selects('/location/json', {dataType:'json', selector:'.searchSelects'});
	    });
	}
	
	// $("#add_user").on('click',function(){
	// 	var list	=	new Array();
	// 	$("ul").find(":checked").each(function(){
	// 		list.push($(this).val());
	// 	})
	// 	var res	=	list.join(';');
	// 	$("#username", window.parent.document).val(res);
	// 	window.parent.jQuery.fancybox.close();
	// });
	
	
});