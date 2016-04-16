
define(['jquery'], function(require, exports, module) {
    var Install = function(){
    	this.init();
    };
    Install.prototype 	= {
    	init 	: function(){
    		this.N 	= 1;
    		this.tasklist = [];
    		this.is_running	= false;
    	},
    	getConsole 	: function(){
    		if(!this.console){
    			this.console = $('<div class="alert" style="width:80%;margin:0 auto;">').appendTo('body');
    		}
    		return this.console;
    	},
    	log 	: function(message){
    		var console = this.getConsole(), span = $("<span>" + message + "</span>"), p = $('<p>').html(this.N++ + ".").append(span);
    		console.append(p);
    		return span;
    	},
    	clear 	: function(){
    		var console = this.getConsole();
    		console.html("");
    		this.init();
    	},
    	addTask : function(taskuri, callback){
    		this.tasklist.push([taskuri, callback]);
    		this.runTask();
    	},
    	task_is_running 	: function(){
    		return this.is_running ? true : false;
    	},
    	set_is_running 	: function(flag){
    		this.is_running 	= !!flag;
    	},
    	runTask : function(){
    		if(!this.task_is_running()){
    			this.set_is_running(true);
    			var that = this, task = this.tasklist.shift();
    			if(task){
    				var taskuri = task[0], callback = task[1];
    				$.ajax({
    					url 	: taskuri,
    					success : function(json){
    						callback.call(that, json);
    						that.set_is_running(false);
    						that.runTask();
    					},
    					dataType	: 'json'
    				});
    			}
    		}
    	}
    };
    var install = new Install, newTask = function(taskuri, text){
    	var t = install.log(text);
    	install.addTask(taskuri, function(json){
            var text = json.error ? "---错误:" + json.errmsg : "完成";
    		t.append(text);
    	});
    };

    $('#HDT-button-install').on('click', function(e){
    	install.clear();
    	install.log("install....开始");
    	newTask("/install/install_sql", "初始化数据库");
    	newTask("/install/install_product", "初始化产品.详细");
    	newTask("/install/install_product_group", "初始化产品.搭配");
    	newTask("/install/install_product_display", "初始化产品.陈列");
        newTask("/install/install_product_display_new", "初始化产品.陈列(新)");

    	newTask("/install/install_user_dealer", "初始化用户.经销商");
    	newTask("/install/install_user_zongdai", "初始化用户.总代");

        newTask("/install/install_product_moq", "初始化产品.起订量");
        newTask("/install/install_rule", "初始化指引");

        newTask("/install/install_discount", "初始化特殊折扣");
        newTask("/install/install_user_target", "初始化客户分类指标");

        newTask("/install/install_product_stock", "初始化产品.库存");
    });

});