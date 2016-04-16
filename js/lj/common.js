define(['jquery', 'lj/fancybox'], function(require, exports, module) {
	$('.target_total').click(function(){
		$('.target_sub').toggle();
	})
	
		$(document).scroll(function(){
		var sTop = $(document).scrollTop();
		if(sTop>0){
			$('.HDT_gotop').slideDown();
		}else{
			$('.HDT_gotop').slideUp();
		}
	})

	$('.HDT_gotop').click(function(){
		$('html,body').animate({scrollTop: '0px'}, 500);
	})

	// $('.rightbar h3').click(function(){
	// 	var _index = $('.rightbar h3').index(this);
	// 	$('.rsubmenu').eq(_index).toggle();

	// })
	
	$('.notfinished').click(function(){
		var callback = function(){
			$.post('/dealer1/commit_order_status',{},function(res){
				if(res.error=='false'){
					$('.notfinished').addClass('hadclick');
					setTimeout(function(){
						location.reload(true);
					},2000);
				}
					 require.async('jquery/jquery.notify', function(n){
					        n.message({title:'提示',text:res.message}, {expires:2000});
					    });
				//}			
			},'json');
		}
		if($(this).hasClass('hadclick')){
			require.async('jquery/jquery.notify', function(n){
		        n.message({title:'提示',text:'你已经提交过了！'}, {expires:2000});
		    });
		}else{
			//$(this).addClass('hadclick');
			require.async('jquery/jquery.notify', function(n){
		        n.confirm({title:'提示',text:'确认提交订单吗？提交之后将无法下单！'},callback);
		    });
		}
	    
		return false;
	})

	$(document).ready(function(){
		require.async(['jquery/fancybox/jquery.fancybox']);
		// $('.fancyboxInput').click(function(){
		// 	$.fancybox.open({
		// 		type : 'inline',
		// 		href : '#searchMain',
		// 		afterShow : function(){
		// 			$('#searchBarText').val($('#search_key').val());
		// 			$('#searchBarText').attr('readonly',false);
		// 			t = setTimeout(function(){
		// 				$('#searchBarText').focus();					
		// 			},500)						
		// 			},
		// 		afterClose : function(){
		// 			$('#search_key').val($('#searchBarText').val());
		// 			$('#searchBarText').attr('readonly','readonly');
		// 			clearTimeout(t);
		// 		},
		// 	})
		// })
		
		// $('#searchBarBut').click(function(){
		// 	var that = $(this);
		// 	var input = $('#searchBarText');
		// 	var input_val = $.trim(input.val());
		// 	if(input_val!=''){
		// 		if(!that.hasClass('hadClick')){
		// 		that.addClass('hadClick');
		// 		$('#searchBarText').blur();
		// 		$('#searchResult').html('<div class="nowloading"></div>');			
		// 		fancyResize();
		// 		$.get('/product/search',{q:input_val},function(html){
		// 			$('#searchResult').html(html);
		// 			fancyResize();
		// 			that.removeClass('hadClick');
		// 		})
		// 		}
		// 	}	
		// })
		
		
		
		// $('body').on('click','.searchPage a',function(){
		// 	var that = $(this);
		// 	var searchbut = $('#searchBarBut');
		// 	var input_val = $.trim(that.attr('data-q'));
		// 	if(input_val!=''){
		// 		if(!searchbut.hasClass('hadClick')){
		// 		var p = that.attr('data-p');
		// 		searchbut.addClass('hadClick')
		// 		$('#searchBarText').blur();
		// 		$('#searchResult').html('<div class="nowloading"></div>');			
		// 		fancyResize();
		// 		$.get('/product/search',{q:input_val,p:p},function(html){
		// 			$('#searchResult').html(html);
		// 			fancyResize();
		// 			searchbut.removeClass('hadClick');
		// 		})
		// 		}
		// 	}	
		// })
		
		// $('#searchBarText').keydown(function(e){
		// 	var curKey = e.which; 
		// 	if(curKey==13){
		// 		$('#searchBarBut').click();
		// 	}
		// })
		$('.mulit_user_change').on('click', function(){
			require.async(['app/model/mulit_user_change'], function(mulit_user_change){
				new mulit_user_change;
			});
		});
		var touch = true;
		$('body').on('touchend touchmove click', '.fancyboxBook', function(e){
			var type = e.type;
			switch(type){
				case 'touchmove':
				touch = false;
				break;
				case 'touchend':
				case 'click':
					if(touch === true){
						e.preventDefault();
						var that = this, product_id = this.getAttribute('data-product-id');
						require.async(['app/model/product'], function(Product){
							new Product(product_id, that);
						});
					}
					touch = true;
				break;
			}
		});

	    $('body').on('touchend touchmove click', '.adProduct', function(e){
	        var type = e.type;
	        switch(type){
	            case 'touchmove':
	            touch = false;
	            break;
	            case 'touchend':
	            case 'click':
	                if(touch === true){
	                    e.preventDefault();
	                    var that = this, product_id = this.getAttribute('data-product-id');
	                    require.async(['app/model/ad_product'], function(adProduct){
	                        new adProduct(product_id, that);
	                    });
	                }
	                touch = true;
	            break;
	        }
	    });
	    
		var $mainbox = $('.mainbox'), menuhide = 'menuhide';
		var $mini_menu = $('#mini_menu').on('click', function(){
			$mainbox.toggleClass(menuhide);
			$mini_menu.html($mainbox.hasClass(menuhide) ? "显示菜单" : "隐藏菜单");
		});
		if($mainbox.hasClass(menuhide)){
			$mini_menu.html("显示菜单");
		}
	})
	
	function fancyResize(){
		$.fancybox._setDimension();
		$.fancybox.reposition();
	}

	$("#search_key").on('focus',function(){
		$(this).val('');
	})

	$('body').on('productOrderChanged', function(e, product){
		$.get("/orderlist/update_top",{},function(html){
			$(".update_top").html(html);
		})
	})
});

function fomatFloat (src, pos) {
	return Math.round(src*Math.pow(10,pos)/Math.pow(10, pos));
}