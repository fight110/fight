

define(['jquery'], function(require, exports, module) {
    var $search = $('#HDT-search-moq'), $searchlist = $('#HDT-search-list'), id = $search[0].getAttribute('data-id'), $moqlist = $('#HDT-moq-list');
    $search.on('change', function(e){
        var query = {};
        $search.find('input').each(function(){
            query[this.name] = this.value;
        });
        query.filter = $moqlist.find("[data-product-id]").map(function(){return this.getAttribute('data-product-id')}).get().join(',');
        $.post('/moq/product_list', query, function(html){
            $searchlist.html(html);
        }, 'html');
    });
    

    var moqlist = {}, $save = $('#HDT-save');
    $('#HDT-main').on('change', 'input.HDT-moq', function(e){
        var target = e.currentTarget, parent = $(target).parent(), product_id = parent[0].getAttribute('data-product-id'), num = target.value;
        moqlist[product_id] = num; 
    });

    $save.on('click', function(e){
        var i = 0;
        for(var k in moqlist){
            setTimeout((function(product_id, num){
                return function(){
                    $.post('/moq/set', {keyword_id:id, product_id:product_id, minimum:num}, function(){}, 'json');
                }
            })(k, moqlist[k]), 150 * i++ );
        }
        if(i){
            var message = "设置成功";
            require.async('jquery/jquery.notify', function(n){
                n.message({title:"提示", text:message}, {expires:2000});
            });
        }
        moqlist = {};
    });

});