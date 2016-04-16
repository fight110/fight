

define(['jquery'], function(require, exports, module) {
    var $add    = $('.HDT-add-user'), tmpl = $('#tmpl-user-add').html(), dialog = null, room_id = $('body').attr('data-room-id');

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
            dpnum = target.getAttribute('data-dpnum'),
            bianhaos = target.getAttribute('data-bianhaos');
        $add.trigger('click');

        (function(){
            if(dialog === null){
                setTimeout(arguments.callee, 200);
            }else{
                dialog.find("input[name='id']").val(id);
                dialog.find("input[name='dp_num']").val(dpnum);
                var ary_bh = bianhaos.split(','), input_bh = dialog.find("input.HDT-bianhao");
                for(var i = 0, len = ary_bh.length; i < len; i++){
                    if(ary_bh[i]){
                        input_bh[i].value = ary_bh[i];
                    }
                }
            }
        })();
    });

    var lastRadio = null; 
    $('#HDT-main-table').on('click', ':radio,.HDT-label', function(e){
        var target = e.currentTarget, id;
        if(this.type != 'radio'){
            if(lastRadio){
                lastRadio.checked = false;
            }
            target = $(this).find('input:radio')[0];
            target.checked = 'checked';
            id = target.value;
        }
        lastRadio = target;
        if(id){
            $.post('/show/set_current_show/', {id:id, room_id:room_id}, function(json){
                var message     = json.valid ? "设置成功" : json.message;
                require.async('jquery/jquery.notify', function(n){
                    n.message({title:"提示", text:message}, {expires:1000});
                })
            }, 'json');
        }
    });

});