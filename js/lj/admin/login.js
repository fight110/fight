define(['jquery'], function(require, exports, module) {
    var $submit = $('#login-submit');
    var login = function(){
        var uname = $('#uname').val(),pword = $('#pword').val();
        var data  = {uname:uname,pword:pword},domain = $('#domain').val(),
            api     = domain ? domain + "login/validate" : "login/validate",            
            // api     = "/login/validate",
            callback = function(data){
                $.ajax({url: api,type: "get",data: data,dataType: "jsonp",
                    beforeSend : function(xhr){}
                }).done(function(json){
                    if(json.valid){
                        if(json.message){
                            require.async('jquery/jquery.notify', function(n){
                                n.confirm({title:"提示", text:json.message}, function(){
                                    $.post('/login/kick_others', {}, function(){
                                        location.href = domain ? domain +'?SID='+json.SID : domain || '/';
                                    });
                                }).cancel(function(){
                                    location.href = domain ? domain +'?SID='+json.SID : domain || '/';
                                });
                            });
                        }else{
                            location.href = domain ? domain +'?SID='+json.SID : domain  || '/';
                        }
                    }else{
                        require.async('jquery/jquery.notify', function(n){
                            n.message({title:"提示", text:json.message}, {expires:2000});
                        });
                    }
                });
            };
        callback(data);
        return false;
    }
    // $form.find("input:first").trigger('focus');
    $submit.on('click', function(){
        login();
    });
    $('#uname').on('keyup',function(){
        var currKey=0,e=window.event ? window.event : arguments[0];
        currKey=e.keyCode||e.which;
        if(currKey==13){
            login();
        }
    });
    $('#pword').on('keyup',function(){
        var currKey=0,e=window.event ? window.event : arguments[0];
        currKey=e.keyCode||e.which;
        if(currKey==13){
            login();
        }
    });
    var isAndroid = /android/.test(navigator.userAgent.toLowerCase());
    // var isIpad    = /ipad/.test(navigator.userAgent.toLowerCase());
    if(isAndroid){
        $('#uname').on('focus',function(){
            $('.login_er_top').slideUp();
        })
        $('#pword').on('focus',function(){
            $('.login_er_top').slideUp();
        })

        $('#uname').on('blur',function(){
            setTimeout(function(){
                var focusId = document.activeElement.id;
                if(focusId != 'pword'&& focusId != 'uname'){
                    $('.login_er_top').slideDown();
                }
            },500)
        })
        $('#pword').on('blur',function(){
            setTimeout(function(){
                var focusId = document.activeElement.id;
                if(focusId !='uname' && focusId != 'pword'){
                    $('.login_er_top').slideDown();
                }
            },500)
        })
    }
});