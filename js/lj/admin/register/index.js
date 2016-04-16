 
define(['jquery'], function(require, exports, module) {
    var $submit = $('#register-submit');

    var register= function(){
        var name = $("input[name='name']").val();
        var phone= $("input[name='phone']").val();
        $.post("/register/message",{name:name,phone:phone},function(json){
            require.async('jquery/jquery.notify', function(n){
                n.message({title:"提示", text:json.message}, {expires:2000});
            });
        },'json');
    }
    var isPhone = false;
    $submit.on("click",function(){
        if(isPhone===true){
            register();
        }else{
            require.async('jquery/jquery.notify', function(n){
                n.message({title:"提示", text:"请输入正确的手机号"}, {expires:2000});
            });
        }
    })

    $("input[name='phone'").on("keyup",function(){
        var phone = $("input[name='phone']").val();
        if(!(/^1[3|4|5|7|8][0-9]\d{8}$/.test(phone))){
            isPhone = false;
        }else{
            isPhone = true;
        }
    })
});