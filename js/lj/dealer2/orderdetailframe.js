

define(['jquery', 'lj/dealer1/orderdetailframe'], function(require, exports, module) {
	var iframe = $('iframe')[0];
	$('#HDT-select-menu').on('change', '[name=fliter_uid]', function(e){
		iframe.src = "/dealer2/orderdetail/" + this.value;
	});
	require('lj/dealer1/orderdetailframe');
});