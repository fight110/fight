define(['jquery'], function(require, exports, module) {
	var mulit_user_change = function () {
		this.href = "/dealer1/mulit_user_change";
		this.init();	
	};
	mulit_user_change.prototype = {
		init: function() {
			var href = this.href;
			$.fancybox.open({ type : 'ajax', padding : 0, margin : 0, href: href, tpl: { },
				afterShow: function() {}
			});
		}
	};

	return mulit_user_change;
});
