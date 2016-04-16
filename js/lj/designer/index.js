 define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
 	var lazy = require('app/lazy'), pager   = require('app/pager'), $search = $('#HDT-FORM input'); 
    var data = {};
    $('#HDT-select-menu').find('select').each(function(){
        data[this.name] = this.value;
    });
    data['q']  = $search.val();
    data['view'] =  "T";
    var api = new pager('/product/list', data , {autorun:true});

    new lazy('.foot', function(){api.next()}, {delay:100, top:100});

 	$('#HDT-select-menu').on('change', 'select', function(e){
        var target = e.currentTarget;
        if(target.name == "category_id") {
            $.get('/location/get_classes_list', {category_id:target.value}, function(html){
                $menu.find('select[name=classes_id]').replaceWith(html);
            });
            api.set(target.name, target.value, true);
            api.set('classes_id', 0, true);
            api.reload();
        }else{
        	api.set(target.name, target.value);
        }
    });
    $search.on('keyup', function(){
        api.set('q', this.value);
    });
    $search[0].onchange = function(){};
 })
