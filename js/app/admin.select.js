
/*
*/
define(['jquery'], function(require, exports, module) {
    var select = function(api){
        require.async('app/selects', function(Selects){
            var area1, area2, $fliter_uid = $('#HDT-select-menu').find('[name=fliter_uid]'), fliter_user = function(){
                $.get('/user/dealerlist', {area1:area1, area2:area2}, function(json){
                    var html = '<option value="">选择店仓</option>';
                    for(var i = 0, list = json.list, len = list.length; i < len; i++){
                        html += "<option value='"+list[i].id+"'>"+list[i].name+"</option>";
                    }
                    $fliter_uid.empty();
                    $fliter_uid.append(html);
                }, 'json');
            };
            new Selects('/location/json', {dataType:'json'});
            $('#HDT-select-menu').on('change', 'select', function(e){
                var name = this.name, value = this.value;
                if(name == 'area1'){
                    api.set('area2', '', true);
                    api.set('fliter_uid', '', true);
                    area1 = value;
                    area2 = '';
                    fliter_user();
                }else if(name == 'area2'){
                    area2 = value; 
                    api.set('fliter_uid', '', true);
                    fliter_user();
                }
                api.set(name, value);
            });
            fliter_user();
        });
    };

    return select;
});