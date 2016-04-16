

define(['jquery'], function(require, exports, module) {
    var $add    = $('.HDT-add-user'), tmpl = $('#tmpl-user-add').html(), dialog = null, select;

    $add.on('click', function(e){
        if(dialog !== null){
            select = null;
            dialog.dialog('destroy');
        }
        
        require.async(['jquery/jquery.ui'], function(ui){
            dialog = $('<div>').html(tmpl).dialog({width:400, autoOpen:true});
        });
    });

    $('body').on('click', '.HDT-edit', function(e){
        var target = e.currentTarget, id = target.getAttribute('data-id'), content = target.getAttribute('data-content');
        $add.trigger('click');
        require.async('app/form', function(Form){
            if(dialog !== null){
                Form.formfill(dialog, {id:id, content:content});
            }else{
                var callback = arguments.callee;
                setTimeout(function(){callback.call(null, Form)}, 200);
            }
        });
    });

    $('#HDT-main-table').on('click', ':radio', function(e){
        var target = e.currentTarget, content = target.getAttribute('data-content');
        if(content){
            $.post('/status/setstatus/', {content:content}, function(json){
                var message     = json.valid ? "设置成功" : json.message;
                require.async('jquery/jquery.notify', function(n){
                    n.message({title:"提示", text:message}, {expires:1000});
                })
            }, 'json');
        }
    });
});