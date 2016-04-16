

define(['jquery'], function(require, exports, module) {
    var $add    = $('.HDT-add-user'),$subbtn = $('#HDT-sub-btn'),$form   = $('#HDT_sub'),tmpl = $('#tmpl-user-add').html();

    require.async(['app/selects'], function(Selects){
        new Selects('/location/json', {dataType:'json', selector:'.searchSelects'});
    });

    function openDialog (options) {
        var that        = this;
        this.options    = options;
        require.async(['jquery/jquery.ui', 'app/selects'], function(ui, Selects){
            var dialog = $('<div title="帐号编辑">').html(tmpl);
            that.dialog = dialog;
            dialog.dialog({width:600, autoOpen:true, open: function(){
                var select = new Selects('/location/json', {dataType:'json'});
                that.select = select;
                if(options.afterOpen) {
                    options.afterOpen.call(that);
                }
            }}).on('dialogclose', function(){
                dialog.dialog('destroy');
            });
            
        });
    }

    $add.on('click', function(e){
        new openDialog({});
    });

	$('body').on('click','#HDT-sub-btn',function(){
		var data    = $('#HDT_sub').serialize();
		var api     = "/dealer/add";
		$.ajax({url: api,type: "POST",data: data,dataType: "json",
            beforeSend : function(xhr){}
        }).done(function(json){
            var message = json.message ? json.message : json.valid ? '保存成功' : '保存失败';
            require.async('jquery/jquery.notify', function(n){
                n.message({title:"提示", text:json.message}, {expires:2000});
            });
            if(json.valid){
                setTimeout(function(){
                    location.reload(); 
                }, 2000);
            }
        });
		
		return false;
	});

    $('body').on('change','select[name=area1]',function(){
    	var val = $(this).val();
    	if(val==''){
    		$('.adList li').show();
    	}else{
    		$('.adList li').each(function(){
    			var area1 = $(this).attr('data-area1');
    			if(area1==val){
    				$(this).show();
    			}else{
    				$(this).hide();
    			}
    		})
    	}
    	var el 			= $('#adlistuncheck').children();
	    var len 		= el.length;
	    var allchecked 	= true;
	    for(var i=0; i<len; i++)
	    {
	        if((el[i].nodeName=="LI" && (el[i].getAttribute('data-area1')==val || !val)))
	        {
	        	var check = el[i].children[0];
	        	allchecked = allchecked && check.checked;
	        }
	    }
        $('.selectall').attr('checked',allchecked);
    })
    $('body').on('click','.selectall',function(e){//全选框
    	var target = e.currentTarget, checked = target.checked;
    	var el = $('#adlistuncheck').children();
	    var len = el.length;
	    var area1 = $('select[name=area1]').val();
    	if(checked==true){
    	    for(var i=0; i<len; i++)
    	    {
    	        if((el[i].nodeName=="LI" && (el[i].getAttribute('data-area1')==area1 || !area1)))
    	        {
    	        	var check = el[i].children[0];
    	        	if(check.checked)
    	        		check.checked = false;
    	        	else
    	        		check.checked = true;
    	        }
    	    }
    	}else{
    	    for(var i=0; i<len; i++)
    	    {
    	        if((el[i].nodeName=="LI" && (el[i].getAttribute('data-area1')==area1 || !area1)))
    	        {
    	        	var check = el[i].children[0];
    	        	if(check.checked)
    	        		check.checked = false;
    	        	else
    	        		check.checked = true;
    	        }
    	    }
    	}
    })
    /*$('body').on('click','.adList li input',function(e){
    	var target  = e.currentTarget, parent = $(target).parent('li'), checked = target.checked;
    	if(checked==true){
    		parent.appendTo('.adListCheck');
    	}else{
    		parent.appendTo('.adListUnCheck');
    	}
    })*/
    $('body').on('click', '.HDT-edit', function(e){
        var target = e.currentTarget, id = target.getAttribute('data-id');
        new openDialog({
            query : {aid:id},
            afterOpen : function() {
                var dialog = this.dialog, select = this.select;
                require.async('app/form', function(Form){
                    $.get("/dealer/user/" + id, {}, function(json){
                        Form.formfill(dialog, json.user);
                        var areaid  = json.user.area2 > 0 ? json.user.area2 : json.user.area1;
                        select.setDefaultValue(areaid);
                    }, 'json');
                });
            }
        });
        
        return false;
    });
});