

define(['jquery', 'app/order', 'iscroll', 'photobox.ref', 'app/ordertable'], function(require, exports, module) {
    $(function(){
        $('body').photobox('a.photobox',{time:0});
        require.async(['jquery/jquery.scrollfollow'], function(){
            // $(".scroll-picbox").scrollFollow({});
        });

        require.async(['rateit.ref'], function(){
            var $rate = $(".rateit");
            $rate.bind('rated', function (event, value) {
                var product_id = this.getAttribute('data-product-id'), rateval = this.getAttribute('data-rateval'), num = this.getAttribute('data-show-num');
                if(rateval === undefined){
                    rateval = this.getAttribute('data-rateit-value');
                }
                if(rateval == value) return false;
                require.async(['app/move'], function(Move){
                    new Move('#HDT-photos-big' + num, '#HDT_STORE_ICON', {});
                });
                $.post('/product/set_user_product', {product_id:product_id, status:true, rateval:value});
                this.setAttribute('data-rateval', value);
            });
            $rate.bind('reset', function(){
                var product_id = this.getAttribute('data-product-id');
                $.post('/product/set_user_product', {product_id:product_id, status:0});
                this.setAttribute('data-rateval', 0);
            });
            $('.HDT_reset_store').on('click', function(e){
                var product_id = this.getAttribute('data-product-id');
                $rate.filter('[data-product-id='+product_id+']').rateit('value', 0).trigger('reset');
                return false;
            });
        });

        var $list_indicator = $('.indicator'), $list_big= $('.HDT-photos-big');
        for(var i = 0, len = $list_indicator.length; i < len; i++){
            (function(indicator, big){
                var $indicator = $(indicator), $big = $(big), $ind_li = $indicator.find('li'), is_click = false, photo_id = big.id;
                $indicator.find('li:first').addClass('hover');
                var myScroll    = new iScroll(photo_id, {
                    snap: 'li',
                    momentum: false,
                    hScrollbar: false,
                    vScrollbar: false,
                    onScrollEnd: function () {
                        if(is_click){
                            is_click = false;
                        }else{
                            $indicator.find('li.hover').removeClass('hover');
                            $indicator.find('li:nth-child(' + (this.currPageX+1) + ')').addClass('hover');
                        }
                    }
                });
                $indicator.on('click', 'li', function(e){
                    $indicator.find('li.hover').removeClass('hover');
                    this.className = 'hover';
                    for(var i = 0, len = $ind_li.length; i < len; i++){
                        if($ind_li[i] == this){
                            break;
                        }
                    }
                    var target = $big.find('li').eq(i)[0];
                    if(target){
                        is_click = true;
                        myScroll.scrollToElement(target, 1000);
                    }
                });
            })($list_indicator[i], $list_big[i]);
        }
    });


    var $order  = $('.HDT-order-table'), order = require('app/order'), OrderTable = require('app/ordertable');

    $('body').on('click', '.HDT-order-save', function(e){
        order.save();//this.getAttribute('data-product-id')
    });
    $('body').on('click', '.HDT-order-cancel', function(e){
        var table = $(this).parents('.HDT-list').find('table.HDT-order-table'),
            product_id  = this.getAttribute('data-product-id');
        require.async('jquery/jquery.notify', function(n){
            n.confirm({title:"取消订单", text:"确定取消订单？"}, function(){
                order.remove(product_id);
                table.find("input").each(function(){
                    this.value = "";
                });
                OrderTable.clear(product_id);
            });
        });
    });

    var $input_show = $('#HDT-show-id'), show_id = $input_show.val();
    order.initShowOrderList(show_id, function(json){
        var list = json.list, len = list.length, num;
        if(len){
            for(var i = 0; i < len; i++){
                var t = $order.find('input[data-product-id='+list[i].product_id+'][data-color-id='+list[i].product_color_id+'][data-size-id='+list[i].product_size_id+']');
                num = list[i].num;
                t.val(num);
            }
            $('table.HDT-order-table').each(function(){
                new OrderTable(this, this.getAttribute('data-product-id'));
            });
        }
    });


    if(show_id){
        (function(){
            var $refresh_button = $('#refresh_button'), show_interval = $refresh_button.attr('data-show-interval');
            if(show_interval > 0){
                var time_show = show_interval * 1000, $ul = $('#HDT-show-ul'), $menu_right = $('#HDT-menu-right-top').find('.on a'), current_show_id = show_id, is_live = 0, room_id = $input_show.attr('data-room-id');
                setTimeout(function(){
                    var callback = arguments.callee;
                    $.get('/show/get_show_id', {current_show_id:current_show_id, room_id:room_id}, function(json){
                        if(json.show_id && json.show_id != show_id && json.list){
                            current_show_id = json.show_id;
                            if($ul.find('li[data-id='+current_show_id+']').length == 0){
                                $ul.find('li.cur').removeClass('cur');
                                if(is_live++ == 0){
                                    $menu_right.append('<img src="/images/live.gif">');
                                }
                                var html = "<li class='cur' data-id='"+current_show_id+"'>";
                                for(var i = 0, list = json.list, len = list.length; i < len; i++){
                                    html += '<a href="/dealer1/show/' + current_show_id + '#p' + list[i].bianhao + '"><div class="rela">'
                                        + '<img src="/thumb/75/' + list[i].defaultimage + '"/>'
                                        + '<div class="rela-bg">编号:<b>' +list[i].bianhao+ '</b></div>'
                                        +'</div></a>';
                                }
                                html += "</li>";
                                var $html = $(html).prependTo($ul), width = $html.width();
                                $html.css('width', 0).animate({width:width}, i * 500);
                            }
                        }
                        setTimeout(callback, time_show);
                    }, 'json');
                }, time_show);
            }

        })();
    }

    require.async(['app/keyborad'], function(keyborad){
        var k = new keyborad('.HDT-order-table', {template:'order'});
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
        
        $('body').on('click','#search_key',function(e){
        	k.close();
        })
    });




});

