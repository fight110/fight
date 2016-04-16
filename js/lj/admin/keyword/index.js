

define(['jquery'], function(require, exports, module) {
    require.async('lj/admin/keyword/saverank');
    var tmpl = $('#tmpl-edit').html();
    var tmplAdd = $('#tmpl-add').html();

    $('body').on('click', '.HDT-edit', function(e){
    	var target = e.currentTarget, id = target.getAttribute('data-id'), name = target.getAttribute('data-name'), order = target.getAttribute('data-rank');
        require.async(['jquery/jquery.ui'], function(ui){
            var dialog = $('<div>').html(tmpl).dialog({width:400, autoOpen:true});
            dialog.find("input[name=name]").val(name);
            dialog.find("input[name=id]").val(id);
            dialog.find("input[name=order]").val(order);
        });
    });

    $('body').on('click', '.HDT-add-but', function(e){
        require.async(['jquery/jquery.ui'], function(ui){
            var dialog = $('<div>').html(tmplAdd).dialog({width:400, autoOpen:true});
        });
    });
    
    
    $('span.tdedit').on('click',function(){
    	var s_val   = $(this).text(),
		s_name  = $(this).attr('data-field'),
		s_id    = $(this).attr('data-id'),
		s_t    = $(this).attr('data-t'),
		width   = $(this).width();
		$('<input type="text" class="lt_input_text" value="'+s_val+'" />').width(width).focusout(function(){
			$(this).prev('span').show().text($(this).val());
			if($(this).val()==''){
				alert('输入不能为空!');
			}else if($(this).val() != s_val) {
				$.getJSON('/keyword/update', {id:s_id, field:s_name, t:s_t, val:$(this).val()}, function(result){
					if(result.status != 1) {
						alert('操作失败！');
						return;
					}
				});
			}
			$(this).remove();
		}).insertAfter($(this)).focus().select();
		$(this).hide();
		return false;
    })
});