

define(['jquery', 'app/selects', 'My97DatePicker/WdatePicker'], function(require, exports, module) {
    var Selects = require('app/selects');
    new Selects('/location/json', {dataType:'json'});

});