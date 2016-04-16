
define(['jquery', 'iscroll.ref', 'app/pager', 'app/order'], function(require, exports, module) {
    var myScroll = null, api = null, groupapi = null, pager = require('app/pager'), $body = $('body'), $form = $('#HDT-form-product'),
        $w = $(window), $loading = $('.loading');
    var $order  = $('#HDT-order-table'), order = require('app/order'), $copy_list = null;
    var $comment_form = $('#HDT-form-comment'), $comment_list = $('#HDT-list-comment'), product_id = $comment_form.find('[name=product_id]').val(),
        $count_all = $('#HDT-count-all'), minimum = parseInt($('#HDT-minimum').html()), $foot_menu = $('#HDT-foot-menu');
    
    
    order.setmini();
    
    if(product_id){
        api = new pager('/comment/list/'+product_id, {}, {id:'#HDT-list-comment', aftercallback:function(html){
            if(!html){
                $('.loading').remove();
            }
            myScroll.refresh();
        }});

        groupapi = new pager('/product/', {group_product_id:product_id,limit:12}, {id:'#HDT-group-list', aftercallback:function(html){
            myScroll.refresh();
        }});
    }

    $w.on('resize', function(){
        var h = $w.height();
        if(h < 300){
            $foot_menu.hide();
        }else{
            $foot_menu.show();
        }
    });

    var $is_show = $('#HDT-is-show'), show_id = $('#HDT-show-id').val(), time_show = 1000 * 5;
    if($is_show.length){
        if($is_show.val() == "off"){
            require.async('jquery/jquery.notify', function(n){
                n.confirm({title:"当前show款未开始", text:"当前show款未开始"}, function(){
                    location.href = "/dealer1";
                }, {mini:true});
            });
        }else{
            setTimeout(function(){
                var callback = arguments.callee;
                $.get('/index/get_show_id', {}, function(json){
                    if(json.show_id && json.show_id != show_id){
                        $('<div class="refresh_show"></div>').appendTo('body').on('click', function(e){
                            location.href = "/index/show";
                        });
                    }else{
                        setTimeout(callback, time_show);
                    }
                }, 'json');
            }, time_show);

        }

    }

    

    
    $comment_form.on('submit', function(e){
        var data = $comment_form.serialize();
        $.post('/comment/add', data, function(json){
            var message = json.valid ? "发布成功" : json.message;
            require.async('jquery/jquery.notify', function(n){
                n.message({title:"提示",text:message}, {expires:1000,mini:true});
            });
            if(json.valid){
                $comment_form.find("textarea").val("");
                $comment_list.prepend(json.html);
            }
            myScroll.refresh();
        }, 'json');
        return false;
    });

    var score = $comment_form.find('input:radio');
    $comment_form.on('click', 'td', function(e){
        var target = e.currentTarget, radio = $(target).find(':radio')[0];
        if(radio){
            score.each(function(){this.checked = '';});
            radio.checked = 'checked';
        }
    });

    

    
    var count_order = function(target){
        var color_id = target.getAttribute('data-color-id'), 
            list = $order.find('input[data-color-id='+color_id+']').get(), count = 0;
        for(var i = 0, len = list.length; i < len; i++){
            if($.isNumeric(list[i].value)){
                count += parseInt(list[i].value);
            }
        }
        $order.find('td[data-color-id='+color_id+']').html(count);
        count = 0;
        $order.find('input').each(function(){
            if($.isNumeric(this.value)){
                count += parseInt(this.value);
            }
        });
        $count_all.html(count);
    }
    $order.on('keyup', 'input', function(e){
        var target      = e.currentTarget,
            color_id    = target.getAttribute('data-color-id'), 
            size_id     = target.getAttribute('data-size-id');
        count_order(target);
    });
    // $order.on('focus', 'input', function(){
    //     $foot_menu.hide();
    // });
    // $order.on('blur', 'input', function(){
    //     $foot_menu.show();
    // });
    $order.on('change', 'input', function(e){
        var target      = e.currentTarget,
            color_id    = target.getAttribute('data-color-id'), 
            size_id     = target.getAttribute('data-size-id');
        order.add(product_id, color_id, size_id, target.value);
    });
    var fix_click_twice = false;
    $order.on('click', '.HDT-order-copy', function(e){
        if(fix_click_twice){
            return false;
        }
        var target  = e.currentTarget, color_id = target.getAttribute('data-color-id');
        $copy_list  = $order.find("input[data-color-id="+color_id+"]");
        require.async('jquery/jquery.notify', function(n){
            n.message({title:"复制", text:"复制订单成功"}, {expires:2000,mini:true});
        });
        fix_click_twice = true;
        setTimeout(function(){fix_click_twice=false}, 500);
        return false;
    });
    $order.on('click', '.HDT-order-paste', function(e){
        if(fix_click_twice){
            return false;
        }
        var target  = e.currentTarget, color_id = target.getAttribute('data-color-id'),
            list    = $order.find("input[data-color-id="+color_id+"]");
        if($copy_list === null){
            require.async('jquery/jquery.notify', function(n){
                n.message({title:"粘贴", text:"没有复制来源"}, {expires:2000,mini:true});
            });
        }else{
            for(var i = 0, len = list.length; i < len; i++){
                list[i].value   = $copy_list[i].value;
                $(list[i]).trigger('change');
            }
            count_order(list[0]);
        }
        fix_click_twice = true;
        setTimeout(function(){fix_click_twice=false}, 500);
        return false;
    });

    
    $('#HDT-order-cancel').on('click', function(e){
        require.async('jquery/jquery.notify', function(n){
            n.confirm({title:"取消订单", text:"确定删除此订单?"}, function(){
                $order.find('input').each(function(){this.value=""});
                $order.find(".HDT-order-count").each(function(){this.innerHTML=""});
                order.reset();
                order.remove(product_id);
            });
        }, {mini:true});
    });

    $('#HDT-order-save').on('click', function(e){
        var num = parseInt($count_all.html());
        if(num < minimum){
            require.async('jquery/jquery.notify', function(n){
                var rest = minimum - num;
                n.message({title:"提示", text:"未到最小起订量:"+minimum + ",还差"+rest}, {expires:2000, mini:true});
            });
        }else{
            order.save();
        }
    });

    order.initOrderList(product_id, function(json){
        var list = json.list, len = list.length;
        if(len){
            for(var i = 0; i < len; i++){
                var t = $order.find('input[data-color-id='+list[i].product_color_id+'][data-size-id='+list[i].product_size_id+']');
                t.val(list[i].num);
                count_order(t[0]);
            }
        } 
    });
    
    

    $(function(){
        myScroll = require('iscroll.ref');
        myScroll.on('ScrollEnd', function(){
            var wh = $w.height(), offset;
            if(api !== null){
                offset = $loading.offset();
                if(offset.top < wh){
                    api.next();
                }
            }
            if(groupapi !== null){
                offset = $('#HDT-group-list').offset();
                if(offset.top < wh){
                    groupapi.next();
                }
            }
        });

        new iScroll('bigimage', {
            snap: 'li',
            momentum: false,
            hScrollbar: false,
            vScrollbar: false
        });

        $('#order-wrapper').height($order.height() + 20);

        new iScroll('order-wrapper', {
            snap: true,
            momentum: false,
            vScroll:false,
            hScrollbar: false,
            vScrollbar: false,
            useTransform: false,
            onBeforeScrollStart: function (e) {
                var target = e.target; 
                while (target.nodeType != 1) target = target.parentNode; 
                if (target.tagName != 'SELECT' && target.tagName != 'INPUT' && target.tagName != 'TEXTAREA') e.preventDefault(); 
            }
        });

    });
    

    

});