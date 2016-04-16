
/*
var g = new General;
*/
define(['jquery'], function(require, exports, module) {
    var General = function(){
        this.hash       = {};
        this.target     = {};
    };
    General.prototype.add   = function(product_id, num){
        var val = this.hash[product_id] + num;
        this.set(product_id, val);
    };
    General.prototype.set   = function(product_id, flag){
        var target  = this.getTarget(product_id);
        if(target){
            this.hash[product_id]   = flag;
            if(!!flag){
                target.addClass('selected');
            }else{
                target.removeClass('selected');
            }
        }
    };
    General.prototype.getTarget = function(product_id){
        var target  = this.target[product_id];
        if(!target){
            target = $('#general' + product_id);
        }
        return target;
    }
    General.prototype.del   = function(product_id, num){
        var val = this.hash[product_id] - num;
        this.set(product_id, val);
    };

    return General;
});