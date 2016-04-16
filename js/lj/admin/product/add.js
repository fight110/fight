

define(['jquery'], function(require, exports, module) {
    var $form = $('#form-main'), photos = null;

    $('#imagelist').on('click', '.delete', function(e){
        var id = this.getAttribute('data-id'), that = this;
        if(confirm("确认移除图片？")) {
            if(id) {
                $.post('/product/remove_image/', {id:id}, function(){
                    $(that).parents('.card').remove();
                });
            }else{
                $(that).parents('.card').remove();
            }
        }
    }).on('click', '.set_default', function(){
        var url = this.getAttribute('data-url');
        if(url) {
            $('#defaultimage').val(url);
            $('.card.defaultimage').removeClass('defaultimage');
            $(this).parents('.card').addClass('defaultimage');
        }
    });

    require.async('app/upload.jquery', function(FileUpload){
        FileUpload(function(){
            $('.fileupload').fileupload({
                dataType: 'json',
                done: function (e, data) {
                    var product_id = this.getAttribute('data-product-id'), color_id = this.getAttribute('data-color-id'), color = this.getAttribute('data-color') || '';
                    $.each(data.result.files, function (index, file) {
                        $.post('/product/add_image/', {product_id:product_id, color_id:color_id, image:file.url}, function(json){
                            if($('#defaultimage').val()=="") {
                                $('#defaultimage').val(file.url);
                            }
                            var html = '<div class="black card">'+
                                '<div class="image"><img src="/thumb/210/'+file.url+'"></div>'+
                                '<div class="extra content"><div class="meta">' + color + 
                                    '<div class="right floated delete" data-id="'+json.id+'">删除</div>' +
                                    '<div class="right floated set_default" data-url="'+file.url+'">设为默认</div></div></div>';
                            $('#imagelist').append(html);

                        }, 'json');
                    });
                }
            });
        });
    });

    $('#td-size').on('click', '[data-id]', function(e){
        var target = e.currentTarget, id = target.getAttribute('data-id');
        if(target.checked == false){
            $.get('/product/check_size_orderlist/' + id, function(json){
                if(json.num) {
                    if(confirm("该尺码订量已经有" + json.num + "件,删除后将清除相应订单，确认要删除？")){
                        $.post('/product/remove_size/' + id);
                    }else{
                        target.checked = true;
                    };
                }else{
                        $.post('/product/remove_size/' + id);
                }
            }, 'json');
        }
    });

    $('#td-color').on('click', '[data-id]', function(e){
        var target = e.currentTarget, id = target.getAttribute('data-id');
        if(target.checked == false){
            // $.post('/product/remove_color/' + id);
        }
    });


    var $sizes = $('#td-size');
    $('input[name=size_group_id],select[name=size_group_id]').on('change', function(){
        $sizes.find('input').parents('li').hide();
        var size_group_id = this.value;
        $.get('/product/size_group_list/' + size_group_id, {}, function(json){
            var size_list = json.size_list;
            for(var i = 0, len = size_list.length; i < len; i++) {
                var size_id = size_list[i].size_id;
                $sizes.find('input[value='+size_id+']').parents('li').show();
            }
        }, 'json');
    }).trigger('change');

    $('.d_intro h3 i').on('click', function(){
        var target = this.getAttribute('data-target');
        $(target).toggle();
        $(this).toggleClass('fa-chevron-down').toggleClass('fa-chevron-up');
    });
});