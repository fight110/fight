
/*
new lazy('.foot', function(){api.next()}, {delay:100, top:100});
*/
define(['jquery'], function(require, exports, module) {
    var KEY_ORDER   = 'orderlist', orderlist = function(){
        this.init();
        this.autosave_time      = 2000;
        this.$  = $({});
    };
    orderlist.prototype.add     = function(product_id, color_id, size_id, num){
        if(!num)    num = 0;
        var hash = this.list[product_id], key = [color_id, size_id].join('-'), that = this;
        if(!hash){
            hash = this.list[product_id] = {};
        }
        hash[key]   = num;
        //this.autosave();
    };
    orderlist.prototype.setDisplayGroup     = function(display_id, group_id){      
        this.extraData['display_id'] = display_id;
        this.extraData['group_id'] = group_id;
    };
    orderlist.prototype.autosave    = function(){
        if(this.autosave_timeout){
            clearTimeout(this.autosave_timeout);
        }
        var that = this;
        this.autosave_timeout   = setTimeout(function(){that.save()}, this.autosave_time);
    };
    orderlist.prototype.on      = function(event, callback){
        this.$.on(event, callback);
    };
    orderlist.prototype.trigger = function(event){
        this.$.trigger(event);
    };
    orderlist.prototype.save    = function(save_product_id){
        if(!save_product_id)    save_product_id = 0;
        this._save(save_product_id);
        this.trigger('save');
    };
    orderlist.prototype._save   = function(save_product_id){
        var that = this, list = this.list, data = {}, extraData = this.extraData, hash, key, product_id, k;
        if(save_product_id){
            product_id  = save_product_id;
            hash = list[product_id];
            for(key in hash){
                k   = ['order', product_id, key].join('-');
                data[k]     = hash[key];
            }
        }else{
            for(product_id in list){
                hash = list[product_id];
                for(key in hash){
                    k   = ['order', product_id, key].join('-');
                    data[k]     = hash[key];
                }
            }
        }
        if(extraData.display_id>0&&extraData.group_id>0){
        	data['group_id']=extraData.group_id;
            data['display_id']=extraData.display_id;
        }
        $.ajax({
            url     : "/orderlist/add",
            type    : 'post',
            data    : data,
            dataType : 'json'
        }).done(function(d) {
            var message = d.message, valid = d.valid;
            require.async('jquery/jquery.notify', function(n){
                if(valid === false){
                    n.warn({title:"保存失败", text:message}, {}); 
                }else{
                    n.message({title:"保存订单", text:message}, {expires:2000});
                    if(save_product_id){
                        that.list[save_product_id]  = {};
                    }else{
                        that.list = {};
                    }
                }
            });
            if(that.autosave_timeout){
                clearTimeout(that.autosave_timeout);
                that.autosave_timeout = null;
            }
        }).fail(function(){
            setTimeout(function(){
                // that._save(save_product_id);
            }, 300);
        });
    };
    orderlist.prototype.reset   = function(product_id){
        this.init(product_id);
    };
    orderlist.prototype.init    = function(product_id){
        if(product_id){
            this.list[product_id] = {};
        }else{
            this.list   = {};
        }
        this.extraData = {};
    };
    orderlist.prototype.remove  = function(product_id, color_id){
        var that = this;
        $.post('/orderlist/remove', {product_id:product_id, color_id:color_id}, function(json){
            var message = json.valid ? "订单取消成功" : json.message;
            require.async('jquery/jquery.notify', function(n){
                if(json.valid === false){
                    n.warn({title:"保存失败", text:message}, {});
                }else{
                    n.message({title:"取消订单",text:message}, {expires:2000});
                }
            });
            that.trigger('save');
        }, 'json');
    };
    orderlist.prototype.initOrderList   = function(product_id, callback){
        if(product_id){
            $.get('/orderlist/list/' + product_id, {}, function(json){
                callback.call(null, json);
            }, 'json');
        }
    };
    orderlist.prototype.initDisplayOrderList    = function(display_id, callback){
        if(display_id){
            $.get('/orderlist/list_by_display/' + display_id, {}, function(json){
                callback.call(null, json);
            }, 'json');
        }
    };
    orderlist.prototype.initGroupOrderList      = function(group_id, callback){
        if(group_id){
            $.get('/orderlist/list_by_group/' + group_id, {}, function(json){
                callback.call(null, json);
            }, 'json');
        }
    };
    orderlist.prototype.initShowOrderList       = function(show_id, callback){
        if(show_id){
            $.get('/orderlist/list_by_show/' + show_id, {}, function(json){
                callback.call(null, json);
            }, 'json');
        }
    }
    orderlist.prototype.setmini     = function(){
        this.mini = true;
    };



    return new orderlist;
});