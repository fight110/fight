

define(['jquery'], function(require, exports, module) {
    var $add    = $('.HDT-add-message'), tmpl = $('#tmpl-message-add').html(), dialog = null, select;

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
        var target = e.currentTarget, id = target.getAttribute('data-id');
        $add.trigger('click');
        require.async('app/form', function(Form){
            $.get("/message/info/" + id, {}, function(json){
                Form.formfill(dialog, json.message);
                setTimeout(function(){

                }, 800);
            }, 'json');
        });
        return false;
    });
});