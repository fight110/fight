define(['jquery','lj/fancybox','jquery/fancybox/jquery.fancybox'], function(require, exports, module) {
	var wrap = '<div class="fancybox-wrap" tabIndex="-1"><div class="fancybox-skin" style="box-shadow:none;background:none;"><div class="fancybox-outer"><div class="fancybox-inner"></div></div></div></div>';
	var adProduct = function (product_id, target) {
		this.product_id = product_id;
		this.target 	= target;
		this.open();
	};
	adProduct.prototype = {
		init: function() {
			this.$el = $('.fancybox-inner');
			var that = this,product_id=this.product_id;
			this.$el.on('click','.close',function(){
				that.close();
			})
			this.$el.on('click','.gotobtn',function(e){
				that.gotobtn(e);
			})
			this.$el.on('change','.search',function(){
				that.search();
			})
			this.thirdTabShowed = false;
			this.$el.on('scroll', function(){
				if(false === that.thirdTabShowed && that.$el.scrollTop() > 600){
					that.thirdTabShowed = true;
	        		require.async(['app/pager', 'app/lazy'], function(pager, lazy){
	            		var group   = new pager('/product/list3/', {group_product_id:product_id,limit:12}, {id:'#HDT-group-list', autorun:true});
	        		});
				}
			});
			var $t = this.$el.find('#HDT_UNORDER_TABLE'), $h = $t.find('.HDT_HIDE'), $m = $t.find('tr:first');
			$m.on('click', function(){
				$h.toggle();
			});
			require.async(['iscroll'],function(){
				var $indicator  = that.$el.find('#indicator'), $big = that.$el.find('#HDT-photos-big'), $ind_li = $indicator.find('li'), is_click = false;
		        $indicator.find('li:first').addClass('hover');
		        var myScroll    = new iScroll('HDT-photos-big', {
		            snap: 'li',
		            momentum: false,
		            hScrollbar: false,
		            vScrollbar: false,
		            onScrollEnd: function () {
		                if(is_click){
		                    is_click = false;
		                }else{
		                    $indicator.find('li.hover').removeClass('hover');
		                    $indicator.find('li:nth-child(' + (this.currPageX+1) + ')').addClass('hover');
		                }
		            }
		        });
		        $indicator.on('click', 'li', function(e){
		            $indicator.find('li.hover').removeClass('hover');
		            this.className = 'hover';
		            for(var i = 0, len = $ind_li.length; i < len; i++){
		                if($ind_li[i] == this){
		                    break;
		                }
		            }
		            var target = $big.find('li').eq(i)[0];
		            if(target){
		                is_click = true;
		                myScroll.scrollToElement(target);
		            }
		        });
	        })
		},
		open: function(){
			var that = this,href = "/ad/product/" + this.product_id;
			$.fancybox.open({ type : 'ajax', padding : 0, margin : 0, href: href,  closeBtn:false,tpl: { wrap : wrap}, autoCenter : true,
				afterShow: function() {
					that.init();
				}
			});
		},
		close: function(){
			$.fancybox.close();
		},
		gotobtn: function(e) {
			var product_id = this.product_id, f = e.currentTarget.getAttribute('data-f'), 
				data  = {bianhao:product_id, f:f},
				target= this.target;
			if(f=="down"){
			    $('body').trigger('lastProduct', this);
				var next_product_id = $(target).next().attr('data-product-id');
				if(next_product_id){    
		             $.fancybox.close();
		             $(target).next().click();
			    }else{
			        require.async('jquery/jquery.notify', function(n){
		                 var message = "已经是最后一款";
		                 n.message({title:"提示", text:message}, {expires:2000});
			        });
			    }
			}else if(f=="up"){
				var next_product_id = $(target).prev().attr('data-product-id');
				if(next_product_id){    
		             $.fancybox.close();
		             $(target).prev().click();
			    }else{
			        require.async('jquery/jquery.notify', function(n){
		                 var message = "已经是第一款";
		                 n.message({title:"提示", text:message}, {expires:2000});
			        });
			    }
			}

		},
		search : function(){
			var q = this.$el.find('.search').val();
			require.async(['jquery/jquery.shCircleLoader-min'],function(){
				$('#loader').shCircleLoader();
				$.get("/product/search_get_id", {q:q}, function(json){
					if(json.product_id){
						var add = $('<li data-product-id='+json.product_id+' class="adProduct" style="display:none;"><li> ');
						$.fancybox.close();
			            add.appendTo('body').click().remove();
			        }else{
			            require.async('jquery/jquery.notify', function(n){
			                var message = json.message || "到最后";
			                n.message({title:"提示", text:message}, {expires:2000});
			            });
					}
					$('#loader').shCircleLoader('destroy');
				},'json');
			})
		}
	};
	return adProduct;
});
