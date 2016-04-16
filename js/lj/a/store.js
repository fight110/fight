

define(['jquery'], function(require, exports, module) {
	var dialog = null;
    $('#HDT-main').on('click', 'a.hdt_more', function(){
    	var width = 600, tr = $(this).parents('tr'),title = tr.attr('data-title'),html = '<table class="table01"><tr><th colspan=3>'+title+'</th><td rowspan=6><img src="'+tr.attr('data-img')+'"></td></tr>';

    	tr.find('[data-uname]').each(function(){
            var uname = this.getAttribute('data-uname'), len = uname.length;
            if(len > 30){
                width = 900;
            }
    		html += '<tr><td>'+this.getAttribute('data-title')+'</td><td>'+this.innerHTML+'</td><td>'+uname+'</td></tr>';
    	});
    	html += '</table>';
    	require.async(['jquery/jquery.ui'], function(ui, Selects){
    		if(dialog){
    			dialog.html(html).dialog({width:width, autoOpen:true});
    		}else{
            	dialog = $('<div>').html(html).dialog({width:width, autoOpen:true});
    		}
        });
        return false;
    });


    var $menu = $('#HDT-select-menu');
    $menu.on('change', 'select', function(){
        var url = location.href, klist = url.split('?'), hash = {};
        if(klist[1]){
            var params = klist[1].split('&');
            for(var i in params){
                var item = params[i], kv = item.split('=');
                hash[kv[0]] = kv[1];
            }
        }
        hash[this.name] = this.value;
        var list = [];
        for(var k in hash){
            list.push(k + "=" + hash[k]);
        }
        location.href = klist[0] + "?" + list.join('&');
    });
    
    require.async('lj/fancybox',function(fancybox){
    	fancybox('','');
    });
});