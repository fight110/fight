

define(['jquery'], function(require, exports, module) {
    var $add    = $('.HDT-add-user'),$subbtn = $('#HDT-sub-btn'),$form   = $('#HDT_sub'),tmpl = $('#tmpl-user-add').html();

    function openDialog (options) {
        var that        = this;
        this.options    = options;
        require.async(['jquery/jquery.ui'], function(ui){
            var dialog = $('<div title="帐号编辑">').html(tmpl);
            that.dialog = dialog;
            dialog.dialog({width:600, autoOpen:true, open: function(){
                if(options.afterOpen) {
                    options.afterOpen.call(that);
                }
            }}).on('dialogclose', function(){
                dialog.dialog('destroy');
            });
            
        });
    }

    $add.on('click', function(e){
        new openDialog({});
    });

	$('body').on('click','#HDT-sub-btn',function(){
		var data    = $('#HDT_sub').serialize();
		var api     = "/designer/add";
		$.ajax({url: api,type: "POST",data: data,dataType: "json",
            beforeSend : function(xhr){}
        }).done(function(json){
            var message = json.message ? json.message : json.valid ? '保存成功' : '保存失败';
            require.async('jquery/jquery.notify', function(n){
                n.message({title:"提示", text:json.message}, {expires:2000});
            });
            if(json.valid){
                setTimeout(function(){
                    location.reload(); 
                }, 2000);
            }
        });
		
		return false;
	});

    $('body').on('click', '.HDT-edit', function(e){
        var target = e.currentTarget, id = target.getAttribute('data-id');
        new openDialog({
            query : {aid:id},
            afterOpen : function() {
                var dialog = this.dialog, select = this.select;
                require.async('app/form', function(Form){
                    $.get("/dealer/user/" + id, {}, function(json){
                        Form.formfill(dialog, json.user);
                    }, 'json');
                });
            }
        });
        
        return false;
    });
});