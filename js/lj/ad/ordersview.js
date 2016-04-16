

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), 
    	api_orderlist = new pager('/orderlist/myordersview', {}, {autorun:true,aftercallback:check_error}), 
    	api_summary = new pager('/ad/myorders_summary', {} , {autorun:true,id:'#HDT-summary'}),
    	$menu = $('#HDT-select-menu');
    
    if($('.Selects').length){
        require.async('app/admin.select', function(select){
            select(api_orderlist);
        });
    }

    $menu.on('change', 'select', function(e){
    	var target = e.currentTarget;
    	if(target.name == "category_id") {
            $.get('/location/get_classes_list', {category_id:target.value}, function(html){
                $menu.find('select[name=classes_id]').replaceWith(html);
            });
            api_orderlist.set(target.name, target.value, true);
            api_orderlist.set('classes_id', 0, true);
            api_orderlist.reload();
            api_summary.set(target.name, target.value, true);
            api_summary.set('classes_id', 0, true);
            api_summary.reload();
        }else{
        	api_orderlist.set(this.name, this.value);
        	api_summary.set(this.name, this.value);
        }
    });
    
    $('.typeSelect').on('change',function(){
    	api_orderlist.set(this.name, this.value);
    })

    function check_error () {
        var wrong = $('#orderDetailView').attr('data-wrong-order'), config = {
            td      : 'td.HDT-order-detail',
            border  : "2px solid #FF0000"
        };
        if(wrong){
            $(config.td).each(function(){
                var val = this.innerHTML >> 0;
                if(val >= wrong){
                    this.style.border = config.border;
                }
            });
        }
        
        $('#orderDetailView tr').each(function(){
            var td = $(this).find(config.td), firstTd = null, lastTd = null, list = [], max = 0, num_hash = {};
            td.each(function(){
                var val = this.innerHTML>>0;
                if(val > max){
                    max = val;
                    num_hash[max] = 0;
                }
                num_hash[val] += 1;
                if(firstTd == null && val > 0){
                    firstTd = this;
                }
                if(firstTd != null){
                    if(val == 0 && lastTd == null){
                        list.push(this);
                    }else{
                        if(list.length == 0){
                            firstTd = this;
                        }else{
                            lastTd = this;
                        }
                    }
                }
            });
            if(list.length && lastTd){
                for(var i = 0, len = list.length; i < len; i++){
                    list[i].style.border = config.border;
                }
            }
            if(num_hash[max] < 4){
                //this.style.border = config.border;
            }
        });
    }
    
    var apiArr = [api_orderlist,api_summary];
    require.async('lj/fancybox',function(fancybox){
    	fancybox(apiArr,'');
    });
    $('.orderViewBody').on('touchmove mousemove',".touch_image",function(){
		var that = this;
		if($(that).find('img').css("width")=="75px"){
			return false;
		}
		$("#orderDetailView").find(".touch_image").each(function(){
			$(this).find('img').css("width","20px");
		})
		$(that).find('img').css("width","75px");
    });
});