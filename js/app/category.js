

define(['jquery/jquery.ui'], function(require, exports, module) {
    require('jquery/jquery.ui');

    var Category    = function(option){
        this.option = option;
        this.result = {};
        this.catelist = [];
        this.callback = {};
        this._init();
    };
    Category.prototype  = {
        _init       : function(){
            var that = this, html = '<span class=selfuzu><select size=10 class=selkuang></select><select size=10 class=selkuang></select><select size=10 class=selkuang><input type="checkbox">设为默认</span>';
            this.dialog = $('<div>').dialog({
                width   : 750,
                height  : 350,
                buttons: [
                    { text: "确定", click: function() { that.runCallback('sure'); $(this).dialog("close"); } },
                    { text: "取消", click: function() { $(this).dialog("close"); } }
                ]
            }).append(html);
            this.select = this.dialog.find('select');
            for(var i = 0, len = this.select.length; i < len; i++){
                (function(){
                    var n = i, select = that.select[n];
                    $(select).on('change', function(){
                        that.select.filter(':gt('+n+')').empty();
                        if(n+1<len){
                            that.initSelect(that.select[n+1], this.value);
                        }
                        that.result.id      = this.value;
                        that.result.name    = that.getName(this.value);
                    });
                })();
            }
            this.initSelect(this.select[0], 0);
        },
        getName     : function(id){
            var name = this.dialog.find('option:selected').map(function(){
                return this.getAttribute('data-name');
            }).get().join(' > ');
            return name;
        },
        initSelect  : function(select, id){
            var that = this, list = this.catelist[id], callback = function(list){
                var html = '';
                for(var i = 0, len = list.length; i < len; i++){
                    html += '<option value="'+list[i].id+'" data-name="'+list[i].name+'">'+list[i].name+'</option>';
                }
                $(select).append(html);
            };
            if(list){
                callback.call(this, list);
            }else{

                $.get(this.option.url + id, {}, function(json){
                    callback.call(that, json.list);
                    that.catelist[id]   = json.list;
                }, 'json');
            }
        },
        show        : function(){
            this.dialog.dialog('open');
            return this;
        },
        getResult   : function(){
            this.result.isDefault   = this.dialog.find('input:checked').length;
            return this.result;
        },
        addCallback : function(key, callback){
            this.callback[key]  = callback;
        },
        runCallback : function(key){
            var result = this.getResult();
            this.callback[key].call(this, result);
            if(result.isDefault){
                var url     = this.option.urlDefault;
                $.post(url, result);
            }
        }
    };

    return {
        getInstance : function(name){
            var instance    = this.instances[name];
            if(instance)    return instance;

            var config      = this.config[name];
            if(!config) throw "init Category[" + name + "] is not defined";
            return this.instances[name] = new Category(config);
        },
        instances   : {},
        config      : {
            'category'  : {
                url         : "/category/json/",
                urlDefault  : "/setting/defaultCategory/"
            },
            'location'  : {
                url         : "/location/json/",
                urlDefault  : "/setting/defaultLocation/"
            }
        }
    };
});



