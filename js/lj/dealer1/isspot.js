 
define(['jquery','app/slider'],function(require, exports, module){
	var slide = require("app/slider");
	new slide({selector:".imglist",showNum:4,showBtn:false,autoPlay:true});
	$("#futures").on("click",function(){
		var valid = $(this).attr("valid");
		if(valid != 1){
			$.get("/dealer1/set_user_isspot",{isspot:1},function(json){
				if(json.valid){
					location.href= '/dealer1';
				}
			},'json');
		}else{
			alert("您没有该类货品的订货权限");
		}
	})
	$("#isspot").on("click",function(){
		var valid = $(this).attr("valid");
		if(valid != 1){
			$.get("/dealer1/set_user_isspot",{isspot:2},function(json){
				if(json.valid){
					location.href= '/dealer1';
				}
			},'json');
		}else{
			alert("您没有该类货品的订货权限");
		}
	})
})