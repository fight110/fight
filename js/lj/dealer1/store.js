

define(['jquery', 'app/pager', 'app/lazy', 'app/order', 'app/ordertable'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api, order = require('app/order'), $list = $('#HDT-main'), OrderTable = require('app/ordertable');
    

    api = new pager('/product/storelist', {}, {autorun:true, message:"松开刷新", aftercallback:function(html){
        if(html){
            var $html = $(html), table = $html.find('table[data-product-id]'), product_ids = table.map(function(){
                var product_id = this.getAttribute('data-product-id');
                new OrderTable($('#HDT-ot'+product_id)[0], product_id);
                return product_id;
            }).get();
            if(product_ids.length){
                $.get('/orderlist/list', {product_ids:product_ids.join(',')}, function(json){
                    var list = json.list, len = list.length;
                    if(len){
                        for(var i = 0; i < len; i++){
                            var row = list[i], product_id = row.product_id, color_id = row.product_color_id, size_id = row.product_size_id;
                            var t = $list.find('input[data-product-id='+product_id+'][data-color-id='+color_id+'][data-size-id='+size_id+']');
                            var num = list[i].num;
                            t.val(num);
                            OrderTable.set(product_id, color_id, size_id, num);
                        }
                    }
                }, 'json');
            }
            require.async(['rateit.ref'], function(){
                $('.rateit_new').rateit({}).removeClass('rateit_new');
            });
        }
    }});

    new lazy('.foot', function(){api.next()}, {delay:100, top:100});

    var $menu   = $('#HDT-select-menu');
    $menu.on('change', 'select', function(e){
        var target = e.currentTarget;
        if(target.name == "category_id") {
            $.get('/location/get_classes_list', {category_id:target.value}, function(html){
                $menu.find('select[name=classes_id]').replaceWith(html);
            });
            api.set(target.name, target.value, true);
            api.set('classes_id', 0, true);
            api.reload();
        }else{
        	api.set(target.name, target.value);
        }
    });

    var $rateval_table = $('#HDT_rateval_table'), $rateval_tds = $rateval_table.find('td');
    $rateval_table.on('touchstart click', 'td[data-rateval]', function(e){
        e.preventDefault();
        var rateval = this.getAttribute('data-rateval');
        api.set('rateval', rateval);
        $rateval_tds.removeClass('colblue');
        $(this).addClass('colblue');
        return false;
    });

    $list.on('click', '.HDT-order-store', function(e){
        e.stopPropagation();
        var product_id = this.getAttribute('data-product-id'), that = this;
        if(product_id){
            $.post('/product/set_user_product', {product_id:product_id,status:0}, function(json){
                var parent = $(that).parents('li'), next = parent.next('li'), run_time = 25, run_interval = 30, height = parent.height(), diff_height = height / run_time;
                setTimeout(function(){
                    var callback    = arguments.callee;
                    height -= diff_height;
                    if(height >= 0){
                        parent[0].style.height = height + 'px';
                        // next[0].style.height = height + 'px';
                        setTimeout(callback, run_interval);
                    }else{
                        parent.remove();
                        next.remove();
                    }
                }, run_interval);
            }, 'json');
        }
    });

    
    var apiArr = [api];
    require.async('lj/fancybox',function(fancybox){
    	fancybox(apiArr,'');
    });
});