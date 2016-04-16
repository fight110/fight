
/*
多级联动select
@params : json, opt
    @json   : json格式的数据 按照数组格式 每一个元素必须含有 id, pid, name
    @opt    : 参数
        value       : 默认选中的元素的id 即为结果
        selector    : 下拉select的容器选择器 默认为.Selects 容器内需要有select和input:hidden 
        dataType    : 数据来源 [array|json] 默认array 即为本地的数组 如果为json时@json 需要为一个json的http地址 通过get方式获取
*/

define(['jquery'], function(require, exports, module) {
    var Selects     = function(json, opt){
        var that    = this;
        this.opt    = $.extend({selector:'.Selects', dataType:'array'}, opt);
        this.tree   = [];
        this.parent = {};
        selector    = $(this.opt.selector);
        this.select = selector.find('select');
        this.input  = selector.find('input:hidden');
        var value   = selector[0].getAttribute('data-default');
        if(value){
            this.opt.value  = value;
        }
        switch (this.opt.dataType) {
            case 'json' : 
                $.get(json, function(json){
                    that.init(json);
                }, 'json');
                break;
            default : this.init(json);
        }
    };
    Selects.prototype.initSelect    = function(n, pid){
        var html, list = this.tree[pid], select = this.select[n], $select = $(select);
        $select.empty();
        if(!list || !select) return false;
        if(list[0].pid==0)
        	html = '<option value="">销售大区</option>';
        else
        	html = '<option value="">二级区域</option>';
        for(var i = 0, len = list.length; i < len; i++){
            html += '<option value="'+list[i].id+'" data-name="'+list[i].name+'">'+list[i].name+'</option>';
        }
        $select.append(html);
    };
    Selects.prototype.add   = function(json){
        var pid = json.pid, list = this.tree[pid];
        if(!list){
            this.tree[pid]  = list = [];
        }
        list.push(json);
        this.parent[json.id] = pid;
    };
    Selects.prototype.setDefaultValue   = function(value){
        var pid = this.parent[value], n = 0;
        if(pid != 0 && pid != undefined){
            n = this.setDefaultValue(pid);
        }
        var select = this.select[n];
        if(select){
            $(select).val(value).trigger('change');
        }
        return n + 1;
    };
    Selects.prototype.init  = function(json){
        var input = this.input, select = this.select, n = 0, that = this, value = this.opt.value;
        for(var i = 0, len = json.length; i < len; i++){
            this.add(json[i]);
        }
        this.initSelect(0, 0);
        select.each(function(){
            this.setAttribute('n', n++);
        }).on('change', function(e){
            var target  = e.currentTarget, n = target.getAttribute('n');
            input.val(target.value);
            that.initSelect(+n+1, target.value);
        });

        if(value){
            this.setDefaultValue(value);
        }
    };

    return Selects;
});



