

define(['jquery', 'app/pager'], function(require, exports, module) {
    var pager   = require('app/pager'), api, t = location.hash;
    
    api = new pager('/analysis/structure', {}, {autorun:true});

});