
define(['jquery', 'jquery/jquery.ui.widget', 'jquery/jquery.iframe-transport'], function(require, exports, module) {
    var list = [], flag = false, 
        runCallback = function(){
            var len = list.length;
            if(flag && len){
                var newlist = list;
                list = [];
                for(var i = 0; i < len; i++){
                    newlist[i]();
                }
            }
        },
        FileUpload = function(callback){
            list.push(callback);
            runCallback();
        };
    require.async('jquery/jquery.fileupload', function(){
        flag = true;
        runCallback();
    });
    return FileUpload;
});