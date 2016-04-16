

define(['jquery'], function(require, exports, module) {

    function Indicator () {
        var template = $('#TemplateIndicator').html(), dialog = $('<div title="编辑指标">').html(template);
        require.async(['jquery/jquery.ui'], function(ui){
            dialog.dialog({width:400, autoOpen:true, open: function(){
            }}).on('dialogclose', function(){
                dialog.dialog('destroy');
            });
        });
        this.dialog = dialog;
        dialog.on('submit', 'form', function(){
            var data = $(this).serialize(), url = this.action, method = this.method;
            $.ajax({
                url  : url,
                type : method,
                data : data,
                dataType : 'json'
            }).done(function(json){
                var message = json.message;
                require.async('jquery/jquery.notify', function(n){
                    n.message({title:"提示", text:message}, {expires:2000});
                });
                if(!json.error) {
                    setTimeout(function(){
                        location.reload();
                    }, 2000);
                    dialog.dialog('destroy');
                }
            });
            return false;
        });
    }
    Indicator.prototype = {
        set_indicator : function (id) {
            var dialog  = this.dialog;
            require.async('app/form', function(Form){
                $.get("/indicator/indicator/" + id, {}, function(json){
                    Form.formfill(dialog, json.indicator);
                }, 'json');
            });
        }
    }

    $('.HDT-edit').on('click', function(){
        var id  = this.getAttribute('data-id'), indicator = new Indicator;
        if(id) {
            indicator.set_indicator(id);
        }
    });

    $('.HDT-add').on('click', function(e){
        e.preventDefault();
        var user_id = this.getAttribute('data-user-id'), field = this.getAttribute('data-field');
        new Open(this.innerHTML, this.href, {data:{user_id:user_id, field:field}, options:{position:['center', 250]}});
        return false;
    });

    function Open (title, url, params) {
        this.title = title;
        this.url   = url;
        this.options = $.extend({
            width:600, autoOpen:true, open: function(){}
        }, params.options || {});
        var dialog = $('<div title="'+title+'">'), that = this;
        require.async(['jquery/jquery.ui'], function(ui){
            dialog.dialog(that.options).on('dialogclose', function(){
                dialog.dialog('destroy');
            });
        });

        $.get(url, {}, function(html){
            dialog.html(html);
        });

        dialog.on('submit', 'form', function(){
            var list = [];
            dialog.find('tr[data-keyword-id]').each(function(){
                var keyword_id = this.getAttribute('data-keyword-id'), data = $.extend({keyword_id:keyword_id}, params.data || {});
                $(this).find('input').each(function(){
                    data[this.name] = this.value;
                });
                list.push(data);
            });
            var callback = function() {
                var data = list.shift();
                if(data) {
                    $.post('/indicator/adding', data, function(json){
                        callback();
                    }, 'json');
                }else{
                    location.reload();
                }
            }
            callback();
            return false;
        });
    }

});