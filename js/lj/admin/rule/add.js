

define(['jquery'], function(require, exports, module) {
    
    $('#HDT-main').on('keyup', 'td input', function(e){
        var value = this.value, error = "", m = $(this).parents('tr').find('td').last();
        if(!$.isNumeric(value)){
            error = "请填写0-100之间的数字";
        }else if(value > 100 || value < 0){
            error = "请填写0-100之间的数字";
        }
        m.html(error);
    });

});