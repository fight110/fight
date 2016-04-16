define(['jquery', 'app/pager','app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'),pager = require('app/pager'),api = new pager('/analysis/dynamic_ranking_table', {}, {autorun:true});
    new lazy('.foot', function(){api.next()}, {delay:100, top:0});
    var isScroll   = $("#isScroll").val()   ? $("#isScroll").val()   : 1;      //默认开启滚动排行榜
    if(isScroll==1){
        var scrollTime = $("#scrollTime").val() ? $("#scrollTime").val() : 2000;   //默认2s滚动一次进度条
        var reloadTime = $("#reloadTime").val() ? $("#reloadTime").val() : 1800000;//默认30分钟刷新一次排名
        var moveY      = $("#moveY").val()      ? $("#moveY").val()      : 60;     //默认一次滚动30px
        var y=0,y2=0,y3=1;
        var t1=setInterval(function(){
            y += moveY;
            y2 = window.scrollY;
            if(y2!=y3){
                y3=y2;
            }else{
                y=0;
            }
            window.scrollTo(0,y);
        },scrollTime);
        var t2=setTimeout(function(){
            location.reload();
        },reloadTime);
    }
});