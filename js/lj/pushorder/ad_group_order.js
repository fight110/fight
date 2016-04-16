define(['jquery' ,'app/pager','lj/pushorder/slider'], function(require, exports, module) {
	$(function(){
		$("#mini_menu").click();
	});

	var group_id 	= $('#group-list').attr('data-group-id');
	var dp_type 	= $('#HDT-select-menu').find("[name='dp_type']").val();
	var dp_type2	= $('#HDT-select-menu').find("[name='dp_type2']").val();	

	var pager   	= require('app/pager');
	var slide 		= require('lj/pushorder/slider');
	var showNum		= $("#push-show-num").attr("data-num");
	var obsilder	= null;

	var group_info 	= new pager('/pushorder/ad_group_order_info', {group_id:group_id},{autorun:false});
	var	group_list 	= new pager('/pushorder/group_order_list',{group_id:group_id,dp_type:dp_type,dp_type2:dp_type2},{autorun:true,id:'#group-list',
						aftercallback:function(html){
							var select_li = $(html).find('li.current');
							group_id = $(select_li).attr('data-id');
							var _index = $(select_li).index();
							group_info.reset('/pushorder/ad_group_order_info', {group_id:group_id},{autorun:true,id:".group-info"});
							obsilder = new slide({selector:"#group-list",showNum:showNum});						
						}
					});

	$('#group-list').on('click','.change_group',function(){
		$(this).siblings().removeClass('current');
		$(this).addClass('current');
		group_id=$(this).attr('data-id');
		group_info.set('group_id',group_id);
	})
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
	$('.push').on('click',function(){
		$.get("/pushorder/set_current_show_group_id",{group_id:group_id},function(json){
			require.async('jquery/jquery.notify', function(n){
        		n.message({title:"提示", text:json.message}, {expires:2000});
    		});
		},'json');
	})
    $(".group-nav .pre").on("click",function(){
    	obsilder !== null ? obsilder.pre() : 0 ;
    })
    $(".group-nav .next").on("click",function(){
    	obsilder !== null ? obsilder.next() : 0 ;
    })
});