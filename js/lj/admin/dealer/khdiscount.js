

define(['jquery'], function(require, exports, module) {
    var tmpl = $('#tmpl-edit').html();

    $('body').on('click', '.HDT-edit', function(e){
    	var target = e.currentTarget, id = target.getAttribute('data-id'), kuanhao = target.getAttribute('data-name'), khdiscount = target.getAttribute('data-discount');
        require.async(['jquery/jquery.ui'], function(ui){
            var dialog = $('<div>').html(tmpl).dialog({width:400, autoOpen:true});
            dialog.find("input[name=id]").val(id);
            dialog.find("input[name=kuanhao]").val(kuanhao);
            dialog.find("input[name=discount]").val(khdiscount);
        });
    });
});