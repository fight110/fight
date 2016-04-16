

define(['jquery'], function(require, exports, module) {
    $("#show_all").on("click",function(){
        var href = location.href;
        var index = href.indexOf('?');
        href = href.substr(0,index) + '?show_all='+ (this.checked ? 1 : 0);;
        location.replace(href);
    })
});