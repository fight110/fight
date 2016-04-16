/* jQuery Notify UI Widget 1.5 by Eric Hynds
 * http://www.erichynds.com/jquery/a-jquery-ui-growl-ubuntu-notification-widget/
 *
 * Depends:
 *   - jQuery 1.4
 *   - jQuery UI 1.8 widget factory
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
*/

define(['jquery/jquery.notify/ui.notify.css', 'jquery/jquery.ui'], function(require, exports, module) {
    (function(d){d.widget("ech.notify",{options:{speed:500,expires:5E3,stack:"below",custom:!1,queue:!1},_create:function(){var a=this;this.templates={};this.keys=[];this.element.addClass("ui-notify").children().addClass("ui-notify-message ui-notify-message-style").each(function(b){b=this.id||b;a.keys.push(b);a.templates[b]=d(this).removeAttr("id").wrap("<div></div>").parent().html()}).end().empty().show()},create:function(a,b,c){"object"===typeof a&&(c=b,b=a,a=null);a=this.templates[a||this.keys[0]];c&&c.custom&&(a=d(a).removeClass("ui-notify-message-style").wrap("<div></div>").parent().html());this.openNotifications=this.openNotifications||0;return(new d.ech.notify.instance(this))._create(b,d.extend({},this.options,c),a)}});d.extend(d.ech.notify,{instance:function(a){this.__super=a;this.isOpen=!1}});d.extend(d.ech.notify.instance.prototype,{_create:function(a,b,c){this.options=b;var e=this,c=c.replace(/#(?:\{|%7B)(.*?)(?:\}|%7D)/g,function(b,c){return c in a?a[c]:""}),c=this.element=d(c),f=c.find(".ui-notify-close");"function"===typeof this.options.click&&c.addClass("ui-notify-click").bind("click",function(a){e._trigger("click",a,e)});f.length&&f.bind("click",function(){e.close();return!1});this.__super.element.queue("notify",function(){e.open();"number"===typeof b.expires&&0<b.expires&&setTimeout(d.proxy(e.close,e),b.expires)});(!this.options.queue||this.__super.openNotifications<=this.options.queue-1)&&this.__super.element.dequeue("notify");return this},close:function(){var a=this.options.speed;this.element.fadeTo(a,0).slideUp(a,d.proxy(function(){this._trigger("close");this.isOpen=!1;this.element.remove();this.__super.openNotifications-=1;this.__super.element.dequeue("notify")},this));return this},open:function(){if(this.isOpen||!1===this._trigger("beforeopen"))return this;var a=this;this.__super.openNotifications+=1;this.element["above"===this.options.stack?"prependTo":"appendTo"](this.__super.element).css({display:"none",opacity:""}).fadeIn(this.options.speed,function(){a._trigger("open");a.isOpen=!0});return this},widget:function(){return this.element},_trigger:function(a,b,c){return this.__super._trigger.call(this,a,b,c)}})})(jQuery);

    var template = '<div id="container" style="display:none">\
        <div id="notify-basic"><a class="ui-notify-cross ui-notify-close" href="#">x</a><h1>#{title}</h1><p>#{text}</p></div>\
        <div id="notify-confirm"><a class="ui-notify-cross ui-notify-close" href="#">x</a><h1>#{title}</h1><p>#{text}</p>\
            <p style="margin-top:10px;text-align:center"><input type="button" value="确认"/> <input type="button" value="取消"/></p>\
        </div>\
        <div id="notify-progress"><a class="ui-notify-cross ui-notify-close" href="#">x</a><h1>#{title}</h1><p>#{text}</p>\
            <p class="notify-progress">#{progress}</p>\
        </div>\
        <div id="notify-warn"><div class="icon"><img alt="icon" src="/images/dialog-warning.png"></div><a class="ui-notify-cross ui-notify-close" href="#">x</a><h1>#{title}</h1><p>#{text}</p></div>\
    </div>';
    var $template = $(template).notify({speed: 500, expires: false}).appendTo('body');
    return {
        message     : function(params, opts){
            opts = opts || {};
            var n   = $template.notify("create", 'notify-basic', params, opts);
            return n;
        },
        warn     : function(params, opts){
            opts = opts || {};
            var n   = $template.notify("create", 'notify-warn', params, opts);
            return n;
        },
        confirm     : function(params, callback, opts){
            opts = opts || {};
            var n   = $template.notify("create", 'notify-confirm', params, opts), cancel = null;

            n.widget().delegate("input","click", function(e){
                var value = e.target.value;
                if(value == "确认") {
                    callback();
                }else if(value == "取消") {
                    n.runCancel();
                }
                n.close();
            });
            n.runCancel = function() {
                if(cancel) {
                    cancel.call(n);
                }
            };
            n.cancel = function(callback) {
                cancel = callback;
            };
            return n;
        },
        progress    : function(params, opts){
            opts = opts || {};
            var n   = $template.notify('create', 'notify-progress', params, opts);
            var np  = n.widget().find('.notify-progress');
            n.update    = function(progress){
                np.html(progress);
            };
            return n;
        }
    }



});
