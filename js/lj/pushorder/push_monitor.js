define(['jquery' ,'app/pager'], function(require, exports, module) {
	var pd_type 	= $('#HDT-select-menu').find("[name='pd_type']").val();
	var pd_type2	= $('#HDT-select-menu').find("[name='pd_type2']").val();	

	var pager   	= require('app/pager');
	var group_info 	= new pager();
	var product_info= new pager();
	var display_info 	= new pager('/pushorder/push_monitor_display_list', {pd_type:pd_type,pd_type2:pd_type2},
		{	autorun:true,
			id:"#display-list",
			aftercallback:function(){
				group_info.reset("/pushorder/push_monitor_group_list",{pd_type:pd_type,pd_type2:pd_type2},{autorun:true,id:"#group-list"});
				product_info.reset("/pushorder/push_monitor_product_list",{pd_type:pd_type,pd_type2:pd_type2},{autorun:true,id:"#product-list"});
			}
		});


	$('#HDT-select-menu').on('change','.select',function(e){
		var target = e.currentTarget;
		if(target.name == "pd_type") {
            pd_type = target.value;
            pd_type2= "";

             $.get('/pushorder/get_display_type_list', {pd_type:target.value}, function(html){
                $('#HDT-select-menu').find('select[name=pd_type2]').replaceWith(html);
            });
            display_info.set(target.name, target.value, true);
            display_info.set('pd_type2', '', true);
            display_info.reload();
        }else{
            pd_type2= target.value;

			display_info.set(target.name,target.value);
        }
	})
});