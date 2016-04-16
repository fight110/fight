define(['jquery'], function(require, exports, module) {
	var slider = function(options) {
		this.init(options);
	};
	slider.prototype = {
		init : function(options) {
			this.options 	=	$.extend({
				 	selector	:'#slider',
				 	showWay		:false,		//是否显示左右按钮
				 	showBtn		:false,		//是否显示焦点按钮
				 	showNum		:1,			//显示个数		
				 	autoTimer	:4000,		//自动播放时间
					autoPlay	:false,		//是否自动播放
					index		:0			//默认显示索引
				},options);

			this.selector	= this.options.selector;
			var selector 	= this.selector;
			var lilen 		= $(selector).find("ul li").length;
			var showNum 	= this.options.showNum;
			var lwidth 		= $(selector).find("ul li:first").width();
			this.sWidth 	= showNum * lwidth;
			var len 		= Math.ceil(lilen / showNum); 
			var index		= this.options.index;
			var that 		= this;

			this.index 		= this.options.index;
			this.len 		= len;

			//以下代码添加数字按钮和按钮后的半透明条，还有上一页、下一页两个按钮
			var showBtn 	= this.options.showBtn;
			var showWay 	= this.options.showWay;
			if(len > 1){
				if(showBtn === true){
					var btn = "<div class='btnBg'></div><div class='btn'>";
					for(var i=0; i < len; i++) {
						var ii = i+1;
						btn += "<span>"+ii+"</span>";
					}
					btn += "</div>";
					$(selector).append(btn);
					$(selector).find("div.btnBg").css("opacity",0.5);
				}

				if(showWay === true){
					var btn = "<div class='preNext pre'></div><div class='preNext next'></div>";
					$(selector).append(btn);

					//上一页、下一页按钮透明度处理
					$(selector+" .preNext").css("opacity",0.2).hover(function() {
						$(this).stop(true,false).animate({"opacity":"0.5"},300);
					},function() {
						$(this).stop(true,false).animate({"opacity":"0.2"},300);
					});

					//上一页按钮
					$(selector+" .pre").click(function() {
						index -= 1;
						if(index == -1) {index = len - 1;}
						that.show(index);
					});
				
					//下一页按钮
					$(selector+" .next").click(function() {
						index += 1;
						if(index == len) {index = 0;}
						that.show(index);
					});
				}
			}
			$(selector+" ul").css("width",$(selector).width()*len);

			var autoTimer 	= this.options.autoTimer;
			var autoPlay	= this.options.autoPlay;
			var picTimer;
			if(autoPlay === true){
			//鼠标滑上焦点图时停止自动播放，滑出时开始自动播放
				$(selector).hover(function() {
					clearInterval(picTimer);
				},function() {
					picTimer = setInterval(function() {
						that.show(index);
						index++;
						if(index == len) {index = 0;}
					},autoTimer);
				}).trigger("mouseleave");
			}
		},
		show : function(index) {
			var nowLeft = -index*(this.sWidth);
			var showBtn = this.options.showBtn;
			var selector= this.options.selector;
			$(selector+" ul").stop(true,false).animate({"left":nowLeft},300); //通过animate()调整ul元素滚动到计算出的position
			if(showBtn === true){
				$(selector+" .btn span").removeClass("on").eq(index).addClass("on"); //为当前的按钮切换到选中的效果
				$(selector+" .btn span").stop(true,false).animate({"opacity":"0.4"},300).eq(index).stop(true,false).animate({"opacity":"1"},300); //为当前的按钮切换到选中的效果
			}

		},
		pre : function() {
			this.index -= 1;
			if(this.index == -1) {
				this.index = this.len - 1;
			}
			this.show(this.index);
		},
		next : function() {
			this.index += 1;
			if(this.index == this.len) {
				this.index = 0;
			}
			this.show(this.index);
		}
	};
	return slider;
});