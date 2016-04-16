
define(['jquery', 'app/keyborad.css', 'app/store'], function(require, exports, module) {
    var defaultConfig = {
        delay   : 500,
        max     : 100000,
        selector    : 'input[readonly]',
        kb_delay    : 600,
        template    : 'default',
        shortcut_key : 'keyword_shortcut',
        shortcut_timeout : 1000 * 3600 * 4,
        shortcut_separator : '-',
        shortcut_active : $('#HDT_SHORTCUT_ACTIVE').attr('active') == 1 ? true : false
    }, currentInput = null, originalInput = null, is_focus = false, restore = function(){}, $w = $(window), keyboradEvent = null, template = {};
    require('app/store');
    template['default'] = '<ul id="HDT-keyborad">\
        <div class="row kdata"><li>1</li><li>2</li><li>3</li><li>4</li><li>5</li><li>6</li><li>7</li><li>8</li><li>9</li><li>0</li><li data-key="delete">\u5220\u9664</li><li data-key="close">\u5173\u95ed</li></div>\
        </ul>';
    template['order']   = '<ul id="HDT-keyborad">\
        <div class="row shortcut"></div>\
        <div class="row kdata"><li>1</li><li>2</li><li>3</li><li>4</li><li>5</li><li>6</li><li>7</li><li>8</li><li>9</li><li>0</li><li data-key="delete" class="fr">\u5220\u9664</li></div>\
        <div class="row"><li class="keylonger" data-key="byhand">\u6309\u624b\u8ba2\u8d27</li><li data-key="setValue">10</li><li data-key="setValue">15</li><li data-key="setValue">20</li><li data-key="goup">\u2191</li><li data-key="goleft">\u2190</li><li data-key="godown">\u2193</li><li data-key="goright">\u2192</li><li data-key="save" class="keylonger">\u4fdd\u5b58</li><li data-key="close" class="fr">\u5173\u95ed</li></div>\
        </ul>';
    template['proportion']   = '<ul id="HDT-keyborad">\
        <div class="row shortcut"><li data-key="close" class="fr">关闭</li></div>\
        <div class="row kdata"><li data-key="proportion">1</li><li data-key="proportion">2</li><li data-key="proportion">3</li><li data-key="proportion">4</li><li data-key="proportion">5</li><li data-key="proportion">6</li><li data-key="proportion">7</li><li data-key="proportion">8</li><li data-key="proportion">9</li><li data-key="proportion">0</li><li data-key="proportionDelete" class="fr">\u5220\u9664</li><li data-key="save">保存</li></div>\
        </ul>';
    var Keyborad = function(selector, config){
        var that = this;
        this.event      = $({});
        this.config     = $.extend({}, defaultConfig, config);
        this.template   = template[this.config.template];
        if(!selector) selector = 'body';
        this.el     = $(selector);
        
        if(keyboradEvent === null){
            //keyboradEvent = /windows/.test(navigator.userAgent.toLowerCase()) ? 'click' : 'touchstart';
            var isAndroid = /android/.test(navigator.userAgent.toLowerCase());
            var isIpad       = /ipad/.test(navigator.userAgent.toLowerCase());
            keyboradEvent  = (isAndroid || isIpad) ? 'touchstart' : 'click' ;
            //console.log(keyboradEvent);
        }
        this.el.on(keyboradEvent, this.config.selector, function(e){
            e.preventDefault();
            e.stopPropagation();
            if(currentInput != this){
                that.focus(this);
            }
            return false;
        });
        this.el.on('focus', this.config.selector, function(){
            this.blur();
            return false;
        });
        if(this.config.template == "order" && this.config.shortcut_active){
            this.event.on('save', function(){
                that.saveShortcut();
            });
        }

       /* this.keyword_status = false; 
        $('body').on('keydown', function(e){
            if(that.keyword_status){
                that.run_keydown(that.kb, e.keyCode, e.key);
                return false;
            }
        });*/
    };
    Keyborad.prototype  = {
        run_keydown : function(kb, keyCode, key) {
            if(keyCode > 48 && keyCode < 58){ // 1 - 9
                var num = keyCode - 48 - 1;
                kb.find(".kdata li").eq(num).trigger(keyboradEvent);
            }else if(keyCode > 96 && keyCode < 106){ // 1 - 9
                var num = keyCode - 96 - 1;
                kb.find(".kdata li").eq(num).trigger(keyboradEvent);
            }else if(keyCode == 48 || keyCode == 96){ // 0
                kb.find(".kdata li").eq(9).trigger(keyboradEvent);
            }else if(keyCode == 8 || keyCode == 46){ // Backspace / Delete
                kb.find("li[data-key='delete']").trigger(keyboradEvent);  
            }else if(keyCode == 9 || keyCode == 32){ // Tab / space
                kb.find("li[data-key='goright']").trigger(keyboradEvent);
            }else if(keyCode == 37){ // Left
                kb.find("li[data-key='goleft']").trigger(keyboradEvent);
            }else if(keyCode == 38){ // Up
                kb.find("li[data-key='goup']").trigger(keyboradEvent);
            }else if(keyCode == 39){ // Right 
                kb.find("li[data-key='goright']").trigger(keyboradEvent);
            }else if(keyCode == 40){ // Down
                kb.find("li[data-key='godown']").trigger(keyboradEvent);
            }else if(keyCode == 13){
                kb.find("li[data-key='save']").trigger(keyboradEvent);
            }
        },
        focus       : function(input){
            // var offset = $(input).offset();
            // setTimeout(function(){
            //     window.scrollTo(offset.left, offset.top - 250);
            // }, 0);
            this.replaceInput(input);
            this.setFocus();
        },
        replaceInput : function(input){
            if(currentInput) currentInput.style.border = '';
            currentInput = input;
            currentInput.style.border = '1px solid blue';
            originalInput = input;
            currentInput.setAttribute('first', true);
            restore    = function(){
                currentInput.style.border = '';
            };
            return;
            var type = input.type, newInput = $(input).clone().insertAfter(input);
            input.type = "hidden";
            currentInput = newInput[0];
            currentInput.type   = "text";
            currentInput.id     = "HDT-keyborad-input";
            currentInput.setAttribute('first', true);
            originalInput = input;
            restore();
            restore    = function(){
                newInput.remove();
                input.type = type;
            };
        },
        setFocus : function(input){
            var that = this;
            if(this.isloading() === false){
                this.setloading(true);
                // setTimeout(function(){
                //     if(currentInput){
                //         var val = currentInput.value;
                //         if(that.isloading() === true){
                //             currentInput.value = val.indexOf('|') >= 0 ? val.replace('|', '') : val+'|';
                //             setTimeout(arguments.callee, that.config.delay);
                //         }else{
                //             currentInput.value = val.replace('|', '');
                //         }
                //     }
                // }, that.config.delay);
                // this.showKeyborad();
            }
            this.showKeyborad();
        },
        isloading   : function(){
            return is_focus;
        },
        setloading  : function(flag){
            is_focus = !!flag;
        },
        showKeyborad    : function(){
            this.keyword_status = true;

            var is_proportion = currentInput.getAttribute('data-is-proportion');
            if(this.kb){
                this.kb.remove();
                this.kb = null;
            }
            if(is_proportion > 0){
                this.kb = $(template['proportion']).appendTo('body').slideDown(this.config.kb_delay);
                var that = this, proportion_list = currentInput.getAttribute('data-proportion-list'), $proportion = this.kb.find('.shortcut'), proportion_ary = [];
                this.kb.on(keyboradEvent, 'li' , function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    var target = $(this).addClass('pressed');
                    setTimeout(function(){
                        target.removeClass('pressed');
                    }, 200);
                    that.keydown(this);
                    return false;
                });
                proportion_ary.push('<li data-key="noob">订货配比</li>');
                var default_proportion = this.default_proportion, default_proportion_exist = false;
                for(var i = 0, plist = proportion_list.split(';'), len = plist.length; i < len; i++){
                    var proportion = plist[i];
                    if(proportion == default_proportion){
                        default_proportion_exist = true;
                        proportion_ary.push('<li data-key="selectProportion" class="curpro">'+proportion+'</li>');
                    }else{
                        proportion_ary.push('<li data-key="selectProportion">'+proportion+'</li>');
                    }
                }
                var proportion_html = proportion_ary.join("");
                $proportion.append(proportion_html);
                if(false === default_proportion_exist){
                    this.setDefaultProportion(plist[0]);
                }
            }else{
                if(!this.kb){
                    var that = this, cancel = function(target){};
                    this.kb = $(this.template).appendTo('body').slideDown(this.config.kb_delay);
                    this.kb.on(keyboradEvent, 'li' , function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        var target = $(this).addClass('pressed');
                        setTimeout(function(){
                            target.removeClass('pressed');
                        }, 200);
                        that.keydown(this);
                        return false;
                    });
                    if(this.config.template == "order"){
                        this.config.is_byhandon = store.get('is_byhandon');
                        if(this.config.is_byhandon){
                            this.kb.find('li[data-key=byhand]').addClass('handon');
                        }
                    }
                }else{
                    this.kb.show();
                }

                if(this.config.shortcut_active){
                    var shortcut = this.kb.find('.shortcut'), shortcut_list = this.getShortcutSorted();
                    if(shortcut.length){
                        shortcut.empty();
                        var now = (new Date).getTime(), num_shortcut = 0;
                        for(var i = 0, len = shortcut_list.length; i < len; i++){
                            var scut = shortcut_list[i];
                            if(now < scut['t'] + this.config.shortcut_timeout){
                                if(num_shortcut < 10){
                                    shortcut.append('<li data-key="shortcut">'+scut['key']+'</li>');
                                }
                                num_shortcut++;
                            }else{
                                this.delShortcut(scut['key']);
                            }
                        }
                        if(num_shortcut){
                            shortcut.css('height', 86);
                            shortcut.prepend('<li data-key="noob">最近订货量</li>');
                        }else{
                            shortcut.css('height', 0);
                        }
                        // if(num_shortcut){
                        //     shortcut.append('<li class="fr" data-key="clearShortcut">清空</li>');
                        // }
                    }
                }else{
                    this.kb.find('.shortcut').css('height', 0);
                }   
            }
            
        },
        keydown     : function(key){
            var func = this[key.getAttribute('data-key')];
            if(func){
                func.call(this, key);
            }else{
                this.addValue(key);
            }
        },
        addValue    : function(key){
            if(currentInput){
                var val = currentInput.value,//.replace('|',''), 
                    first = currentInput.getAttribute('first'), 
                    newval = first ? key.innerHTML>>0 : (val>>0) * 10 + (key.innerHTML>>0), max = currentInput.getAttribute('data-max') || this.config.max;
                if(first) currentInput.removeAttribute('first');
                if(newval <= max){
                    this.setValue(newval);
                }
            }
        },
        setValue    : function(key){
            var newval  = $.isNumeric(key)  ? key : key.innerHTML;
            currentInput.value = newval;
            originalInput.value = newval;
            this.triggerChange(originalInput, this.config);
        },
        triggerChange : function(input, config){
            this.event.trigger('change', [input, config]);
        },
        close       : function(){
            if(this.kb){
                this.kb.slideUp(this.config.kb_delay);
                this.setloading(false);
                restore();
                currentInput = originalInput = null;
                restore = function(){};
                this.event.trigger('close', [originalInput, this.config]);
            }
            this.keyword_status = false;
        },
        delete : function(){
            if(currentInput){
                var val = ((currentInput.value>>0 )/ 10)>>0;//.replace('|','')
                currentInput.value = val ? val : '';
                originalInput.value = val;
                this.event.trigger('change', [originalInput, this.config]);
            }
        },
        goup    : function(key){
            var list = this.el.find(this.config.selector), size = $(originalInput).parents('tr').find(this.config.selector).length;
            for(var i = 0, len = list.length; i < len; i++){
                if(list[i] == originalInput){
                    var input = list[i - size];
                    if(input){
                        this.scrolldiff(originalInput, input);
                        this.focus(input);
                    }
                    break;
                }
            }
        },
        godown      : function(key){
            var list = this.el.find(this.config.selector), size = $(originalInput).parents('tr').find(this.config.selector).length;
            for(var i = 0, len = list.length; i < len; i++){
                if(list[i] == originalInput){
                    var input = list[i + size];
                    if(input){
                        this.scrolldiff(originalInput, input);
                        this.focus(input);
                    }
                    break;
                }
            }
        },
        goright     : function(key){
            var list = this.el.find(this.config.selector);
            for(var i = 0, len = list.length; i < len; i++){
                if(list[i] == originalInput){
                    var input = list[i+1];
                    if(input){
                        this.focus(input);
                    }
                    break;
                }
            }
        },
        goleft     : function(key){
            var list = this.el.find(this.config.selector);
            for(var i = 0, len = list.length; i < len; i++){
                if(list[i] == originalInput){
                    var input = list[i-1];
                    if(input){
                        this.focus(input);
                    }
                    break;
                }
            }
        },
        scrolldiff  : function(input1, input2){
            var scrollTop = $w.scrollTop() + $(input2).offset().top - $(input1).offset().top - 100;
            window.scrollTo(0, scrollTop);
        },
        save    : function(){
            this.event.trigger('save');
        },
        byhand : function(key){
            var $key = $(key);
            if($key.hasClass('handon')){
                $key.removeClass('handon');
                store.set('is_byhandon', false);
                this.config.is_byhandon = false;
            }else{
                $key.addClass('handon');
                store.set('is_byhandon', true);
                this.config.is_byhandon = true;
            }
        },
        shortcut : function(key){
            var list = key.innerHTML.split(this.config.shortcut_separator);
            var inputs = $(currentInput).parents('tr').find('input');
            for(var i = 0, len = list.length; i < len; i++){
                var input = inputs[i], val = list[i], currentVal;
                if(input){
                    input.value = val;
                    if(input == originalInput){
                        currentInput.value = val;
                    }
                    this.triggerChange(input);
                }
            }
        },
        getShortcut : function(){
            return store.get(this.config.shortcut_key + '-' + currentInput.getAttribute('kb-keyword')) || {};
        },
        getShortcutSorted: function(){
            var shortcut = this.getShortcut(), keys = [];
            for(var key in shortcut){
                keys.push({key:key, t:shortcut[key]});
            }
            return keys.sort(function(a, b){
                return b.t - a.t;
            });
        },
        setShortcut : function(){
            if(arguments.length){
                var string = Array.prototype.join.call(arguments, this.config.shortcut_separator), shortcut = this.getShortcut();
                shortcut[string] = (new Date).getTime();
                var key = this.config.shortcut_key + '-' + currentInput.getAttribute('kb-keyword');
                store.set(key, shortcut);
            }
        },
        delShortcut : function(key){
            var shortcut = this.getShortcut();
            delete shortcut[key];
            store.set(this.config.shortcut_key, shortcut);
        },
        clearShortcut : function(){
            store.set(this.config.shortcut_key, {});
            var shortcut = this.kb.find('.shortcut');
            shortcut.animate({height:0}, 1000, function(){
                shortcut.empty();
            });
        },
        saveShortcut : function(){
            var k = this;
            $(currentInput).parents('tr[data-color-id]').each(function(){
                var color_id = this.getAttribute('data-color-id'), list = [];
                $(this).find('input[data-product-id]').each(function(){
                    list.push(this.value||0);
                });
                k.setShortcut.apply(k, list);
            });
        },

        proportion : function(key){
            var proportion = this.getProportion(), n = key.innerHTML>>0, xnum = currentInput.getAttribute('proportion-xnum'), first = currentInput.getAttribute('first');
            var inputs = $(currentInput).parents('tr').find('input');
            if(!first && xnum){
                xnum = xnum * 10 + n;
            }else{
                currentInput.removeAttribute('first');
                xnum = n;
            }
            this.setProportionValue(inputs, proportion, xnum);
        },
        proportionDelete : function(){
            var proportion = this.getProportion(), xnum = currentInput.getAttribute('proportion-xnum');
            var inputs = $(currentInput).parents('tr').find('input');
            if(xnum){
                xnum = (xnum - xnum % 10) / 10;
            }
            this.setProportionValue(inputs, proportion, xnum || 0);
        },
        setProportionValue : function(inputs, proportion, xnum){
            for(var i = 0, len = proportion.length; i < len; i++){
                var input = inputs[i], val = proportion[i] * xnum;
                if(input){
                    input.setAttribute('proportion-xnum', xnum);
                    input.value = val;
                    if(input == originalInput){
                        currentInput.value = val;
                    }
                    this.triggerChange(input);
                }
            }
        },
        selectProportion : function(key) {
            this.setDefaultProportion(key.innerHTML);
        },
        setDefaultProportion : function(proportion) {
            var list = this.kb.find(".shortcut li").each(function(){
                if(proportion == this.innerHTML){
                    $(this).addClass('curpro');
                }else{
                    $(this).removeClass('curpro');
                }
            });
            this.default_proportion = proportion;
            currentInput.setAttribute('first', true);
        },
        getProportion : function() {
            return this.default_proportion.split(':');
        },

        on  : function(eventType, callback){
            this.event.on(eventType, callback);
        },
        off : function(eventType, callback){
            this.event.off(eventType, callback);
        },
        noob : function(){}
    };
   
    return Keyborad;
});
