

define(['jquery' ,'app/pager','lj/pushorder/slider'], function(require, exports, module) {
	$(function(){
		$("#mini_menu").click();
	});
	var group_order = $('#group-order').attr('data-status');	
	var pager   	= require('app/pager');
	var group_info 	= null;

	var slide 	= require("lj/pushorder/slider");
	var showNum	= $("#push-show-num").attr("data-num");
	var obsilder= null;

	if(group_order==1){
		$('#HDT-FORM').submit(function(e){e.preventDefault()});
		var $ul = $('#group-list'),show_group_id = $('#HDT-group-id').attr('data-group-id');
		group_info = new pager('/pushorder/group_order_info', {group_id:show_group_id},{autorun:true,id:".group-info"});
		if(show_group_id){
			var group_order_interval = $('#group-order-interval').attr('data-interval'),current_group_id=show_group_id;
			if(group_order_interval>0){
				var interval 	= group_order_interval * 1000;
				var inter 	 	= null;
				var group_list 	= new pager('/pushorder/group_order_list',
					{group_id:show_group_id},
					{autorun:true,
						id:'#group-list',
						aftercallback:function(html){
							obsilder = new slide({selector:"#group-list",showNum:showNum});
							setTimeout(function(){
								var callback = arguments.callee;
								$.get('/pushorder/get_company_group_id', {current_group_id:current_group_id}, function(json){
									if(json.group_id != current_group_id && json.group){
			       						var hasgroup = false;
			       						current_group_id=json.group_id;
			       						$(html).find('li').each(function(){
			       							var id = $(this).attr("data-id");
			       							if(id==json.group_id){
			       								hasgroup = true;
			       								$ul.find('li').removeClass("live");
			       								$ul.find('li').eq($(this).index()).addClass("live");
			       								return ;
			       							}
			       						});
			       						if(hasgroup===false){
			       							group_list.set("group_id",json.group_id);
			       							group_info.set("group_id",json.group_id);
			       						}
									}
	    							setTimeout(callback, interval);
								},'json');
							},interval);
							$('#search_key').off("change");
							$('#search_key').on('change',function(){
								var q = this.value;
								$.get("/pushorder/search_group",{q:q},function(json){
									if(json.valid){
										var group = json.group;
										$(html).find('li').each(function(){
											var id = $(this).attr("data-id");
			       							if(id==group.id){
			       								$ul.find('li').removeClass("current");
			       								$ul.find('li').eq($(this).index()).removeClass("live");
			       								$ul.find('li').eq($(this).index()).addClass("current");
			       								return ;
			       							}
										})
									}else{
										require.async('jquery/jquery.notify', function(n){
						            		n.message({title:"提示", text:json.message}, {expires:2000});
						        		});
									}
								},'json');
							})
						}
					});
			}
		}
	}else{
		var group_id 	= $("#group-list").attr('data-group-id');
		var dp_type 	= $('#HDT-select-menu').find("[name='dp_type']").val();
		var dp_type2	= $('#HDT-select-menu').find("[name='dp_type2']").val();

		group_info = new pager('/pushorder/group_order_info', {group_id:group_id},{autorun:false});
		var	group_list = new pager('/pushorder/group_order_list',{group_id:group_id,dp_type:dp_type,dp_type2:dp_type2},{autorun:true,id:'#group-list',
							aftercallback:function(html){
								var select_li = $(html).find('li.current');
								group_id = $(select_li).attr('data-id');
								group_info.reset('/pushorder/group_order_info', {group_id:group_id},{autorun:true,id:".group-info"});
								obsilder = new slide({selector:"#group-list",showNum:showNum});
							}
						});

		$('#HDT-select-menu').on('change','.select',function(e){
			var target = e.currentTarget;
			group_list.set("group_id","",true);
			if(target.name == "dp_type") {
	             $.get('/pushorder/get_group_type_list', {dp_type:target.value}, function(html){
	                $('#HDT-select-menu').find('select[name=dp_type2]').replaceWith(html);
	            });
	            group_list.set(target.name, target.value, true);
	            group_list.set('dp_type2', '', true);
	            group_list.reload();
	        }else{
				group_list.set(target.name,target.value);
	        }
		})
		$('#HDT-FORM').submit(function(e){e.preventDefault()});
		$('#search_key').on('change',function(){
			var q = this.value;
			$.get("/pushorder/search_group",{q:q},function(json){
				if(json.valid){
					var group = json.group;
					group_list.set('group_id',group.id);
					$('#HDT-select-menu').find("[name='dp_type']").val(group.dp_type);
					$('#HDT-select-menu').find("[name='dp_type2']").val(group.dp_type2);
				}else{
					require.async('jquery/jquery.notify', function(n){
	            		n.message({title:"提示", text:json.message}, {expires:2000});
	        		});
				}
			},'json');
		})
	}

	$('#group-list').on('click','.change_group',function(){
		$(this).siblings().removeClass('current');
		if(group_order==1){
			$(this).removeClass('live');
		}
		$(this).addClass('current');
		group_id=$(this).attr('data-id');
		group_info.set('group_id',group_id);
	})

	$('body').on('productOrderChanged', function(e, product){
        group_info.reload();
    });

	require.async(['jquery/jquery.pin/jquery.pin.min'], function(){
        $(".pinned").pin();
    });
    $(".group-nav .pre").on("click",function(){
    	obsilder !== null ? obsilder.pre() : 0 ;
    })
    $(".group-nav .next").on("click",function(){
    	obsilder !== null ? obsilder.next() : 0 ;
    })
});