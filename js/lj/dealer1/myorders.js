

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), 
    	api_orderlist = new pager('/orderlist/myorders', {limit:15}, {autorun:true}), 
    	api_summary = new pager('/dealer1/myorders_summary', {} , {autorun:true,id:'#HDT-summary'}),
    	$menu = $('#HDT-select-menu');
    
    new lazy('.foot', function(){api_orderlist.next()}, {delay:100, top:0});

    $menu.on('change', 'select', function(e){
    	var target = e.currentTarget;
        if(target.name == "category_id") {
            $.get('/location/get_classes_list', {category_id:target.value}, function(html){
                $menu.find('select[name=classes_id]').replaceWith(html);
            });
            api_orderlist.set(target.name, target.value, true);
            api_orderlist.set('classes_id', 0, true);
            api_orderlist.reload();
            api_summary.set(target.name, target.value, true);
            api_summary.set('classes_id', 0, true);
            api_summary.reload();
        }else{
        	api_orderlist.set(this.name, this.value);
        	api_summary.set(this.name, this.value);
        }
    });

});