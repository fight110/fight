
/*
new lazy('.foot', function(){api.next()}, {delay:100, top:100});
*/
define(['jquery'], function(require, exports, module) {
    var Move = function(target, to, options){
        this.options    = $.extend({
            time        : 800, 
            run_time    : 20, 
            callback    : null,
            autohide    : true,
            css         : {
                zIndex      : 100,
                position    : 'absolute',
                border      : '',
                overflow    : 'hidden'
            }
        }, options || {});
        this.target     = $(target);
        this.to         = $(to);

        var css_target  = $.extend(this.target.offset(), {width : this.target.width(), height : this.target.height()}),
            css_to      = $.extend(this.to.offset(), {width : this.to.width(), height : this.to.height()}),
            run_time    = this.options.run_time,
            diff_width  = ( css_target.width    - css_to.width )    / run_time,
            diff_height = ( css_target.height   - css_to.height)    / run_time,
            diff_top    = ( css_target.top      - css_to.top)       / run_time,
            diff_left   = ( css_target.left     - css_to.left)      / run_time,
            callback    = this.options.callback,
            t           = this.options.time / run_time,
            run         = 0,
            that        = this;
        this.el = $('<div>').css(this.options.css).append(this.target.clone()).css(css_target).appendTo('body');
        setTimeout(function(){
            var f = arguments.callee;
            css_target.width    -= diff_width;
            css_target.height   -= diff_height;
            css_target.top      -= diff_top;
            css_target.left     -= diff_left;
            that.el.css(css_target);
            if(++run < run_time){
                setTimeout(f, t);
            }else{
                if(that.options.autohide == true){
                    that.el.remove();
                }
                callback && callback.call(that);
            }
        }, t);
    }
    
    return Move;
});