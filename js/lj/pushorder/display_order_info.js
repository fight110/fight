define(['jquery' ,'app/pager'], function(require, exports, module) {
    var display_id  = $('#HDT-main').attr('data-id');
    var pager       = require('app/pager');
    
    if($('.Selects').length){
        require.async('app/admin.select', function(select){
            select(api);
        });   
    }

    var api = new pager("/pushorder/display_order_info_api",{display_id:display_id},{autorun:true,aftercallback:function(){
        setTimeout(function(){
            $("#user-display").height($("#user-display img").height()+30);
            $("#user-group").show();
        },1000);
    }});

    $('#HDT-select-menu').on('change', 'select', function(e){
        var target = e.currentTarget;
        api.set(target.name, target.value);
        api.next();
    });
    
    $('#mini_menu').on('click', function(){
        setTimeout(function(){
            $("#user-display").height($("#user-display img").height()+30);
        },100);
    });

	$('.dealer-order').on('click','#show-btn',function(){
		var title = $(this).html();
		if(title=="显示标准效果"){
			$(this).html("隐藏标准效果");
		}else{
			$(this).html("显示标准效果");
		}
        $('#std-display').toggle();
		$('#std-group').toggle();
	})
	$('.dealer-order').on("click",".title .right-btn",function(){
        var data = $(this).attr("data");
        $.get("/pushorder/display_next",{display_id:display_id,f:data},function(json){
            if(json.valid){
                location.href = location.href.replace(/\/\d+/,'/'+json.id);
            }else{
                require.async('jquery/jquery.notify', function(n){
                    n.message({title:"提示", text:json.message}, {expires:2000});
                });
            }
        },'json');
    })
     $('#HDT-FORM').submit(function(e){
        e.preventDefault()
        var bianhao = $("#search_key").val();
        if(bianhao){
            $.get("/pushorder/display_by_bianhao",{bianhao:bianhao},function(json){
                if(json.display_id){
                    location.href = location.href.replace(/\/\d+/,'/'+json.display_id);
                }else{
                    require.async('jquery/jquery.notify', function(n){
                        n.message({title:"提示", text:"未找到对应陈列"}, {expires:2000});
                    });
                }
            },'json')
        }
    });

});