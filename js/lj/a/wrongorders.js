

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), $wrong = $('#HDT-wrong-order'), api = new pager('/orderlist/mywrongorders', {limit:15,wrong:$wrong.val()}, {autorun:true,message:"松开刷新"});
    
    new lazy('.foot', function(){api.next()}, {delay:100, top:0});

    $wrong.on('change', function(){
        api.set('wrong', this.value);
    });
});