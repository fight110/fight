

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {

    var lazy = require('app/lazy'), pager = require('app/pager'), api, $list = $('#HDT-display-list'), display_id = $list[0].getAttribute('data-display-id');

    if(display_id){
        api = new pager('/product/list', {display_id:display_id}, {id:'#HDT-display-list',autorun:true, aftercallback:function(){
            $list.find('.fancyboxBook').each(function(){
                var product_id = this.getAttribute('data-product-id');
                if(product_id) {
                    refresh(product_id, this);
                }
            });
        }});

        // new lazy('.foot', function(){api.next();}, {delay:200, top:200});

        $('.fill_form').on('click', function(e){
            return confirm(this.getAttribute("data-confirm"));
        });
    }

    function refresh (product_id, target) {
        var $general = $('#general' + product_id), num = $(target).find('.amount em').html();
        if(num>>0) {
            $general.addClass('selected');
            $general.find('em').html(num);
        }else{
            $general.removeClass('selected');
            $general.find('em').html('');
        }
    }
   
    $('#HDT-last,#HDT-next').on('click', function(e){
        var bianhao = this.getAttribute('data-bianhao'), f = this.getAttribute('data-f'), data = {bianhao:bianhao, f:f};
        $.get("/dealer1/get_display_id_by_bianhao", data, function(json){
            if(json.display && json.display.id){
                location.href = "/dealer1/displaydetail/" + json.display.id;
            }else{
                require.async('jquery/jquery.notify', function(n){
                    var message = json.message || "到最后";
                    n.message({title:"提示", text:message}, {expires:2000});
                });
            }
        }, 'json');
    });

    $('body').on('productOrderChanged', function(e, product){
        var target = product.target, total = product.ordertable.total, price = target.getAttribute('data-price'), amount = price * total,
            $target = $(target);
        if(amount > 10000) {
            amount = fomatFloat(amount / 10000, 1) + '万';
        }
        $target.find('.amount em').html(total);
        $target.find('.mode em').html(amount);
        if(total) {
            $target.find('.unorder').addClass('on');
        }else{
            $target.find('.unorder').removeClass('on');
        }
        refresh(product.product_id, target);
    });

});
