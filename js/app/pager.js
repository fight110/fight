/*
var api     = new pager('/comment/list/'+product_id, {}, {autorun:true});
api.next();
api.set(key, val);
*/

define(['jquery'], function(require, exports, module) {
    var pager   = function(url, data, options){
        this.init(url, data, options);
    };
    pager.prototype.init    = function(url, data, options){
        this.end        = false;
        this.loading    = false;
        this.url        = url;
        this.data       = $.extend({p:1}, data);
        this.options    = $.extend({type:'GET', id:'#HDT-main',autorun:false, timeout:10000}, options);
        this.main       = $(this.options.id);
        if(this.options.empty){
            this.main.empty();
        }
        if(this.options.autorun){
            this.next();
        }
    };
    pager.prototype.reset       = function(url, data, options){
        if(this.request && this.request.abort) this.request.abort();
        this.main.empty();
        data.p  = 1;
        this.init(url, data, options);
    };
    pager.prototype.set     = function(key, val, notRunNow){
        var d = this.data[key];
        if(d != val){
            this.data[key]  = val;
            if(notRunNow === true) return;
            this.reset(this.url, this.data, this.options);
        }
    };
    pager.prototype.seturl  = function(url){
        if(this.url != url){
            this.url    = url;
            this.reset(this.url, this.data, this.options);
        }
    };
    pager.prototype.setdata = function(data){
        this.reset(this.url, data, this.options);
    };
    pager.prototype.next    = function(callback){
        if(this.end === true || this.loading === true){
            return false;
        }
        this.setloading(true);

        var that = this, type = this.options.type, url = this.url, data = this.data, id = this.options.id, func = arguments.callee, timeout = this.options.timeout,
        request = $.ajax({
            url         : url,
            type        : type,
            data        : data,
            dataType    : "html",
            timeout     : timeout
        });
        callback    = callback ? callback : this.options.callback;

        this.request    = request;
         
        request.done(function(html) {
            if(html==""){
                that.setend();
            }
            if(callback){
                if(false !== callback.call(that, html)){
                    $(html).appendTo(that.main);
                }
            }else{
                $(html).appendTo(that.main);
            }
            if(that.options.aftercallback){
                that.options.aftercallback.call(that, html);
            }
            that.data.p++;
            that.setloading(false);
        });
         
        request.fail(function(jqXHR, textStatus) {
            that.setloading(false);
        });
        request.error(function(jqXHR, textStatus, error){
            if(textStatus == "timeout"){
                setTimeout(function(){
                    func.call(that, callback);
                }, 500);
            }
        });

    };
    pager.prototype.setloading  = function(t){
        this.loading = !!t;
        if(!this.loading_div) this.loading_div = $("<div class='HDT_pager_div'></div>").insertAfter(this.main);
        var div = this.loading_div, message = this.options.message;
        
        if(this.loading){
            div.addClass('loading');
            if(message) div.html("");
        }else{
            div.removeClass('loading');
            if(message && this.end === false) div.html(message);
        }
        return this.loading;
    };
    pager.prototype.setend      = function(){
        this.end    = true;
    };
    pager.prototype.reload      = function(){
        this.setdata(this.data);
    };
    

    return pager;
});
