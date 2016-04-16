

define(['jquery', 'app/pager'], function(require, exports, module) {

    var pager = require('app/pager'), api, $list = $('#HDT-group-list'), group_id = $list[0].getAttribute('data-group-id');

    if(group_id){
        api = new pager('/group/member_list', {group_id:group_id}, {id:'#HDT-group-list', autorun:true});
    }

    $('#HDT-last,#HDT-next').on('click', function(e){
        var bianhao = this.getAttribute('data-bianhao'), f = this.getAttribute('data-f'), data = {bianhao:bianhao, f:f};
        $.get("/dealer1/get_group_id_by_bianhao", data, function(json){
            if(json.group && json.group.id){
                location.href = "/dealer1/groupdetail/" + json.group.id;
            }else{
                require.async('jquery/jquery.notify', function(n){
                    var message = json.message || "到最后";
                    n.message({title:"提示", text:message}, {expires:2000});
                });
            }
        }, 'json');
    });

    $('body').on('productOrderChanged', function(e, product){
        var target = product.target, ordertable = product.ordertable, $target = $(target);
        for(var color_id in ordertable.data) {
            var num = ordertable.get_color_count(color_id);
            console.log('tr[data-color-id='+color_id+'] .num');
            $target.find('tr[data-color-id='+color_id+'] .num').html(num);
        }
    });

});
