
/*
new OrderTable(this, this.getAttribute('data-product-id'), function(){
    general.set(this.product_id, this.total);
});
*/
define(['jquery'], function(require, exports, module) {
    var OrderTable  = function(table, product_id, callback){
        this.init(table, product_id, callback);
    };
    OrderTable.prototype    = {
        canSave : function(){
            var result = {error:0};
            if(this.check_start_num() === false){
                result.error = 1;
                result.message = "保存失败，必须要" + this.order_start_num + "连码起订";
            }
            if(this.total && false === this.check_color_moq()){
                result.error = 1;
                result.message = "保存失败，有款色未达到起订量";
            }
            return result;
        },
        check_color_moq : function(){
            if(this.moq){
                for(var color_id in this.moq){
                    var count = this.get_color_count(color_id);
                    if(count && this.moq[color_id] > count){
                        return false;
                    }
                }
            }else{
                return true;
            }
        },
        check_start_num : function(){
            var that = this,order_start_num = this.order_start_num,flag = 1;
            for(var color_id in that.data){
                var color_tr = that.count_list.filter('[data-color-id=' + color_id + ']')
                var  start_num =0,num_v=0,start_flag = 0;
                var list = color_tr.find("input[data-color-id]").not('#HDT-keyborad-input');
                list.each(function () {
                    if(this.value>0){
                        num_v++;
                        start_num++;
                        if(start_num >= order_start_num){
                            start_flag = 1;
                        }
                    }else{
                        start_num = 0;
                    }
                });
                if(num_v == 0) start_flag = 1;
                flag = start_flag & flag ;
            }
            return flag ? true : false;
        },
        get_color_count : function(color_id){
            var hash = this.data[color_id], count = 0;
            for(var key in hash){
                count += hash[key];
            }
            return count;
        },
        set     : function(color_id, size_id, val){
            var old = this.getVal(color_id, size_id), count = this.count[color_id] >> 0;
            val = val >> 0;
            this.count[color_id]    = count - old + val;
            this.total              = this.total - old + val;
            this.data[color_id][size_id]   = val;
            this.setCount(color_id);
        },
        init    : function(table, product_id, callback){
            this.table  = $(table);
            this.data   = {};
            this.count  = {};
            this.total  = 0;
            this.count_list = this.table.find("tr[data-color-id]");
            this.count_all  = this.table.find(".HDT-count-all");
            this.start_flag = true;
            this.moq    = {};
            this.order_start_num = this.table.attr('data-start-num');
            this.order_start_pass = this.table.attr('data-start-pass');
            this.order_size_num = $(this.count_list[0]).find('input[data-size-id]').length;
            OrderTable.data[product_id]     = this;
            this.product_id = product_id;
            this.callback   = callback;

            var that = this;
            this.count_list.each(function(){
                var moq = this.getAttribute('data-color-moq'), color_id=this.getAttribute('data-color-id'); //款色起订量
                if(moq && color_id){
                    that.moq[color_id] = moq;
                }
                $(this).find('input').each(function(){
                    var color_id = this.getAttribute('data-color-id'), size_id = this.getAttribute('data-size-id'), val = this.value;
                    if(color_id && size_id){
                        that.set(color_id, size_id, val);
                    }
                });
            });
        },
        setCount    : function(color_id){
            if(color_id){
                var color_tr = this.count_list.filter('[data-color-id=' + color_id + ']'), color_count = this.count[color_id];
                color_tr.find(".HDT-order-count").html(color_count);

                if(this.order_start_num > 1){
                    if(this.order_size_num < this.order_start_num){
                        this.order_start_num = this.order_size_num;
                    }
                    if(color_count > 0){
                        var order_start_num = this.order_start_num, start_flag = 0, start_num =0, order_start_pass = this.order_start_pass, num_v = 0;
                        var list = color_tr.find("input[data-color-id]").not('#HDT-keyborad-input');
                        if(order_start_pass){
                            list = list.filter(order_start_pass);
                            if(list.length < this.order_start_num){
                                this.order_start_num = list.length;
                            }
                        }
                        list.each(function () {
                            if(this.value>0){
                                num_v++;
                                start_num++;
                                if(start_num >= order_start_num){
                                    start_flag = 1;
                                }
                            }else{
                                start_num = 0;
                            }
                        });
                        if(num_v == 0) start_flag = 1;
                    }else{
                        start_flag = 1;
                    }
                    color_tr.attr('start_flag', start_flag);
                    if(start_flag == 1){
                        this.count_list.each(function(){
                            if(0 == this.getAttribute('start_flag')){
                                start_flag = 0;
                            }
                        });
                    }
                    this.start_flag = start_flag ? true : false;
                }
            }

            this.count_all.html(this.total);
            if(this.callback){
                this.callback.call(this);
            }
        },
        getVal : function(color_id, size_id){
            var hash = this.data[color_id];
            if(!hash){
                hash = this.data[color_id] = {};
            }
            return hash[size_id]>>0;
        },
        clear   : function(product_color_id){
            for(var color_id in this.data){
                if(product_color_id){
                    if(color_id == product_color_id) {
                        var color_hash = this.data[color_id];
                        for(var size_id in color_hash){
                            this.set(color_id, size_id, 0);
                        }
                    }
                }else{
                    var color_hash = this.data[color_id];
                    for(var size_id in color_hash){
                        this.set(color_id, size_id, 0);
                    }
                }
            }
        },
        get_result: function() {
            var data = this.data, result = {}, product_id = this.product_id;
            for(var color_id in data) {
                var size_data = data[color_id];
                for(var size_id in size_data) {
                    var num = size_data[size_id], key = ['order', product_id, color_id, size_id].join('-');
                    result[key] = num;
                }
            }
            return result;
        }
    };
    OrderTable.data = {};
    OrderTable.get  = function(product_id){
        return OrderTable.data[product_id];
    }
    OrderTable.set  = function(product_id, color_id, size_id, val){
        var t = OrderTable.get(product_id);
        if(t){
            t.set(color_id, size_id, val);
        }
    };
    OrderTable.clear    = function(product_id, product_color_id){
        var t = OrderTable.get(product_id);
        if(t){
            t.clear(product_color_id);
        }
    };
    OrderTable.get_result = function(product_id) {
        var t = OrderTable.get(product_id);
        if(t) {
            return t.get_result();
        }
    };
    OrderTable.canSave  = function(product_id){
        // for(var product_id in this.data) {
            var t = this.data[product_id], result = t.canSave();
            if(result.error){
                return result;
            }
        // }
        return {error:0};
    }
    
    return OrderTable;
});