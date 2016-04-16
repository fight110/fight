

define(['jquery'], function(require, exports, module) {
    var $sortable = $('#sortable'), $savelist = $('#SAVELIST');
    require.async('jquery/jquery.ui', function(ui){
        $sortable.sortable({});
        $sortable.disableSelection();
    });

    $savelist.on('click', function(e){
        var i = 1, list = [], t = $('#FactoryTable').val();
        $sortable.find('tr').each(function(){
            var id      = this.getAttribute('data-id'), rank = i++;
            list.push(id+":"+rank);
        });
        $.post('/keyword/setrank/', {t:t,list:list.join(',')}, function(json){
            var message     = json.valid ? "保存成功" : json.message;
            require.async('jquery/jquery.notify', function(n){
                n.message({title:"提示", text:message}, {expires:2000});
            });
            window.location.reload(true);
        }, 'json');
    });
});