

define(['jquery'], function(require, exports, module) {
	require.async(['jquery/jquery.ui', 'jquery/jquery.tmpl'], function(){
		setTimeout(function(){
			var $notice = $('#HDT-company-notice'), html = $notice.html(), title = $notice.attr('title');
			$('<div>').append(html).appendTo('body').dialog({
				title 	: title,
				// modal	: true,
				width	:'80%', 
				height	: 600,
				buttons	: [ { text: "同意", click: function() { $( this ).dialog( "close" ); } } ],
				close 	: function(event, ui){
					$.post('/dealer1/agree_notice');
				}
			});
		}, 0);
	});
});