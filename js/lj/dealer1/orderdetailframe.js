
define(['jquery'], function(require, exports, module) {
	(function($){
        $.AutoiFrame = function(_o){
            var _o_=new Function("return "+_o)(), $o = $('#'+_o), autoiframe = function(){
                var ow = _o_.contentWindow ? _o_.contentWindow : _o_;
                var height = ow.document.body.scrollHeight;
                $o.height(height);
            };
            autoiframe();
            $o.load(autoiframe);
        }
    })(jQuery);

    $(function(){
        $.AutoiFrame('iFrame1');
    });
});