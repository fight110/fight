
/*
new lazy('.foot', function(){api.next()}, {delay:100, top:100});
*/
define(['jquery'], function(require, exports, module) {
    var $window = $(window), lazy    = function(selector, callback, options){
        this.el         = $(selector);
        this.callback   = callback;
        this.options    = $.extend({delay:100, top:0, time:false}, options);
        this.isscroll   = false;
        var that    = this, bind    = function(e){
            var delay = that.options.delay;
            if(that.isscroll == false){
                that.isscroll = true;
                setTimeout(function(){
                    that.check();
                }, delay);
            }
        };
        $window.on('scroll touchmove', bind);
        this.off    = function(){
            $window.off('scroll touchmove', bind);
        }
    };
    lazy.prototype.check    = function(){
        var wh = $window.height(), ws = $window.scrollTop(), position = this.el.position();
        if(position.top < wh + ws + this.options.top){
            var ret = this.callback.call(this);
            if(this.options.time > 0 && ret !== false){
                this.options.time--;
                if(this.options.time == 0){
                    this.off();
                }
            }
        }
        this.isscroll = false;
    };
    

    return lazy;
});