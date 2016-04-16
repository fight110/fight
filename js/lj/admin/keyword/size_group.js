

define(['jquery'], function(require, exports, module) {
    var $sortable = $('#sortable'), $savelist = $('#SAVELIST');
    require.async('jquery/jquery.ui', function(ui){
        $sortable.sortable({});
        $sortable.disableSelection();
    });

    $savelist.on('click', function(e){
        var i = 1, list = [], t = $('#FactoryTable').val();
        $sortable.find('tr').each(function(){
            var id      = this.getAttribute('data-id'), rank = i++;
            list.push(id+":"+rank);
        });
        $.post('/keyword/setrank/', {t:t,list:list.join(',')}, function(json){
            var message     = json.valid ? "保存成功" : json.message;
            require.async('jquery/jquery.notify', function(n){
                n.message({title:"提示", text:message}, {expires:2000});
            });
        }, 'json');
    });

    $('.size_group_units').on('click', '.edit', function(e){
    	var target = e.delegateTarget, hasShow = target.getAttribute('hasShow');
    	if(hasShow == 1) {
    		$(target).find('input:checkbox').each(function(){
    			if(this.checked === false) {
    				$(this).parent().fadeOut();
    			}
    		});
    		target.setAttribute('hasShow', 0);
    	}else{
    		$(target).find('input:checkbox').parent().fadeIn();
    		target.setAttribute('hasShow', 1);
    	}
    });

    $('.size_group_units').on('change', ':checkbox', function(e) {
    	var size_group_id = this.getAttribute('data-size-group-id'),
    		size_id 	  = this.getAttribute('data-size-id'),
    		checked 	  = this.checked ? 1 : 0;
    	$.post('/keyword/size_group_set', {size_id:size_id,size_group_id:size_group_id, checked:checked}, function(json){
    		if(json.error) {
    			alert(json.message);
    		}
    	}, 'json');
    });
});