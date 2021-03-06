

define(['jquery', 'app/pager'], function(require, exports, module) {
    var $form = $('#form-main'), $search = $('#group-search'), 
        $list = $('#group-search-list'), $member = $('#group-member'), photos = null, pager = require('app/pager'), $imagelist = $('#imagelist');
    require.async('app/upload.jquery', function(FileUpload){
        FileUpload(function(){
            $('#defaultfileupload').fileupload({
                dataType: 'json',
                done: function (e, data) {
                    $.each(data.result.files, function (index, file) {
                        //var url = file.url, html = '<a class="black card"><div class="image"><input type="hidden" name="defaultimage" value="'+url+'"><img src="/thumb/210/'+url+'"></div></a>';
                        //$imagelist.html(html);
                        var url = file.url, html = '<div class="image"><input type="hidden" name="defaultimage" value="'+url+'"><img src="/thumb/210/'+url+'"></div>';
                        $('.defaultimage').html(html);
                    });
                }
            });
            $('#contrastfileupload').fileupload({
                dataType: 'json',
                done: function (e, data) {
                    $.each(data.result.files, function (index, file) {
                        var url = file.url, html = '<div class="image"><input type="hidden" name="contrast_image" value="'+url+'"><img src="/thumb/210/'+url+'"></div>';
                        $('.contrast_image').html(html);
                    });
                }
            });
            $('#backgroundfileupload').fileupload({
                dataType: 'json',
                done: function (e, data) {
                    $.each(data.result.files, function (index, file) {
                        var url = file.url, html = '<div class="image"><input type="hidden" name="background_image" value="'+url+'"><img src="/thumb/210/'+url+'"></div>';
                        $('.background_image').html(html);
                    });
                }
            });
            $('.fileupload').fileupload({
                dataType: 'json',
                done: function (e, data) {
                    var id = this.getAttribute('data-id'), 
                        kuanhao = this.getAttribute('data-product-kuanhao'), 
                        color = this.getAttribute('data-color') || '';
                    $.each(data.result.files, function (index, file) {
                        $.post('/group/set_image/', {id:id, image:file.url}, function(json){
                            var html = '<a class="card">'+
                                       '<div class="image"><img src="/thumb/75/' + file.url+'"></div>'+
                                       '<div class="extra content">' +
                                       '<div class="meta">' +
                                        kuanhao + 
                                       '<div class="right floated delete" data-id="'+id+'">删除</div>' +
                                       '<div class="right floated ">'+color+'</div></div></div></a>';
                            $('#newimagelist').append(html);
                        }, 'json');
                    });
                }
            });
        });
    });

    var search_callback = function(e){
        var query = {};
        $search.find('select,input').each(function(){
            query[this.name]    = this.value;
        });
        query.filter = $member.find('[data-product-id]').map(function(){return this.getAttribute('data-product-id')}).get().join(',');
        $.post('/group/list/', query, function(html){
            $list.html(html);
        }, 'html');
    };
    $search.on('change', 'select', search_callback).on('keyup', 'input', search_callback);
    search_callback();

    function GP (group_id) {
        this.group_id = group_id;
        this.member_api = new pager('/group/memberlist/' + group_id, {}, {autorun:true, id:'#memberlist'});
        this.member_image = new pager('/group/member_image/' + group_id,{}, {autorun:true, id:'#newimagelist'});
        this.memberlist = $('#memberlist');
        this.newimagelist = $('#newimagelist');
    }
    GP.prototype = {
        addElement : function (target) {
            var product_id = target.getAttribute('data-product-id'), color_id = target.getAttribute('data-color-id');
            this.add(product_id, color_id);
        },
        add : function(product_id, color_id) {
            var group_id = this.group_id, api = this.member_api;
            $.post('/group/add_member', {group_id:group_id, product_id:product_id, color_id:color_id}, function(){
                require.async(['jquery/jquery.notify'], function(n){
                    n.message({title:'保存成功'}, {expires:2000});
                    setTimeout(function(){
                        api.reload();
                    }, 1000);
                });
            });
        },
        remove : function(id) {
            if(id) {
                var api = this.member_api,api2 = this.member_image;
                $.post('/group/remove_member', {id:id}, function() {
                    require.async(['jquery/jquery.notify'], function(n){
                        n.message({title:'移除成功'}, {expires:2000});
                        setTimeout(function(){
                            api.reload();
                            api2.reload();
                        }, 1000);
                    });
                });
            }
        }
    };


    if($list.length) {
        var group_id = $list.attr('data-group-id'), group = new GP(group_id); 
        $list.on('click', '.option a', function(e){
            group.addElement(this);
        });
        group.memberlist.on('click', '.remove', function(e){
            if(confirm("确认移除？")){
                group.remove(this.getAttribute('data-id'));
            }
        });
        group.newimagelist.on('click', '.delete', function(e){
            var that = this,id=this.getAttribute('data-id');
            if(id){
                $.post('/group/set_image',{id:id}, function(){
                    $(that).parents(".card").remove();
                });
            }else{
                $(that).parents(".card").remove();
            }
        })
    }
});