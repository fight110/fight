

define(function(require, exports, module) {


function fileQueueError(file, errorCode, message) {
		
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
	if (numFilesQueued > 0) {
		notify('提示', '上传中');
		this.startUpload();
	}
}

function uploadProgress(file, bytesLoaded) {

}

function uploadSuccess(file, serverData) {
    var json = $.parseJSON(serverData);
    if(json.valid===true){
        notify("上传成功", "<img src='/img/"+json.filename+"' width=150/>");
    }else {
        notify("错误提示", json.message);
    }
}

function uploadComplete(file) {
	if (this.getStats().files_queued > 0) {
		this.startUpload();
	}
}

function uploadError(file, errorCode, message) {
	
}


function notify(title, message){
    require.async('jquery/jquery.notify', function(n){
        n.message({title:title,text:message}, {expires:2000});
    });
}
	return {
		fileQueueError : fileQueueError,
		fileDialogComplete : fileDialogComplete,
		uploadProgress : uploadProgress,
		uploadError : uploadError,
		uploadSuccess : uploadSuccess,
		uploadComplete : uploadComplete
	};
});
