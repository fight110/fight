

define(['jquery', 'iscroll'], function(require, exports, module) {
    $(function(){
        var $indicator  = $('#indicator'), $big = $('#HDT-photos-big'), $ind_li = $indicator.find('li'), is_click = false;
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
                myScroll.scrollToElement(target);
            }
        });

    });


    var product_id = $('body').find('[name=product_id]').val();
    if(product_id){
        require.async(['app/pager', 'app/lazy'], function(pager, lazy){
            var group   = new pager('/product/list3/', {group_product_id:product_id,limit:12}, {id:'#HDT-group-list'});
            new lazy('#HDT-group-list', function(){group.next()}, {delay:100, top:100, time:1});
        });
    }
    
    var $ordertable = $('#HDT-order-table'), height = $ordertable.height();
    if(height>220){
        $ordertable.parent().css({'overflow-y':'scroll','height':200});
    }
    $ordertable.on('change', 'input', function(e){
        var target = e.currentTarget, 
            user_id     = target.getAttribute('data-user-id'),
            color_id    = target.getAttribute('data-color-id'),
            size_id     = target.getAttribute('data-size-id'),
            num         = target.value,
            callback    = function(){
                $.post('/orderlist/zongdaiedit', {user_id:user_id,product_id:product_id,color_id:color_id,size_id:size_id,num:num}, function(json){
                    var message = json.valid ? "订单修改成功" : json.message;
                    require.async('jquery/jquery.notify', function(n){
                        n.message({title:"订单修改",text:message}, {expires:2000});
                    });
                    if(num == 0){
                        $(target).parents('tr').remove();
                    }
                }, 'json');
            };
        if(num == 0){
            require.async('jquery/jquery.notify', function(n){
                n.confirm({title:"删除订单", text:"确定要删除此条订单？"}, callback);
            });
        }else{
            callback.call(this);
        }
    });
    $ordertable.on('click', '.HDT-delete', function(e){
        var target = e.currentTarget, parent = $(target).parents('tr'), input = parent.find('input').get(0),
            user_id     = input.getAttribute('data-user-id'),
            color_id    = input.getAttribute('data-color-id'),
            size_id     = input.getAttribute('data-size-id'),
            callback    = function(){
                $.post('/orderlist/zongdaiedit', {user_id:user_id,product_id:product_id,color_id:color_id,size_id:size_id,num:0}, function(json){
                    var message = json.valid ? "删除订单成功" : json.message;
                    require.async('jquery/jquery.notify', function(n){
                        n.message({title:"订单删除",text:message}, {expires:2000});
                    });
                    $(target).parents('tr').remove();
                }, 'json');
            };
        require.async('jquery/jquery.notify', function(n){
            n.confirm({title:"删除订单", text:"确定要删除此条订单？"}, callback);
        });
        return false;
    });



});

