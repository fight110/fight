define(['jquery','lj/fancybox','jquery/fancybox/jquery.fancybox'], function(require, exports, module) {
	var user_select = function () {
		this.href = "/dealer/user_select";
		this.open();
	};
	user_select.prototype = {
		init: function() {
			if($('.searchSelects').length){
				require.async(['app/selects'], function(Selects){
			        new Selects('/location/json', {dataType:'json', selector:'.searchSelects'});
			    });
			}	
			$(".selectall").on('click',function(){
				var checked = this.checked;
				$('.user_list :checkbox').not(".selectall").each(function(){
					this.checked = checked; 
				})
			});
			
			$(".reverse").on('click',function(){
				$('.user_list :checkbox').not(".selectall").each(function(){
					this.checked = !this.checked; 
				})
			});

			require.async(['jquery/jquery.pagination'], function(){
				var initPagination = function() {
					var num_entries = $("#hiddenresult ul.user_page").length;
					// 创建分页
					$("#Pagination").pagination(num_entries, {
						num_edge_entries: 1, //边缘页数
						num_display_entries: 4, //主体页数
						callback: pageselectCallback,
						items_per_page: 1, //每页显示1项
						prev_text: "前一页",
						next_text: "后一页"
					});
				};
				function pageselectCallback(page_index, jq){
					var new_content = $("#hiddenresult ul.user_page:eq("+page_index+")").clone();
						$(".user_list").empty().append(new_content); //装载对应分页的内容
					return false;
				}
				$("#hiddenresult").load('/dealer/user_list', null, initPagination);

				$(".list_type").on('click',function(){
					var that = this;
					$(".on").removeClass('on');
					$(that).addClass('on');
					var list_type = $(that).attr('list_type');
					$("#hiddenresult").load('/dealer/user_list', {list_type:list_type}, initPagination);
				})

				$('#submit').on('click',function(){
			    	var area1=$('.select-menu').find("select[name=area1]").val(),
			    		area2=$('.select-menu').find("select[name=area2]").val(),
			    		zd_id=$('.select-menu').find("select[name=zd_id]").val(),
			    		q = $('.select-menu').find("input[name=q]").val(),
			    		list_type = $(".on").attr('list_type');
		                $("#hiddenresult").load('/dealer/user_list', {area1:area1,area2:area2,zd_id:zd_id,q:q,list_type:list_type}, initPagination);
				})
			});
		},
		open: function(){
			var that = this,href = that.href;
			$.fancybox.open({ type : 'ajax', padding : 0, margin : 0, href: href, tpl: { }, autoCenter : true,
				afterShow: function() {
					that.init();
				}
			});
		},
		get_list: function(){
			var list = [];
			$("ul").find(":checked").each(function(){
                list.push($(this).val());
            })
			return list;
		},
		close: function(){
			$.fancybox.close();
		}
	};
	return user_select;
});
