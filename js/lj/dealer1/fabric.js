

define(['jquery', 'app/pager', 'app/lazy', 'app/ordertable', 'app/order'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager = require('app/pager'), q = $('#HDT-hidden-q').val(), OrderTable = require('app/ordertable'), order = require('app/order'),
        $list = $('#HDT-main'), api = null, api_fabric = null, $menu = $('#HDT-select-menu'), tType = $list.attr('data-tType');

    var data = {q:q,tType:tType};
    $menu.find('select').each(function(){
        data[this.name] = this.value;
    });
    api = new pager('/product/list', data, {autorun:true,message:"松开刷新", aftercallback:function(html){
        if(tType && html){
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
        }
    }});
    new lazy('.foot', function(){api.next()}, {delay:200, top:200});
    api_fabric = new pager('/product/fabric', {complete:0}, {id:'#HDT-fabric'});

    $menu.on('change', 'select', function(e){
        var target = e.currentTarget;
        api.set(target.name, target.value);
    });
    var $fabric_table = $('#HDT_fabric_table'), $fabric_tds = $fabric_table.find('td'), old_fabric_id = null;
    $fabric_table.on('click', 'td[data-fabric-id]', function(e){
        e.preventDefault();
        var fabric_id = this.getAttribute('data-fabric-id');
        api.set('fabric_id', fabric_id);
        if(old_fabric_id != fabric_id){
            api_fabric.set('fabric_id', fabric_id);
            api_fabric.next();
            old_fabric_id = fabric_id;
        }
        $fabric_tds.removeClass('colblue');
        $(this).addClass('colblue');
        return false;
    });


    require.async('app/keyborad', function(keyborad){
        var k = new keyborad('#HDT-main', {template:'order',selector:'input[readonly]'});
        k.on('change', function(e, input, config){
            var value = input.value,
                product_id  = input.getAttribute('data-product-id'),
                color_id    = input.getAttribute('data-color-id'), 
                size_id     = input.getAttribute('data-size-id');
            if(config && config.is_byhandon){
                $(input).parents('tr').find('input').not('#HDT-keyborad-input').each(function(){
                    this.value=value;
                    var product_id  = this.getAttribute('data-product-id'), 
                        color_id    = this.getAttribute('data-color-id'), 
                        size_id     = this.getAttribute('data-size-id');
                    order.add(product_id, color_id, size_id, value);
                    OrderTable.set(product_id, color_id, size_id, value); 
                });
            }else{
                order.add(product_id, color_id, size_id, value);
                OrderTable.set(product_id, color_id, size_id, value);
            }
        });
        k.on('save', function(e){
            order.save();
        });
        order.on('save', function(){
            k.close();
        });
        $list.on('click', '.HDT-order-save', function(e){
            k.save();
        });
        
        $('body').on('click','#search_key',function(e){
        	k.close();
        })
    });

    
    $list.on('click', '.HDT-order-cancel', function(e){
        var table = $(this).parents('li').find('table'), 
            product_id  = this.getAttribute('data-product-id'),
            color_id = table.find('tr[data-color-id]').map(function(){
                return this.getAttribute('data-color-id');
            }).get().join(',');
        require.async('jquery/jquery.notify', function(n){
            n.confirm({title:"取消订单", text:"确定取消订单？"}, function(){
                order.remove(product_id, color_id);
                table.find("input").each(function(){
                    this.value = "";
                });
                OrderTable.clear(product_id);
            });
        });
    });

});