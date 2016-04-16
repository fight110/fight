
define(['app/swfupload'], function(require, exports, module) {
    function getCookie(c_name){
        if (document.cookie.length>0){
            c_start=document.cookie.indexOf(c_name + "=");
            if (c_start!=-1){ 
                c_start=c_start + c_name.length+1;
                c_end=document.cookie.indexOf(";",c_start);
                if (c_end==-1){
                    c_end=document.cookie.length;
                }
                return unescape(document.cookie.substring(c_start,c_end))
            }
        }
        return "";
    }
    var SWFUpload   = require('app/swfupload'), handlers = require('app/handlers'), SID = getCookie('SID'), settings    = {
        flash_url : "/js/app/swfupload.swf",
        upload_url: "/upload/",
        post_params: {'SID':SID},
        debug: false,
        prevent_swf_caching:false,
        file_size_limit : "50 MB",   // 3MB
        file_types : "*.*",
        file_types_description : "JPG Images",
        file_upload_limit : 0,

        // Button settings
        button_image_url: "http://my.20fd.com/js/app/swfuploadbutton.png",
        button_placeholder_id: "spanButtonPlaceHolder",
        button_text_left_padding: 12,
        button_text_top_padding: 3,
        button_width: 180,
        button_height: 18,
        button_text : '<span class="button">Select Images</span>',
        button_text_style : '.button { font-family: Helvetica, Arial, sans-serif; font-size: 12pt; } .buttonSmall { font-size: 10pt; }',
        button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
        button_cursor: SWFUpload.CURSOR.HAND,

        file_dialog_complete_handler : fileDialogComplete,
        upload_progress_handler : uploadProgress,
        upload_error_handler : uploadError,
        upload_success_handler : uploadSuccess,
        upload_complete_handler : uploadComplete
    },  FileProgress    = function(){
        this.notify     = null;
        this.init();
    },  fileprogress = null, listen = $({});
    FileProgress.prototype.init     = function(){
        var that = this, notify = that.notify;
        if(notify === null){
            require.async('jquery/jquery.notify', function(n){
                that.notify  = n.progress({'text':'文件上传', 'progress':'0%'});
            });
        }
    };
    FileProgress.prototype.update   = function(progress){
        if(this.notify){
            this.notify.update(progress);
        }
    };

    function fileDialogComplete(numFilesSelected, numFilesQueued) {
        if (numFilesQueued > 0) {
            this.startUpload();
            if(fileprogress === null){
                fileprogress = new FileProgress;
            }
        }
    }
    function uploadSuccess(file, serverData) {
        var json = $.parseJSON(serverData);
        if(json.valid===true){
            fileprogress.update("100%...上传完成");
            notify("上传成功", "<img src='/thumb/210/"+json.filepath+"' width=150/>");
            listen.trigger('uploadSuccess.'+this.customSettings.target, json, this);
            setTimeout(function(){
                if(fileprogress && fileprogress.notify) fileprogress.notify.close();
                fileprogress = null;
            }, 2000);
        }else {
            notify("错误提示", json.message);
        }
    }

    function uploadProgress(file, bytesLoaded, bytesTotal) {
        if(fileprogress){
            var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
            fileprogress.update(percent + "%...");
        }
    }

    function uploadComplete(file) {
        if (this.getStats().files_queued > 0) {
            this.startUpload();
        }
    }
    function uploadError(){}

    function notify(title, message){
        require.async('jquery/jquery.notify', function(n){
            n.message({title:title,text:message}, {expires:2000});
        });
    }

    
    return function(options){
        options     = $.extend({}, settings, options);
        var upload  = new SWFUpload(options);
        upload.listen = listen;
        return upload;
    }
    

});