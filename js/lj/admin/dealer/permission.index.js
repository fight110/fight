

define(['jquery'], function(require, exports, module) {
    $('.table01').on('change', 'input', function() {
        var user_id = this.getAttribute('data-user-id'),
            brand_id = this.getAttribute('data-brand-id'),
            name    = this.name,
            value   = this.value;
        $.post("/dealer/user_exp/" + user_id, {brand_id:brand_id, name:name, value:value}, function(json) {
            if(json.error) {
                alert("保存失败");
            }
        }, 'json');
    });
});