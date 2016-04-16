

define(['jquery', 'app/pager'], function(require, exports, module) {
    var pager   = require('app/pager'), $form = $('#HDT-main'), $menu = $('#select_menu');

    api = new pager('/dealer1/select_proportion_list', {size_group_id:$menu.find('[data-size-group-id]:first').attr('data-size-group-id')}, {autorun:true});
    $form.on('submit', function(){
        var list = [], total = 0, table = $form.find('table'), num = table.attr('data-num'), size_group_id = table.attr('data-size-group-id');
        $form.find('table input').each(function(){
            total += this.value>>0;
            list.push(this.value>>0);
        });
        if(num > 0 && total != num) {
            require.async('jquery/jquery.notify', function(n){
                n.message({title:"提示",text:"配比总数必须为" + num}, {expires:3000});
            });
        }else{
            $.post('/dealer1/set_user_proportion', {proportion:list.join(':'), size_group_id:size_group_id}, function(json){
                api.reload();
            }, 'json');
        }
        return false;
    });

    $menu.on('click', 'a', function(e){
        var size_group_id = this.getAttribute('data-size-group-id');
        api.set('size_group_id', size_group_id);
        $menu.find('a').removeClass('on');
        $(this).addClass('on');
    });
   
});