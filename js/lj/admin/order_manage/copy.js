

define(['jquery', 'app/selects'], function(require, exports, module) {
    var $search     = $('#HDT-search-form'), Selects = require('app/selects'), $copy_from = $('[name=copy_from]'), $copy_to = $('[name=copy_to]');
    new Selects('/location/json', {dataType:'json'});
});