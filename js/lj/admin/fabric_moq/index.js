

define(['jquery'], function(require, exports, module) {
    $('#HDT-moq-list').on('change', '.HDT-moq', function(e){
        var fabric_id = this.getAttribute('data-fabric-id'),
            color_id = this.getAttribute('data-color-id'),
            val = this.value;
        $.post('/fabric_moq/edit', {fabric_id:fabric_id,color_id:color_id,val:val}, function(json){
            require.async('jquery/jquery.notify', function(n){
                var message = json.error ? json.errmsg : '设置成功';
                n.message({title:'提示',text:message}, {expires:2000});
            });
        }, 'json');
    });

});