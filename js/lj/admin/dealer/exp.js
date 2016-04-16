

define(['jquery'], function(require, exports, module) {
    var $main = $('#HDT-main'), user_id = $main.attr('data-user-id'), field = $main.attr('data-field');
    $main.on('change', 'input', function(){
        var name    = this.name, value = this.value, keyword_id = this.getAttribute('data-keyword-id'), data = {user_id:user_id,field:field, keyword_id:keyword_id};
        data[name]  = value;
        $.post('/dealer/set_exp_complete', data, function(json){
            require.async('jquery/jquery.notify', function(n){
                var message = json.message;
                n.message({title:'提示',text:message}, {expires:2000});
            });
        }, 'json');
    });
});