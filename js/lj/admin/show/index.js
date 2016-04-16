

define(['jquery'], function(require, exports, module) {
    var $add    = $('.HDT-add-user'), tmpl = $('#tmpl-user-add').html(), dialog = null;

    $add.on('click', function(e){
        if(dialog !== null){
            dialog.dialog('destroy');
        }
        
        require.async(['jquery/jquery.ui'], function(ui){
            dialog = $('<div>').html(tmpl).dialog({width:400, autoOpen:true});
        });
    });

    $('body').on('click', '.HDT-edit', function(e){
        var target = e.currentTarget, 
            id = target.getAttribute('data-id'), 
            name = target.getAttribute('data-name');
        $add.trigger('click');

        (function(){
            if(dialog === null){
                setTimeout(arguments.callee, 200);
            }else{
                dialog.find("input[name='id']").val(id);
                dialog.find("input[name='name']").val(name);
            }
        })();
    });

});