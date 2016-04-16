

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/ad/product_color_moq_table', {}, {autorun:true,message:"下拉刷新"});

    new lazy('.foot', function(){api.next()}, {delay:100, top:0});

    $('#HDT-select-menu').on('change', 'select', function(e){
        var name = this.name, value = this.value;
        api.set(name,value);
    });
});