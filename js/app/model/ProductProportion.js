
define(['jquery'], function(require, exports, module) {
    var Proportion = function (product_id, selector) {
      	this.product_id = product_id;
      	this.$el 		= $(selector);
      	var that = this;
      	this.data 		= [];
      	this.post_data  = {product_id:product_id, color:{}};
      	this.$inputs 	= this.$el.find('input').each(function(){
      		that.bindInput(this);
      	});
    };
    Proportion.prototype = {
    	init: function(){
    		var that = this, product_id = this.product_id;
    		return $.get('/orderlist/proportion_list', {product_id:product_id}, 'json').done(function(json){
    			var list = json.list;
    			for(var i = 0, len = list.length; i < len; i++) {
    				var row = list[i], color_id = row['product_color_id'], proportion_id = row['proportion_id'], num = row['xnum'];
    				that.setColorProportionNum(color_id, proportion_id, num);
    			}
    		});
    	},
    	setColorProportionNum: function(color_id, proportion_id, num) {
    		this.$el.find('input[data-color-id='+color_id+'][data-proportion-id='+proportion_id+']').val(num).trigger('keyup');
    	},
    	bindInput: function(input) {
    		var color_id = input.getAttribute('data-color-id'),
    			proportion_id = input.getAttribute('data-proportion-id'),
    			proportion 	= input.getAttribute('data-proportion'),
    			proportion_list = proportion.split(':'),
    			that = this, color = this.getColor(color_id);
    		color.setProportion(proportion_id, proportion_list, this.value);
    		$(input).on('keyup', function(){
    			color.setProportionNum(proportion_id, this.value);
    			that.setPostData(color_id, proportion_id, this.value);
    			that.$el.trigger('proportionChanged', color);
    		});
    	},
    	setPostData: function(color_id, proportion_id, value) {
    		var color_hash = this.post_data['color'], hash;
    		if(!color_hash) {
    			color_hash = this.post_data['color'] = {};
    		}
    		hash = color_hash[color_id];
    		if(!hash) {
    			hash = color_hash[color_id] = {};
    		}
    		hash[proportion_id]	= value;
    	},
    	getColor: function(color_id) {
    		var color = this.data[color_id];
    		if(!color) {
    			color = new ProductColor(color_id);
    			this.data[color_id]	= color;
    		}
    		return color;
    	},
    	on: function() {
    		this.$el.on.apply(this.$el, arguments);
    	},
    	save: function() {
    		var product_id = this.product_id, data = this.post_data;
    		var xhr = $.post('/orderlist/proportion_add', data, 'json').done(function(json){
    			require.async('jquery/jquery.notify', function(n){
    				if(json.error) {
	                    n.warn({title:"提示", text:json.message}, {expires:2000});
    				}else{
	                    n.message({title:"提示", text:json.message}, {expires:2000});
    				}
	            });
    		});
    		return xhr;
    	},
    	cancel: function(){
    		this.$inputs.each(function(){
    			if(this.value) {
    				this.value = 0;
    				$(this).trigger('keyup');
    			}
    		});
    		var product_id = this.product_id, that = this,
    			xhr = $.post('/orderlist/proportion_cancel', {product_id:product_id}, 'json').done(function(json){
    				var message = json.message || '取消成功';
    				require.async('jquery/jquery.notify', function(n){
	    				if(json.error) {
		                    n.warn({title:"提示", text:message}, {expires:2000});
	    				}else{
	    					that.$el.trigger('proportionChanged');
		                    n.message({title:"提示", text:message}, {expires:2000});
	    				}
		            });
    			});
    		return xhr;
    	}
    };

   	function ProductColor (color_id) {
   		this.color_id = color_id;
   		this.data 	= {};
   	}
   	ProductColor.prototype = {
   		clear: function() {
   			for(var proportion_id in this.data) {
   				this.setProportionNum(proportion_id, 0);
   			}
   		},
   		setProportion: function(proportion_id, proportion_list, num) {
   			this.data[proportion_id]	= {proportion_list:proportion_list};
   			this.setProportionNum(proportion_id, num);	
   		},
   		setProportionNum: function(proportion_id, num) {
   			this.data[proportion_id]['num']	= num>>0;
   		},
   		get_value_list: function() {
   			var list = [];
   			for(var proportion_id in this.data) {
   				var proportion = this.data[proportion_id], proportion_list = proportion.proportion_list, num = proportion.num;
   				for(var i = 0, len = proportion_list.length; i < len; i++) {
   					var n = list[i]>>0;
   					list[i]	= num * (proportion_list[i]>>0) + n;
   				}
   			}
   			return list;
   		}
   	};

    return Proportion;
});