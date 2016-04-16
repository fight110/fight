

define(['jquery', 'app/pager'], function(require, exports, module) {
    var pager   = require('app/pager'), utype = $('#HDT-main').attr('utype');
    var api     = new pager('/menu/mlist/', {utype:utype}, {autorun:true});

    $('#HDT-main').on('click', '.add_menu', function(){
        var pid = this.getAttribute('data-pid'), id = this.getAttribute('data-id');
        require.async(['jquery/jquery.ui', 'app/form'], function(ui, Form){
            var template = $('#template').html(), dialog = $('<div title="菜单编辑">').html(template);
            dialog.dialog({width:400, autoOpen:true, open: function(){
                dialog.find('input[name=utype]').val(utype);
                dialog.find('input[name=pid]').val(pid);
                if(id) {
                    $.get("/menu/menuinfo/" + id, {}, function(json){
                        Form.formfill(dialog, json.menu);
                    }, 'json');
                }
            }}).on('dialogclose', function(){
                dialog.dialog('destroy');
            }).on('submit', 'form', function(e){
                var form = dialog.find('form'), url  = form.attr('action'), data = form.serialize();
                $.post(url, data, function(json){
                    if(json.error) {
                        require.async('jquery/jquery.notify', function(n){
                            n.warn({title:"提示", text:json.message}, {expires:2000});
                        });
                    }else{
                        require.async('jquery/jquery.notify', function(n){
                            n.message({title:"提示", text:'提交成功'}, {expires:2000});
                        });
                        setTimeout(function(){
                            api.reload();
                            dialog.dialog('close');
                        }, 500);
                    }
                }, 'json');
                return false;
            });
        });
    });

    $('#HDT-main').on('change', ':checkbox', function(){
        var pid = this.getAttribute('data-pid'), checked = this.checked, id = this.value;
        if(pid == 0) {
            $('#HDT-main :checkbox').each(function(){
                if(this.getAttribute('data-pid') == id) {
                    this.disabled = !checked;
                    if(checked) {
                        this.checked = checked;
                    }
                }
            });
        }
    });

});