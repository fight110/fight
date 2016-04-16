

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'),
        $list = $('#HDT-main'), q = $('#HDT-hidden-q').val(), api = null, $menu = $('#HDT-select-menu'), tType = $list.attr('data-tType'), orderType = $list.attr('data-orderType'), $search = $('#HDT-FORM input');

    var data = {q:q, tType:tType, orderType:orderType};
    $menu.find('select').each(function(){
        data[this.name] = this.value;
    });
    data['q']  = $search.val();
    api = new pager('/product/list', data, {autorun:true,message:"松开刷新"});
    if($('#HDT-rank-distribute').length){
	   api_rank = new pager('/analysis/rank_distribute_table', {} , {autorun:true,id:'#HDT-rank-distribute'})
    }
    new lazy('.foot', function(){api.next()}, {delay:200, top:200});

    $menu.on('change', 'select', function(e){
        $('html,body').animate({scrollTop: '0px'}, 500);
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
        if($('#HDT-rank-distribute').length){
            api_rank.set(target.name, target.value);
        }
    });
    $search.on('keyup', function(){
        api.set('q', this.value);
    });
    $search[0].onchange = function(){};

    var message = $('#HDT-message').val();
    if(message){
        require.async('jquery/jquery.notify', function(n){
            n.message({title:"提示", text:message}, {expires:3000});
        });
    }

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
    });

    require.async(['jquery/jquery.pin/jquery.pin.min'], function(){
        $(".pinned").pin();
    });
    var time = null;
    $('body').on('lastProduct',function(){
        if(null===time){
            api.next();
            time = 10000;
        }
        setTimeout(function(){
            time=null;
        },10000);
    });
});