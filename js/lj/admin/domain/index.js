

define(['jquery'], function(require, exports, module) {
    $('.HDT-add').on('click', function(e){
        $.get("/domain/domain_html",{},function(html){
            var dialog = $('<div title="新增域名">').html(html);
            require.async(['jquery/jquery.ui'],function(){
                dialog.dialog({autoOpen:true, open: function(){
                    //
                }}).on('dialogclose', function(){
                    dialog.dialog('destroy');
                });
            });
        },'html');
    });

	$('body').on('click','#HDT-sub-btn',function(){
		var data    = $('#domain_add').serialize();
        $.get("/domain/add",data,function(json){
            require.async('jquery/jquery.notify', function(n){
                n.message({title:"提示", text:json.message}, {expires:2000});
            });
            if(json.valid){
                setTimeout(function(){
                    location.reload(); 
                }, 2000);
            }
        });
	});

    $('.HDT-edit').on('click',function(e){
        var id = $(this).attr('data-id');
        $.get("/domain/domain_html",{id:id},function(html){
            var dialog = $('<div title="新增域名">').html(html);
            require.async(['jquery/jquery.ui'],function(){
                dialog.dialog({autoOpen:true, open: function(){
                    //
                }}).on('dialogclose', function(){
                    dialog.dialog('destroy');
                });
            });
        },'html');
    })
});