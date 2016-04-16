

define(['jquery', 'app/order', 'app/ordertable'], function(require, exports, module) {

    require.async(['rateit.ref'], function(){
        var $rate = $("#rateit1"), rateval = $rate.attr('data-rateit-value');
        $rate.bind('rated', function (event, value) {
            if(rateval == value) return false;
            require.async(['app/move'], function(Move){
                new Move('#HDT-photos-m', '#HDT_STORE_ICON', {});
            });
            $.post('/product/set_user_product', {product_id:product_id, status:true, rateval:value});
            rateval = value;
        });
        $rate.bind('reset', function(){
            $.post('/product/set_user_product', {product_id:product_id, status:0});
            rateval = 0;
        });
        $('#HDT_reset_store').on('click', function(e){
            $rate.rateit('value', 0).trigger('reset');
            return false;
        });
    });
    
    $(function(){
        var $big = $('#HDT-photos-big');
        if($big.length){
            require.async(['iscroll', 'photobox.ref'], function(){
                // $('body').photobox('a.photobox',{time:0});
                var $indicator  = $('#indicator'), $ind_li = $indicator.find('li'), is_click = false;
                // $big.find('img').glisse({speed: 200, changeSpeed: 200, effect:'fade', fullscreen: false});
                $indicator.find('li:first').addClass('hover');
                var myScroll    = new iScroll('HDT-photos-big', {
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
            });
        }
       
        $('.detailTopImgul li').eq(0).addClass('nowSelected');
        
        $('.detailTopImgul li').click(function(){
        	var imgUrl = $(this).find('img').attr('src');
        	if(imgUrl!=''){
        		imgUrl = imgUrl.replace('/thumb/75/','/thumb/210/');    
        		$('#HDT-photos-m').attr('src',imgUrl);
        	}   	   	
        	$(this).addClass('nowSelected').siblings().removeClass('nowSelected');
        })
    });


    var product_id = $('#HDT-product-id').val(), stock_api = null;
    if(product_id){
        require.async(['app/pager', 'app/lazy'], function(pager, lazy){
            var group   = new pager('/product/list/', {group_product_id:product_id}, {id:'#HDT-group-list'});
            var group1  = new pager('/product/grouplist/', {product_id:product_id,type_id:3}, {id:'#HDT-group-list1'});
            var display = new pager('/product/displaylist/', {product_id:product_id}, {id:'#HDT-display-list'});
            new lazy('#HDT-group-list', function(){group.next()}, {delay:100, top:100, time:1});
            new lazy('#HDT-group-list1', function(){group1.next()}, {delay:100, top:100, time:1});
            new lazy('#HDT-display-list', function(){display.next()}, {delay:100, top:100, time:1});

            var master_string = '#HDT-master-orderlist', $master = $(master_string);
            if($master.length){
                var master   = new pager('/orderlist/masterlist/', {product_id:product_id}, {id:master_string,autorun:true});
                var viewall  = function(e){
                    e.preventDefault();
                    $master.off('click', '.HDT_VIEWALL', viewall);
                    $.get('/orderlist/masterlistuser', {product_id:product_id}, function(html){
                        var $html = $(html).appendTo($master);
                        $master.on('click', '.HDT_VIEWALL', function(){
                            e.preventDefault();
                            $html.toggle();
                            return false;
                        });
                    }, 'html');
                    return false;
                };
                $master.on('click', '.HDT_VIEWALL', viewall);
            }
            var stock_string = "#HDT-stock", $stock = $(stock_string);
            if($stock.length){
                stock_api = new pager("/product/stocktable", {product_id:product_id}, {id:stock_string,autorun:true});
                order.on('save', function(){
                    setTimeout(function(){
                        stock_api.reload();
                    }, 500);
                })
            }
        });
    }
    

    var OrderTable  = require('app/ordertable');
    var $order  = $('#HDT-order-table'), order = require('app/order');

    $('#HDT-order-cancel').on('click', function(e){
        require.async('jquery/jquery.notify', function(n){
            n.confirm({title:"取消订单", text:"确定删除此订单?"}, function(){
                $order.find('input').each(function(){this.value="";});
                order.reset();
                order.remove(product_id);
                OrderTable.clear(product_id);
            });
        });
    });

    order.initOrderList(product_id, function(json){
        var list = json.list, len = list.length;
        if(len){
            for(var i = 0; i < len; i++){
                var t = $order.find('input[data-color-id='+list[i].product_color_id+'][data-size-id='+list[i].product_size_id+']');
                t.val(list[i].num);
            }
        } 

        new OrderTable($('#HDT-order-table')[0], product_id);
    });

    require.async(['app/keyborad'], function(keyborad){
        var k = new keyborad('#HDT-order-table', {template:'order'});
        k.on('change', function(e, input, config){
            var value = input.value,
                product_id  = input.getAttribute('data-product-id'),
                color_id    = input.getAttribute('data-color-id'), 
                size_id     = input.getAttribute('data-size-id');
            if(config && config.is_byhandon){
                $(input).parents('tr').find('input').not('#HDT-keyborad-input').each(function(){
                    var nv = value;
                    this.value=nv;
                    var product_id  = this.getAttribute('data-product-id'), 
                        color_id    = this.getAttribute('data-color-id'), 
                        size_id     = this.getAttribute('data-size-id');
                    order.add(product_id, color_id, size_id, nv);
                    OrderTable.set(product_id, color_id, size_id, nv);
                });
            }else{
                order.add(product_id, color_id, size_id, value);
                OrderTable.set(product_id, color_id, size_id, value);
            }
        });
        k.on('save', function(e){
            var canSave = OrderTable.canSave();
            if(canSave.error){
                 require.async('jquery/jquery.notify', function(n){
                    var message = canSave.message;
                    n.warn({title:"提示", text:message}, {expires:2000});
                });
            }else{
                order.save();
            }
        });
        order.on('save', function(){
            k.close();
        });

        $('#HDT-order-save').on('click', function(e){
            k.save();
        });
        
        $('body').on('click','#search_key',function(e){
        	k.close();
        })
    });

    $('#HDT-product-last,#HDT-product-next').on('click', function(e){
        var bianhao = this.getAttribute('data-bianhao'), f = this.getAttribute('data-f'), data = {bianhao:bianhao, f:f};
        $.get("/product/get_product_id_by_bianhao", data, function(json){
            if(json.product && json.product.id){
                location.href = "/index/detail/" + json.product.id;
            }else{
                require.async('jquery/jquery.notify', function(n){
                    var message = json.message || "到最后";
                    n.message({title:"提示", text:message}, {expires:2000});
                });
            }
        }, 'json');
    });

    
	$('#HDT-comment-save').on('click', function(e){
		var product_comment = $('#product_comment').val();
		 $.post('/product/set_comment', {product_id:product_id, product_comment:product_comment},
		 	function (data, status){
		 		var flag = data.flag;
		 		require.async('jquery/jquery.notify', function(n){
		 			flag === false ?
                	n.warn({title:"提示", text:"您的建议保存失败！"}, {}) :
                	n.message({title:"提示", text:"您的建议保存成功！"}, {expires:2000});
                });
		 	}
		 );
    });
});

