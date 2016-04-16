
/*
*/
define(['jquery'], function(require, exports, module) {
    var iframes = {};
    var open = function(url, options){
        options = $.extend({name:"default"}, options || {});
        var Klass = function(url, options){
            this.url = url;
            this.options = options;
            this.find('iframe').attr('src', url);
        };
        Klass.prototype = get_iframe(options.name);
        return new Klass(url, options);
    };
    function get_iframe (name) {
        var iframe = iframes[name];
        if(!iframe){
            var html = "<div class='main_con' style='position:fixed;top:100px;width:100%;height:80%;'><iframe style='width:100%;height:100%;'></div>";
            iframe = $(html).appendTo('body');
        }
        return iframe;
    }
    open("/dealer1/detail/1");
    return open;
});