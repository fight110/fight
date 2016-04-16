


define(['jquery', 'jquery/jquery.tmpl', 'app/jtouch'], function(require, exports, module) {
    var Photos  = function(id, template, opts){
        if(!template.main){
            throw new Error("Photos template main is undefined");
        }
        if(!template.unit){
            throw new Error("Photos template unit is undefined");
        }
        this.template   = template;
        this.opts       = opts;
        this.main   = $(id).append($.tmpl(template.main, opts.main || {}));
        this.list   = this.main.find('ul');
        this.big    = this.main.find('.HDT-photos-big');

        var that    = this;
        
        if(opts.iseditor){
            this.on('mouseenter', 'ul img', function(e){
                var target = e.currentTarget,  isX = target.getAttribute('isX');
                if(!isX){
                    var $target = $(target), position = $target.position(), width = $target.width(),
                        x = $('<div title="点击删除" style="position:absolute;">x</div>');
                    var p = {top:position.top, left:position.left + width - 10};
                    $target.after(x);
                    x.offset(p);
                    x.on('click', function(e){
                        that.main.trigger('removeimg', target);
                        $target.parents('li').remove();
                    });
                    target.setAttribute('isX', true);
                }
            });
        }else{
            var Touches = JTouch(this.big[0]);
            Touches.on('flick',function(evt,data){
                switch(data['direction']){
                    case 'left' :
                        that.next();
                        break;
                    case 'right':
                        that.prev();
                        break;
                }
            });
        }
        
        this.on('click', 'ul img', function(e){
            var target = e.currentTarget, src = target.getAttribute('data-src'), $target = $(target);
            that.list.find('.hover').removeClass('hover');
            $target.parents('li').addClass('hover');
            that.big.attr('src', '/thumb/458/' + src);
        });
        this.list.find('img:first').trigger('click');
    };
    Photos.prototype    = {
        add     : function(json){
            var unit    = this.template.unit, list = this.list;
            $.tmpl(unit, json).appendTo(list).find('img:first').trigger('click');
        },
        on      : function(event, selector, callback){
            this.main.on(event, selector, callback);
        },
        next    : function(){
            var obj = this.list.find('.hover').next();
            if(!obj[0]){
                obj = this.list.find('li:first');
            }
            obj.find('img').trigger('click');
        },
        prev    : function(){
            var obj = this.list.find('.hover').prev();
            if(!obj[0]){
                obj = this.list.find('li:last');
            }
            obj.find('img').trigger('click');
        }
    };

    return Photos;
});



