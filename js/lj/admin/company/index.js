

define(['jquery'], function(require, exports, module) {
    require.async('app/upload.jquery', function(FileUpload){
        FileUpload(function(){
            $('#fileupload').fileupload({
                dataType: 'json',
                done: function (e, data) {
                    $.each(data.result.files, function (index, file) {
                        var html = "<img src='/thumb/75/"+file.mediumUrl+"' width=75>";
                        $('#FD-upload-img').html(html).next('input:hidden').val(file.mediumUrl);
                    });
                }
            });
        });
    });
    
    
    $('body').on('click', '.menu_edit', function(e){
    	$('.ui-dialog').remove();
    	var target = e.currentTarget, type = target.getAttribute('data-type');
    	$.getJSON('/menu/control', {type:type}, function(result){
    		if(result.html!=''){
       		 require.async(['jquery/jquery.ui'], function(ui){
       			var content = result.html+'<div><input type="button" value="保存" class="saveMenu" ></div>'
 	            var dialog = $('<div>').html(content).dialog({width:800, autoOpen:true});
 	        });
    		}else{
    			alert('参数错误!');
    		}
		});     
    });
    
    $('body').on('click','.saveMenu',function(e){
    	var menu = document.getElementsByName('menuId');
    	var sel = [];
    	var notSel = [];
        for(var i=0,len=menu.length; i<len; i++){
        	if(menu[i].checked==true){
        		sel.push(menu[i].value);
        	}else{
        		notSel.push(menu[i].value);
        	}      	        
        }
  	    $.ajax({
    	        type: "POST",
    	        url: "/menu/save",
    	        dataType:"json",
    	        data: {
    	            "sel":sel,
    	            "notSel":notSel
    	        },
    	            success: function(data) {
    	            	if(data.success==1){
    	            		alert('更新成功！');
    	            	}
    	            	$('.ui-dialog').remove();
    	            },
    	    }); 
    	    
    })
    $('.ex_set').on('click',function(){
        $.get('/company_config/get_classes', {}, function(json){
            var list = json.list,len = list.length,html='<div class="ex-alert">';
            for(var i=0;i<len;++i){
                html += '<li class="classes"><input type="checkbox" name="classes" text="'+list[i].keywords.name+'"value="'+list[i].keyword_id+'"/>'+list[i].keywords.name+"</li>";
            }
            html    +=  '<div class="clear"></div><input type="button" id="HDT-sub-btn" value="保存" /></div>';
            require.async(['jquery/jquery.ui'], function(ui){
                var dialog = $('<div title="不计入指标小类编辑">');
                dialog.html(html).dialog({width:450,autoOpen:true, open: function(){
                    $('body').on('click','#HDT-sub-btn',function(){
                            dialog.dialog('close');
                    })
                }}).on('dialogclose', function(){
                    var value=[],name=[];
                    $(this).find("input[name='classes']").each(function(){
                        if(this.checked){
                            value.push(this.value);
                            name.push($(this).attr('text'));
                        }
                    });
                    $(".ex_classes").val(value.join(','));
                    $(".ex_name").val(name.join(','));
                });
            });
        },'json');
    })
    $(".set_timestamp").on("click",function(){
        var myDate = new Date(); 
        console.log(myDate);
        $(".timestamp").val(myDate.getTime());
    })
    $(".tab-div").on("click",function(){
        var divId = $(this).attr("data");
        var siblingDiv = $(this).addClass("on").siblings(".on").removeClass("on").first().attr("data");
        $("#"+siblingDiv).fadeOut("fast",function(){
            $("#"+divId).fadeIn("fast");
        }); 
    })
});