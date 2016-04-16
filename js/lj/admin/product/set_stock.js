

define(['jquery'], function(require, exports, module) {
    var $table = $('#HDT-stock-table');
    if(stock_list.length){
    	for(var i = 0, len = stock_list.length; i < len; i++){
    		var unit = stock_list[i], 
    			product_id = unit.product_id, 
    			product_color_id = unit.product_color_id, 
    			product_size_id = unit.product_size_id,
    			totalnum = unit.totalnum,
    			selector = 'input[name="stock-'+product_id+'-'+product_color_id+'-'+product_size_id+'"]';
    		$(selector).val(totalnum);
    	}
    }

});