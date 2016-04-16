

define(['jquery'], function(require, exports, module) {
    var $select_menu = $('.select_menu');
    $select_menu.on('click', 'p', function(e){
        var target = e.currentTarget, $target = $(target), $ul = $target.next();
        $ul.toggle();
    });

    $('body').on('click', 'a.HDT-delete', function(e){
        var target = e.currentTarget, id = target.getAttribute('data-id');
        if(id){
            require.async('jquery/jquery.notify', function(n){
                n.confirm({title:"确定移除该产品?", text:"移除后不能恢复"}, function(){
                    var link = location.href;
                    location.href = "/product/delete/" + id + "?returl=" + link;
                });
            });
            return false;
        }
    });
    var $checkbox = $('body').find('input[name=show_id]:checkbox');
    $checkbox.on('click', function(e){
        var id = this.value, old = $checkbox.filter(':checked').not(this)[0];
        if(old){
            old.checked = '';
        }

        this.checked = 'checked';
        $.post('/company/setshow', {show_id:id}, function(json){
            var message = json.valid ? "设置当前show款成功" : json.message;
            require.async('jquery/jquery.notify', function(n){
                n.message({title:"设置show款", text:message}, {expires:2000});
            });
        }, 'json');
    });

    $('body').on("click", 'input[name=hot_id]:checkbox', function(e){
        var hot_id = this.value, that = this;
        if(hot_id){
            $.post("/product/hot/" + hot_id, {"hot":this.checked ? 1 : 0}, function(json){
                var message = json.valid ? "设置爆款成功" : json.message;
                require.async('jquery/jquery.notify', function(n){
                    n.message({title:"设置爆款", text:message}, {expires:2000});
                });
                if(that.checked){
                    $(that).parents("li").find(".nid").append('<img class="icoimg" src="/style/images/bao.gif">');
                }else{
                    $(that).parents("li").find(".nid .icoimg").remove();
                }
            }, 'json');
        }
    });
});