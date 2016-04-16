
define(['iscroll'], function(require, exports, module) {
    var myScroll = new iScroll('wrapper', {
        // zoom: true,
        useTransform: false,
        onBeforeScrollStart: function (e) {
            var target = e.target; 
            while (target.nodeType != 1) target = target.parentNode; 
            if (target.tagName != 'SELECT' && target.tagName != 'INPUT' && target.tagName != 'TEXTAREA') e.preventDefault(); 
        }
    });

    myScroll.on = function(key, func){
        key = "on" + key;
        this.options[key]   = func;
    };

    document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);
    return myScroll;
});