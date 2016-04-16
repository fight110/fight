

define(['jquery', 'app/selects'], function(require, exports, module) {
    var $search     = $('#HDT-search-form'), Selects = require('app/selects');
    new Selects('/location/json', {dataType:'json'});

});