

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


    var product_id = $('input[name=product_id]').val();

    if(product_id){
        require.async(['app/pager', 'app/lazy'], function(pager, lazy){
            var group   = new pager('/product/list2/', {group_product_id:product_id,limit:12}, {id:'#HDT-group-list'});
            new lazy('#HDT-group-list', function(){group.next()}, {delay:100, top:100, time:1});
        });
    }
    
    var $ordertable = $('#HDT-order-table'), height = $ordertable.height();
    if(height>220){
        $ordertable.parent().css({'overflow-y':'scroll','height':200});
    }

});

