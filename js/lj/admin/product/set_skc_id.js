

define(['jquery'], function(require, exports, module) {
    var $colortable = $('#colortable'), product_id = $colortable.attr('data-product-id'),moq_status	=	$colortable.attr('data-moq-status');
    $('#add_color').on('click', function(){
        require.async(['jquery/jquery.ui'], function() {
            $.get('/product/select_color', function(html){
                var $el = $('<div>').html(html).dialog({width:600, autoOpen:true}), $checkboxs = $el.find(':checkbox');
                $el.on('keyup', '.color_filter', function(){
                    var value = this.value, reg = new RegExp(value);;
                    $checkboxs.each(function(){
                        var name = this.getAttribute('data-name');
                        if(value && !reg.test(name)) {
                            $(this).parent('td').hide();
                        }else{
                            $(this).parent('td').show();
                        }
                    });
                });
                $el.on('submit', function(e){
                    $el.find(':checked').each(function(){
                        var value = this.value, name = this.getAttribute('data-name'),
                            html = '<tr data-color-id="'+value+'"><td>'+name+'</td>' + 
                                    '<td><input type="text" name="color_code_'+product_id+'_'+value+'" value=""></td>'+
                                    '<td><input type="text" name="skc_id_'+product_id+'_'+value+'" value=""></td></tr>';
                        if($colortable.find('tr[data-color-id='+value+']').length == 0) {
                            $colortable.append(html);
                        }
                    });
                    $el.dialog('close');
                    return false;
                });
            });
        });
        
    });
    
    $('.replace_color').on('click',function(){
    	var color_id=$(this).attr("color_id"),list	=	[];
    	$colortable.find("tr[data-color-id]").each(function(){
    		list.push($(this).attr('data-color-id'));
    	});
    	require.async(['jquery/jquery.ui'], function() {
    		$.get('/product/select_color',{list:list}, function(html){
    			var $el = $('<div>').html(html).dialog({width:600, autoOpen:true}), $checkboxs = $el.find(':checkbox');
    			$el.on('keyup', '.color_filter', function(){
                    var value = this.value, reg = new RegExp(value);;
                    $checkboxs.each(function(){
                        var name = this.getAttribute('data-name');
                        if(value && !reg.test(name)) {
                            $(this).parent('td').hide();
                        }else{
                            $(this).parent('td').show();
                        }
                    });
                });
                $el.on('submit', function(e){
                	if(confirm("颜色更改后对应订单将会转移至新颜色中，确认继续操作?")){
                		var value = $("input[name='color']:checked").val(),name = $("input[name='color']:checked").attr('data-name');
                        var $old_color_table	=	$colortable.find('tr[data-color-id="'+color_id+'"]');
                		var color_code	=	$old_color_table.find('input[name="color_code_'+product_id+'_'+color_id+'"]').val();
                		var skc_id		=	$old_color_table.find('input[name="skc_id_'+product_id+'_'+color_id+'"]').val();
                		if(moq_status){
                			var moq_num		=	$old_color_table.find('input[name="moq_'+product_id+'_'+color_id+'"]').val();
                			var moq_html	=	'<td><input type="text" name="moq'+product_id+'_'+value+'" value="'+moq_num+'"></td>';
                		}
	                    var html = '<tr data-color-id="'+value+'"><td>'+name+'</td>' + 
	                                    '<td><input type="text" name="color_code_'+product_id+'_'+value+'" value="'+color_code+'"></td>'+
	                                    '<td><input type="text" name="skc_id_'+product_id+'_'+value+'" value="'+skc_id+'"></td>'+ moq_html +
	                                    '<td><input type="button" value="替换颜色" color_id='+value+' class="replace_color"></td></tr>';
	                        if($old_color_table.length) {
	                        	$.post('/product/replace_color',{product_id:product_id,color_id:color_id,new_color_id:value},function(json){
	                        		$old_color_table.remove();
	                            	$colortable.append(html);
	                                require.async('jquery/jquery.notify', function(n){
	                                    n.message({title:"颜色替换", text:json.message}, {expires:2000});
	                                });
	                        	},'json');
	                        }
	                    $el.dialog('close');
                	}
                    return false;
                });
    		});
    	})
    })

});