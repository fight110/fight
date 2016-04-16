

define(['jquery'], function(require, exports, module) {
    var $select_menu = $('.select_menu');
    $select_menu.on('click', 'p', function(e){
        var target = e.currentTarget, $target = $(target), $ul = $target.next();
        $ul.toggle();
    });
    
    $('body').on('click', 'a.HDT-delete', function(e){
        return confirm("确定移除该搭配?移除后不能恢复");
    });
    
    $('body').on('click','.selectAll',function(e){
    	var target = e.currentTarget;
    	var status = target.checked;
    	var input = document.getEl
    	var ids=$(".displayStatus");
    	for(var i=0;i<ids.length;i++){
    		ids[i].checked=status;   
    	}
    })
    $('body').on('click','.updateBtn',function(){
    	var ids=$(".displayStatus");
    	var select = [];
    	var notSelect = [];
    	var status;
    	for(var i=0;i<ids.length;i++){
    		status = ids[i].checked;
    		if(status===true){
    			select.push(ids[i].value);
    		}else{
    			notSelect.push(ids[i].value) ;
    		}
    	}
    	//console.log(select);
    	//console.log(notSelect);
    	if(confirm('确认更新吗？'))
    	{
    	$.post('/display/update_display_status',{sel:select,notSel:notSelect},function(data){
    		if(data.code==1){
    			alert(data.message);
    		}else{
    			alert('参数错误！');
    		}
    	},'json')
    	}
    })
});