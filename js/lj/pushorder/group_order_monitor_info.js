define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy        = require('app/lazy');
    var pager       = require('app/pager');
    var display_id  = $(".display-id").attr("data-id"); 

    var api         = new pager('/pushorder/group_order_monitor_list', {display_id:display_id}, {autorun:true,id:"#group-info"});
    var pc_api      = new pager('/pushorder/group_order_monitor_pc_list', {display_id:display_id}, {autorun:true});

    // new lazy('.foot', function(){api.next()}, {delay:100, top:100});
    $("button").on("click",function(){
        var data = $(this).attr("data");
        $.get("/pushorder/display_next",{display_id:display_id,f:data},function(json){
            if(json.valid){
                location.href = location.href.replace(/\d/,json.id);
            }else{
                require.async('jquery/jquery.notify', function(n){
                    n.message({title:"提示", text:json.message}, {expires:2000});
                });
            }
        },'json');
    })

    $('#HDT-FORM').submit(function(e){
        e.preventDefault()
        var bianhao     = $("#search_key").val();
        if(bianhao){
            $.get("/pushorder/display_by_bianhao",{bianhao:bianhao},function(json){
                if(json.display_id){
                    location.href = location.href.replace(/\d/,json.display_id);
                }else{
                    require.async('jquery/jquery.notify', function(n){
                        n.message({title:"提示", text:"未找到对应陈列"}, {expires:2000});
                    });
                }
            },'json')
        }
    });
});