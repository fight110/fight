

define(['jquery'], function(require, exports, module) {
    require.async(['app/selects'], function(Selects){
        var select = new Selects('/location/json', {dataType:'json'});
    });

    var rule_id     = $('input[name=rule_id]').val(), field = $('input[name=field]').val();
    $('#HDT-main').on('click', ':checkbox', function(e){
        var user_id = this.value, checked = this.checked ? 1 : 0, data = {
            rule_id     : rule_id,
            user_id     : user_id,
            field       : field,
            checked     : checked
        };
        $.post('/rule/user_set/', data, function(json){
            var message     = json.valid ? "设置成功" : json.message;
            require.async('jquery/jquery.notify', function(n){
                n.message({title:'提示', text:message}, {expires:1000});
            });
        }, 'json');
    });

});