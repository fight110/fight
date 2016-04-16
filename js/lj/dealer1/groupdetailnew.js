

define(['jquery', 'app/lazy', 'app/pager',  'app/order', 'app/general', 'app/ordertable'], function(require, exports, module) {
    //'iscroll','glisse.ref',
    // $(function(){
    //     var $big = $('#HDT-photos-big');
    //     $big.find('img').glisse({speed: 200, changeSpeed: 200, effect:'fade', fullscreen: false});

    //     var myScroll    = new iScroll('HDT-photos-big', {
    //         snap: 'li',
    //         momentum: false,
    //         hScrollbar: false,
    //         vScrollbar: false
    //     });
    // });
    var General = require('app/general'), OrderTable = require('app/ordertable'), general = new General;

    var pager = require('app/pager'), lazy = require('app/lazy'), api, order = require('app/order'),
        $list = $('#HDT-group-list'), group_id = $list[0].getAttribute('data-group-id');
    
	var display_id = $list[0].getAttribute('data-display-id');
		       
    if(group_id){
        api = new pager('/product/groupdetaillist?type=new', {group_id:group_id}, {id:'#HDT-group-list', autorun:true, aftercallback:function(){
            order.initGroupOrderList(group_id, function(json){
                var list = json.list, len = list.length, num;
                if(len){
                    for(var i = 0; i < len; i++){
                        var row = list[i], product_id = row.product_id, color_id = row.product_color_id, size_id = row.product_size_id;
                        var t = $list.find('input[data-product-id='+product_id+'][data-color-id='+color_id+'][data-size-id='+size_id+']');
                        num = list[i].num;
                        t.val(num);
                        general.add(product_id, num);
                    }
                }

                $('#HDT-group-list').find('table.HDT-keyborad').each(function(){
                    new OrderTable(this, this.getAttribute('data-product-id'), function(){
                        general.set(this.product_id, this.total);
                        var html = this.total ? "订数:" + this.total : "";
                        $('#general' + this.product_id).find('em').html(html);
                    });
                });
                require.async('app/keyborad', function(keyborad){           	
                    var k = new keyborad('.HDT-keyborad', {template:'order'});
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
                        }
                        order.add(product_id, color_id, size_id, value);
                        OrderTable.set(product_id, color_id, size_id, value);
                    });
                    k.on('save', function(e){
                        var canSave = OrderTable.canSave();
                        if(canSave.error){
                             require.async('jquery/jquery.notify', function(n){
                                var message = canSave.message;
                                n.warn({title:"提示", text:message}, {expires:2000});
                            });
                        }else{
                        	if(display_id>0&&group_id>0){
                        		order.setDisplayGroup(display_id,group_id);
                        	}
                            order.save();
                        }
                    });
                    order.on('save', function(){
                        k.close();
                    });
                    $list.on('click', '.HDT-order-save', function(e){
                        k.save();//this.getAttribute('data-product-id')
                    });
                    $('body').on('click','#search_key',function(e){
                    	k.close();
                    })
                });
            });

            require.async(['rateit.ref'], function(){
                var $rate = $('.rateit_new').rateit({}).removeClass('rateit_new');
                $rate.bind('rated', function (event, value) {
                    var product_id = this.getAttribute('data-product-id');
                    if(product_id){
                        require.async(['app/move'], function(Move){
                            new Move('#HDT_GROUP_LIST_IMG_' + product_id, '#HDT_STORE_ICON', {});
                        });
                        $.post('/product/set_user_product', {product_id:product_id, status:true, rateval:value});
                    }
                });
                $rate.bind('reset', function(){
                    var product_id  = this.getAttribute('data-product-id');
                    if(product_id){
                        $.post('/product/set_user_product', {product_id:product_id, status:0});
                    }
                });
            });
        }});
        // new lazy('.foot', function(){api.next()}, {delay:100, top:100});
    }


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

    $('#HDT-last,#HDT-next').on('click', function(e){
        var gid = this.getAttribute('data-gid'), f = this.getAttribute('data-f'), did = this.getAttribute('data-did'), data = {gid:gid, did:did, f:f};
        $.get("/dealer1/get_group_id_by_id_new", data, function(json){
            if(json.group && json.group.group_id && json.group.display_id){
                location.href = "/dealer1/groupdetailnew/" + json.group.group_id+'?did='+json.group.display_id;
            }else{
                require.async('jquery/jquery.notify', function(n){
                    var message = json.message || "到最后";
                    n.message({title:"提示", text:message}, {expires:2000});
                });
            }
        }, 'json');
    });

});
