

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api = new pager('/orderlist/product_color_orderlist', {limit:20}, {autorun:true});
    
    // new lazy('.foot', function(){api.next()}, {delay:100, top:0});

    var $menu   = $('#HDT-select-menu');
    $menu.on('change', 'select', function(e){
        var target = e.currentTarget;
        if(target.name == "category_id") {
            $.get('/location/get_classes_list', {category_id:target.value}, function(html){
                $menu.find('select[name=classes_id]').replaceWith(html);
            });
            api.set(target.name, target.value, true);
            api.set('classes_id', 0, true);
            api.reload();
        }else{
        	api.set(target.name, target.value);
        }
    });

    $('body').on('click', '#checkall', function(){
        var checked = this.checked, i = 0;
        $(':checkbox:visible').not('#checkall').each(function(){
            if(i++ < 30){
                this.checked = checked;
            }
        });
    });

    function Q () {
        this.list = [];
        this.is_running = false;
    };
    Q.prototype = {
        add : function(data, callback) {
            this.list.push({data:data, callback:callback});
            this.run();
        },
        run : function(){
            if(this.is_running === true) return false;
            var params = this.list.shift(), that = this;
            if(params){
                this.is_running = true;
                $.post("/product/set_product_color_status", params.data, function(json){
                    if("function" === typeof params.callback){
                        params.callback.call(that);
                    }
                    that.is_running = false;
                    that.run();
                });
            }else{
                if(this.end_callback){
                    this.end_callback.call(this);
                }
            }
        }
    };
    var q = new Q;
    q.end_callback = function(){
        alert("操作成功");
        api.reload();
    };

    $('#unactive').on('click', function(){
        if(confirm("确认删除？")){
            $(':checkbox:checked').not('#checkall').each(function(){
                var that = this, data = get_data(this, 0);
                q.add(data, function(){
                    $(that).parents('tr').find('.status').html("<font color='red'>删除款</font>");
                });
            });
        }
    });

    $('#active').on('click', function(){
        if(confirm("确认恢复？")){
            $(':checkbox:checked').not('#checkall').each(function(){
                var that = this, data = get_data(this, 1);
                q.add(data, function(){
                    $(that).parents('tr').find('.status').html("<font color='green'>正常款</font>");
                });
            });
        }
    });

    function get_data (object, status) {
        var data = {status:status};
        data.product_id = object.getAttribute('data-product-id');
        data.product_color_id = object.getAttribute('data-product-color-id');
        return data;
    }

    $('#filter').on('keyup', function(){  	
        var filter = this.value, reg = new RegExp(filter), $main = $('#HDT-main'), n = 1;
        $main.find(':checked').each(function(){
            this.checked = false; 
        });
        if(filter) {
        	var str_skc=filter.split(";");
        	$main.find('tr[data-skc-id]').each(function(){
                var skc_id = this.getAttribute('data-skc-id'), kuanhao = this.getAttribute('data-kuanhao');
     		   	for(var i=0, len = str_skc.length; i < len; i++){
     		   		var value = str_skc[i];
     		   		if(value) {
     		   			if(skc_id == value  || kuanhao == value){//reg.test(kuanhao)
	                        this.style.display = "";
	                        $(this).find('td:first').html(n++);
	                        break;
	                    }else{
	                 	   this.style.display = "none";
	                    }
     		   		}
          		  	
     		   	}
             });
        }else{
        	$main.find('tr[data-skc-id]').css('display', '');
        }
    });

    
    var apiArr = [api];
    require.async('lj/fancybox',function(fancybox){
    	fancybox(apiArr,'');
    });
});