define(['jquery', 'app/order', 'app/ordertable'], function(require, exports, module) {
	var OrderTable  = require('app/ordertable');
	var order = require('app/order');	
	$('body').on('click','.fancyboxBook',function(){
		var that = $(this);
		$.fancybox.open({
			type : 'ajax',
			padding : 0,
			margin : 0,
			href : that.attr('data-href'),
			//href : 'dealer1/detail/1',
			tpl: {
				wrap     : '<div class="fancybox-wrap" tabIndex="-1"><div class="fancybox-skin" style="box-shadow:none;background:none;"><div class="fancybox-outer"><div class="fancybox-inner"></div></div></div></div>',
			},
			afterShow : function(){
				var product_id = $('#HDT-product-id').val();			
				var $order  = $('#HDT-order-table');
			    order.initOrderList(product_id, function(json){
			        var list = json.list, len = list.length;
			        if(len){
			            for(var i = 0; i < len; i++){
			                var t = $order.find('input[data-color-id='+list[i].product_color_id+'][data-size-id='+list[i].product_size_id+']');
			                t.val(list[i].num);
			            }
			        } 
			        new OrderTable($('#HDT-order-table')[0], product_id);
			    });
			   require.async(['app/keyborad'], function(keyborad){
			        k = new keyborad('#HDT-order-table', {template:'order'});
			        k.on('change', function(e, input, config){
			            var value = input.value,
			                product_id  = input.getAttribute('data-product-id'),
			                color_id    = input.getAttribute('data-color-id'), 
			                size_id     = input.getAttribute('data-size-id');
			            if(config && config.is_byhandon){
			                $(input).parents('tr').find('input').not('#HDT-keyborad-input').each(function(){
			                    var nv = value;
			                    this.value=nv;
			                    var product_id  = this.getAttribute('data-product-id'), 
			                        color_id    = this.getAttribute('data-color-id'), 
			                        size_id     = this.getAttribute('data-size-id');
			                    order.add(product_id, color_id, size_id, nv);
			                    OrderTable.set(product_id, color_id, size_id, nv);
			                });
			            }else{
			                order.add(product_id, color_id, size_id, value);
			                OrderTable.set(product_id, color_id, size_id, value);
			            }
			        });
			        k.on('save', function(e){
			            var canSave = OrderTable.canSave();
			            if(canSave.error){
			                 require.async('jquery/jquery.notify', function(n){
			                    var message = canSave.message;
			                    n.warn({title:"提示", text:message}, {expires:2000});
			                });
			            }else{
			                order.save();
			            }
			        });
			        order.on('save', function(){
			            k.close();
			        });

			        $('#HDT-order-save').on('click', function(e){
			            k.save();
			        });
			        
			        $('body').on('click','#search_key',function(e){
			        	k.close();
			        });
			        
			    });
			   
			    require.async(['rateit.ref'], function(){
			    	$('div.rateit, span.rateit').rateit();
			        var $rate = $("#rateit1"), rateval = $rate.attr('data-rateit-value');
			        $rate.bind('rated', function (event, value) {
			            if(rateval == value) return false;
			            require.async(['app/move'], function(Move){
			                new Move('#HDT-photos-m', '#HDT_STORE_ICON', {});
			            });
			            $.post('/product/set_user_product', {product_id:product_id, status:true, rateval:value});
			            rateval = value;
			        });
			        $rate.bind('reset', function(){
			            $.post('/product/set_user_product', {product_id:product_id, status:0});
			            rateval = 0;
			        });
			        $('#HDT_reset_store').on('click', function(e){
			            $rate.rateit('value', 0).trigger('reset');
			            return false;
			        });
			    });
			    
			    
			    
		        
		        $('.detailTopImgul li').eq(0).addClass('nowSelected');
		        
		        $('.detailTopImgul li').click(function(){
		        	var imgUrl = $(this).find('img').attr('src');
		        	if(imgUrl!=''){
		        		imgUrl = imgUrl.replace('/thumb/75/','/thumb/210/');    
		        		$('#HDT-photos-m').attr('src',imgUrl);
		        	}   	   	
		        	$(this).addClass('nowSelected').siblings().removeClass('nowSelected');
		        });
		        
		        if(product_id){
		            require.async(['app/pager', 'app/lazy'], function(pager, lazy){
		                var master_string = '#HDT-master-orderlist', $master = $(master_string);
		                if($master.length){
		                    var master   = new pager('/orderlist/masterlist/', {product_id:product_id}, {id:master_string,autorun:true});
		                    var viewall  = function(e){
		                        e.preventDefault();
		                        $master.off('click', '.HDT_VIEWALL', viewall);
		                        $.get('/orderlist/masterlistuser', {product_id:product_id}, function(html){
		                            var $html = $(html).appendTo($master);
		                            $master.on('click', '.HDT_VIEWALL', function(){
		                                e.preventDefault();
		                                $html.toggle();
		                                return false;
		                            });
		                        }, 'html');
		                        return false;
		                    };
		                    $master.on('click', '.HDT_VIEWALL', viewall);
		                }
		                var stock_string = "#HDT-stock", $stock = $(stock_string);
		                if($stock.length){
		                    stock_api = new pager("/product/stocktable", {product_id:product_id}, {id:stock_string,autorun:true});
		                    order.on('save', function(){
		                        setTimeout(function(){
		                            stock_api.reload();
		                        }, 500);
		                    })
		                }
		            });
		        }
		        

			},
			afterClose : function(){
				k.close();
			},
		})
	})
	

		$('body').on('click','.fancyboxTableft li',function(){
			var that = $(this);
			var _index = $('.fancyboxTableft li').index(this);
			if(!that.hasClass('selectTab')){
				that.addClass('selectTab').siblings().removeClass('selectTab');
				$('.productDetail li.productTab:visible').hide();
				$('.productDetail li.productTab').eq(_index).fadeIn();
				
			}
			//fancyResize();
		})
		
		
	
	
	
	
	$('body').on('click','.thirdTab', function(e){
		var product_id = $('#HDT-product-id').val();
		var that = $(this);
		if(!that.hasClass('hasLoaded')){
			 require.async(['app/pager', 'app/lazy'], function(pager, lazy){
	                var group   = new pager('/product/list/', {group_product_id:product_id}, {id:'#HDT-group-list'});
	                var group1  = new pager('/product/grouplist/', {product_id:product_id}, {id:'#HDT-group-list1'});
	                var display = new pager('/product/displaylist/', {product_id:product_id}, {id:'#HDT-display-list'});
	                group.next();
	                group1.next();
	                display.next();
	                })
			
			 var $big = $('#HDT-photos-big');
		        if($big.length){

		            require.async(['swipe'], function(){
		            	var $indicator  = $('#indicator'), $ind_li = $indicator.find('li');
		                $indicator.find('li:first').addClass('hover');
		            	var elem = document.getElementById('HDT-photos-big');
		            	window.mySwipe = Swipe(elem, {
		            	  // startSlide: 4,
		            	  // auto: 3000,
		            	  // continuous: true,
		            	  // disableScroll: true,
		            	  // stopPropagation: true,
		            	  callback: function(index, element) {
		            		  $indicator.find('li.hover').removeClass('hover');
		            		  $ind_li.eq(index).addClass('hover');
		            	  },
		            	  // transitionEnd: function(index, element) {}
		            	});
		                
		                $indicator.on('click', 'li', function(e){              
		                    var _index = $ind_li.index(this);
		                    mySwipe.slide(_index);
		                });
		            });
		        
		        }
		        that.addClass('hasLoaded');
		}
    });
	
	
	$('body').on('click','#HDT-comment-save', function(e){
		var product_id = $('#HDT-product-id').val();
		var product_comment = $('#product_comment').val();
		 $.post('/product/set_comment', {product_id:product_id, product_comment:product_comment},
		 	function (data, status){
		 		var flag = data.flag;
		 		require.async('jquery/jquery.notify', function(n){
		 			flag === false ?
                	n.warn({title:"提示", text:"您的建议保存失败！"}, {}) :
                	n.message({title:"提示", text:"您的建议保存成功！"}, {expires:2000});
                });
		 	}
		 );
    });

$('body').on('click','#HDT-order-cancel', function(e){
	var product_id = $('#HDT-product-id').val();
    var $order  = $('#HDT-order-table');
    require.async('jquery/jquery.notify', function(n){
        n.confirm({title:"取消订单", text:"确定删除此订单?"}, function(){
            $order.find('input').each(function(){this.value="";});
            order.reset();
            order.remove(product_id);
            OrderTable.clear(product_id);
        });
    });
});
	
$('body').on('click', '.gotobtn' , function(e){
    var product_id = $('#HDT-product-id').val(), f = this.getAttribute('data-f'), data = {bianhao:product_id, f:f};
    $.get("/product/get_product_id_by_bianhao", data, function(json){
        if(json.product && json.product.id){     	
            var add = $('<a data-href="/dealer1/product/'+json.product.id+'" href="javascript:void(0)" class="fancyboxBook" id="addTemp" style="display:none;">go</a>');
            $.fancybox.close();
            add.appendTo('body').click().remove();
        }else{
            require.async('jquery/jquery.notify', function(n){
                var message = json.message || "到最后";
                n.message({title:"提示", text:message}, {expires:2000});
            });
        }
    }, 'json');
});

	function fancyResize(){
		$.fancybox._setDimension();
		$.fancybox.reposition();
	}
});
