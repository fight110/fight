
define(['jquery'], function(require, exports, module) {
	var wrong = $('body').attr('data-wrong-order'), 
	config = {
		td 		: 'td.HDT-order-detail',
		border 	: "2px solid #FF0000"
	};
    if(wrong){
        $(config.td).each(function(){
            var val = this.innerHTML >> 0;
            if(val >= wrong){
                this.style.border = config.border;
            }
        });
    }
    
    $('tr').each(function(){
    	var td = $(this).find(config.td), firstTd = null, lastTd = null, list = [], max = 0, num_hash = {};
    	td.each(function(){
    		var val = this.innerHTML>>0;
            if(val > max){
                max = val;
                num_hash[max] = 0;
            }
            num_hash[val] += 1;
    		if(firstTd == null && val > 0){
    			firstTd = this;
    		}
    		if(firstTd != null){
    			if(val == 0 && lastTd == null){
    				list.push(this);
    			}else{
    				if(list.length == 0){
    					firstTd = this;
    				}else{
    					lastTd = this;
    				}
    			}
    		}
    	});
    	if(list.length && lastTd){
    		for(var i = 0, len = list.length; i < len; i++){
    			list[i].style.border = config.border;
    		}
    	}
        if(num_hash[max] < 4){
            //this.style.border = config.border;
        }
    });
});