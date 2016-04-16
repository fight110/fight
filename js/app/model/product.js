define(['jquery', 'app/ordertable'], function(require, exports, module) {
	var wrap = '<div class="fancybox-wrap" tabIndex="-1"><div class="fancybox-skin" style="box-shadow:none;background:none;"><div class="fancybox-outer"><div class="fancybox-inner"></div></div></div></div>',
		OrderTable = require('app/ordertable'), k;
	var Product = function (product_id, target) {
		this.product_id = product_id;
		this.target 	= target;
		this.open();
	};
	Product.prototype = {
		init: function () {
			this.$el = $('.fancybox-inner');
			this.$save_button = $('#HDT-order-save');

			var that = this, $table = this.$el.find('#HDT-order-table'), ordertable_exist = $table.length, $proportion_table = $('#proportion_table'), product_id = this.product_id;
			
			if($proportion_table.length) {
				require.async(['app/model/ProductProportion'], function(ProductProportion){
		            var productProportion = new ProductProportion(product_id, $proportion_table), proportion_callback = function(color) {
		                var list = color.get_value_list(), color_id = color.color_id, i = 0;
		                $table.find('input[data-color-id="'+color_id+'"]').each(function(){
		                    var n = list[i++];
		                    OrderTable.set(product_id, color_id, this.getAttribute('data-size-id'), n);
		                    this.value = n;
		                });
		            };
		            productProportion.init().done(function(){
		                that.ordertable = new OrderTable($table[0], product_id);
		            });
		            productProportion.on('proportionChanged', function(e, color){
		                if(color) {
		                    proportion_callback(color);
		                }else{
		                    for(var color_id in productProportion.data) {
		                        proportion_callback(productProportion.data[color_id]);
		                    }
		                }
		            });
		            $('#HDT-order-save').on('click', function(e){
		                productProportion.save();
		                that.hasChanged = true;
		            });
		            $('#HDT-order-cancel').on('click', function(e){
		                require.async('jquery/jquery.notify', function(n){
		                    n.confirm({title:"取消订单", text:"确定删除此订单?"}, function(){
		                        productProportion.cancel();
		                		that.hasChanged = true;
		                    });
		                });
		            });

		            require.async(['rateit.ref'], function(){
						new Rateit(that);
					});
		        });
			}else{
				var filter_color_id = this.target.getAttribute('data-color-id')>>0;
				if(filter_color_id) {
					$table.find('tr[data-color-id]').each(function(){
						var color_id = this.getAttribute('data-color-id');
						if(color_id != filter_color_id) {
							$(this).find('input').addClass('inputGary');//.css('background', 'none repeat scroll 0 0 #e9e9e9');
						}else{
							$(this).addClass('color_on');
						}
					});
				}
				if(ordertable_exist){
					var $user_list 	= this.$el.find('.mulit_user li').on('click', function(e){
						$user_list.removeClass('on');
						$(this).addClass('on');
						that.initOrder(this.getAttribute('data-user-id'));
					});
					this.user_list  = $user_list;
					this.initOrder();

					require.async(['app/keyborad', 'rateit.ref'], function(keyborad){
						new Keyborad(keyborad, this);
						new Rateit(this);
					}.bind(this));
				}

				var $productDetail = this.$el.find('.productDetail li.productTab'), that = this;
				var $tablist = this.$el.find('.fancyboxTableft li').on('click', function(){
					var $this = $(this);
					if($this.hasClass('selectTab')) return false;
					var _index = $tablist.index(this);
					$this.addClass('selectTab').siblings().removeClass('selectTab');
					$productDetail.hide().eq(_index).fadeIn();
				});

				this.$el.on('click', '[hdt-p]', function(e){
					var event = this.getAttribute('hdt-p');
					if(event && 'function' == typeof that[event]){
						that[event].call(that, e);
					}
				});
			}

			this.$el.on('click','#HDT-comment-save',function(){
				that.commentSave();
			})

			this.$el.on('click','.gotobtn',function(e){
				that.gotobtn(e);
			})

			this.$el.on('change','.search',function(){
				that.search();
			})

			this.$el.on('focus','.search',function(){
				k && k.close();
			})

			this.$el.on('click','.close',function(){
				that.close();
			})
			
			this.thirdTabShowed = false;
			this.$el.on('scroll', function(){
				if(false === that.thirdTabShowed && that.$el.scrollTop() > 600){
					that.thirdTabShowed = true;
					that.thirdTab();
				}
			});
		},
		initOrder: function(user_id) {
			var order = new Order(user_id, this);
			order.init().done(this.initOrderList.bind(this));
			this.order  = order;
		},
		open: function () {
			var product_id = this.product_id, that = this,
				href = '/dealer1/product/' + product_id;
			if(that.target.getAttribute('data-slide-navi')){
				href = href + '?slide_navi=1';
			}
			$.fancybox.open({ type : 'ajax', padding : 0, margin : 0, href: href, closeBtn:false, tpl: { wrap : wrap }, autoCenter : true,
				afterShow: function() {
					that.init();

					var order = that.order;

					$('.detailTopImgul li').eq(0).addClass('nowSelected');
		        
					$('.detailTopImgul li').click(function(){
						var imgUrl = $(this).find('img').attr('src');
						if(imgUrl!=''){
							imgUrl = imgUrl.replace('/thumb/75/','/thumb/280/');    
							$('#HDT-photos-m').attr('src',imgUrl);
						}
						$(this).addClass('nowSelected').siblings().removeClass('nowSelected');
					});

					// $('.detailColor').click(function(){
					$('.detailColor').on('click touchstart','input,td',function(){
						var thats = this;
						require.async(['jquery/jquery.scrollTo'], function(s){
							that.$el.scrollTo('220px', { axis:'y' } );
							//that.$el.scrollTo($thats);
							var color_id = $(thats).parents('tr').attr('data-color-id');
							$.get("/product/get_pcimage",{product_id:product_id,color_id:color_id},function(json){
								if(json.valid){
									var imgUrl='/thumb/280/'+json.image;
									$('#HDT-photos-m').attr('src',imgUrl);
								}
							},'json');
						});
					});

					var search_color_id = that.target.getAttribute('data-search-color-id')>>0;
					if(search_color_id) {
						$.get("/product/get_pcimage",{product_id:product_id,color_id:search_color_id},function(json){
							if(json.valid){
								var imgUrl='/thumb/280/'+json.image;
								$('#HDT-photos-m').attr('src',imgUrl);
							}
						},'json');
					}

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
					        }else{
					        	order.on('save',function(){
					        		require.async(['jquery/jquery.scrollTo'], function(s){
										that.$el.scrollTo('0', { axis:'y' } );
					        		})
					        	})
					        }
					    });
					}
				},
				afterClose : function(){
					k && k.close();
					if(that.hasChanged) {
						$('body').trigger('productOrderChanged', that);
					}
				}
				
			});
		},
		close: function(){
			$.fancybox.close();
		},
		initOrderList: function (json) {
			var list = json.list, len = list.length, $order  = this.$el.find('#HDT-order-table');
			OrderTable.clear(this.product_id);
			$order.find('input[data-size-id]').each(function(){
				this.value = '';
			});
	        if(len){
	            for(var i = 0; i < len; i++){
	            	var product_color_id = list[i].product_color_id;// + '-' + list[i].product_pattern_id;
	                var t = $order.find('input[data-color-id='+product_color_id+'][data-size-id='+list[i].product_size_id+']');
	                t.val(list[i].num);
	            }
	        }
	        this.ordertable = new OrderTable($('#HDT-order-table')[0], this.product_id);
		},
		commentSave : function () {
			var product_id = this.product_id;
			var product_comment = this.$el.find('#product_comment').val();
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
		},
		orderCancel : function() {
			var product_id = this.product_id, $order  = $('#HDT-order-table'), that = this;
			require.async('jquery/jquery.notify', function(n){
			    n.confirm({title:"取消订量", text:"确定删除此款全部订量?"}, function(){
			        that.order.remove().then(function(json){
			        	if(json.valid) {
			        		$order.find('input').each(function(){this.value="";});
			        	}
			        });
			    });
			});
		},
		deleteColor : function(e) {
			var product_id = this.product_id, that = this, color_id = e.target.getAttribute('data-color-id');
			require.async('jquery/jquery.notify', function(n){
			    n.confirm({title:"取消订量", text:"确定删除此款全部订量?"}, function(){
			        that.order.remove(color_id).then(function(json){
			        	if(json.valid) {
			        		$(e.target).parents('tr').find('input').each(function(){this.value="";});
			        	}
			        });
			    });
			});
		},
		orderSave: function() {
			k.save();
			// this.order.save();
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
		thirdTab: function () {
			var product_id = this.product_id;
			if(product_id){
				require.async(['app/pager', 'app/lazy'], function(pager, lazy){
					new pager('/product/list/', {group_product_id:product_id}, {id:'#HDT-p-group-list', autorun:true});
					new pager('/product/grouplist/', {product_id:product_id}, {id:'#HDT-p-group-list1', autorun:true});
					new pager('/product/displaylist/', {product_id:product_id}, {id:'#HDT-p-display-list', autorun:true});
		        });
				
				var $big = $('#HDT-photos-big');
				if($big.length){
				    require.async(['swipe'], function(){
				    	var $indicator  = $('#indicator'), $ind_li = $indicator.find('li');
				        $indicator.find('li:first').addClass('hover');
				    	var elem = document.getElementById('HDT-photos-big');
				    	window.mySwipe = Swipe(elem, {
				    	  callback: function(index, element) {
				    		  $indicator.find('li.hover').removeClass('hover');
				    		  $ind_li.eq(index).addClass('hover');
				    	  }
				    	});
				        
				        $indicator.on('click', 'li', function(e){              
				            var _index = $ind_li.index(this);
				            mySwipe.slide(_index);
				        });
				    });

				}
			}
		},
		search : function(){
			this.$el.find('.search').blur();
			var q = this.$el.find('.search').val();
			require.async(['jquery/jquery.shCircleLoader-min'],function(){
				$('#loader').shCircleLoader();
				$.get("/product/search_get_id", {q:q}, function(json){
					if(json.product_id){
						var add = $('<li data-product-id='+json.product_id+' data-search-color-id='+json.color_id+' data-slide-navi="1" class="fancyboxBook" style="display:none;"><li> ');
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

	function Keyborad (keyborad, product) {
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
			        OrderTable.set(product_id, color_id, size_id, nv);
			    });
			}else{
			    OrderTable.set(product_id, color_id, size_id, value);
			}
		});
		k.on('save', function(e){
		    var canSave = OrderTable.canSave(product.product_id);//保存时只判断当前product_id
		    if(canSave.error){
		         require.async('jquery/jquery.notify', function(n){
		            var message = canSave.message;
		            n.warn({title:"提示", text:message}, {expires:2000});
		        });
		    }else{
		        product.order.save();
		    }
		});
		product.order.on('save', function(){
		    k.close();
		});
	}

	function Rateit (product) {
		this.product = product;
		this.target  = product.$el.find('.rateit').rateit();
		this.rateval = this.target.attr('data-rateit-value');
		this.target.on('rated', this.rated.bind(this)).on('reset', this.reset.bind(this));
		$('#HDT_reset_store').on('click', function(e){
			this.target.rateit('value', 0).trigger('reset');
			return false;
		}.bind(this));
	}
	Rateit.prototype = {
		rated: function(event, value) {
			if(this.rateval == value) return false;
			$.post('/product/set_user_product', {product_id:this.product.product_id, status:true, rateval:value});
			this.rateval = value;
		},
		reset: function() {
			$.post('/product/set_user_product', {product_id:this.product.product_id, status:0});
			this.rateval = 0;
		}
	};

	function Order (user_id, product) {
		this.user_id 	= user_id;
		this.product 	= product;
		this.product_id = product.product_id;
		this.$  = $({});
	}
	Order.prototype = {
		init: function() {
			var xhr = $.get('/orderlist/list/' + this.product_id, {user_id:this.user_id}, function(json){}, 'json');
			return xhr;
		},
		save: function() {
			if(this.status) return false;
			this.product.$save_button.val("提交中...");
			this.status = true;
			var that = this;
			setTimeout(function(){
				that.status = false;
			}, 1500);
			var url ="/orderlist/add", product_id = this.product_id, ordertable = OrderTable.get(product_id), data = ordertable.get_result();
			data['user_id']	= this.user_id;

	        $.ajax({ url: url, type: 'post', data: data, dataType : 'json' }).done(function(d) {
	            var message = d.message, valid = d.valid, expires=d.expires;
	            require.async('jquery/jquery.notify', function(n){
	                if(valid === false){
	                    n.warn({title:"保存失败", text:message}, {}); 
	                }else{
	                    n.message({title:"保存订单", text:message}, {expires:expires || 2000});
	                }
	            });
	            that.product.$save_button.val("保存订单");
	        });
	        this.product.hasChanged = true;
        	this.trigger('save');
		},
		remove: function(product_color_id) {
			var product_id = this.product_id, user_id = this.user_id, product = this.product;
			return $.post('/orderlist/remove', {product_id:product_id, user_id:user_id,color_id:product_color_id}, function(json){
				var message = json.valid ? "订单取消成功" : json.message;
				require.async('jquery/jquery.notify', function(n){
				    if(json.valid === false){
				        n.warn({title:"保存失败", text:message}, {});
				    }else{
				        n.message({title:"取消订单",text:message}, {expires:2000});
						product.hasChanged = true;
						OrderTable.clear(product_id, product_color_id);
				    }
				});
			}, 'json');
		},
		on: function(event, callback){
			this.$.on(event, callback);
		},
    	trigger : function(event){
			this.$.trigger(event);
    	}
	};

	return Product;
});
