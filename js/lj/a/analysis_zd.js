define(['jquery', 'app/pager'], function(require, exports, module) {
    var pager = require('app/pager'), api, t = location.hash, menu = $('#HDT-menu-analysis'), main = $('#HDT-main'), colors = [
   '#2f7ed8',
   '#910000',
   '#0d233a',
   '#8bbc21',
   '#1aadce',
   '#492970',
   '#f28f43',
   '#77a1e5',
   '#c42525',
   '#a6c96a'
],area1=$('select[name=area1]'),area2=$('select[name=area2]');

    var bars = function(){
        require.async(['jquery/highcharts/highcharts'], function(){
            var main = $('#HDT-main'), title = menu.find('.on').html(), categories = main.find('tr').map(function(){
                return this.getAttribute('data-name');
            }).get(), series = [], chartType = 'bar';

            var data_u = main.find('tr[data-u]').map(function(){
                return this.getAttribute('data-u')>>0;
            }).get();
            series.push({
                name    : '订货数据',
                data    : data_u
            });

            if(data_u.length > 10){
                ChartHeight = data_u.length * 20;
            }else{
                ChartHeight = 400;
            }

            $('#container').height(ChartHeight).highcharts({
                chart: {type: chartType},
                credits:{enabled:false},
                title: {text: title},
                subtitle: {text: 'Source: www.haodingtong.com'},
                xAxis: {categories: categories},
                yAxis: {min: 0,title: {text: '订数'}},
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td><td style="padding:0"><b>{point.y:f}</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: series,
                navigation: {
                    buttonOptions:{enabled:false}
                },
                colors: colors
            });

        });
    };

    var pies = function(){
        require.async(['jquery/highcharts/highcharts'], function(){
            var main = $('#HDT-main'), title = menu.find('.on').html(), categories = main.find('tr').map(function(){
                return $(this).find('td:first').html();
            }).get(), series = [];

            series.push({
                center  : ['50%', '50%'],
                type    : 'pie',
                name    : '订货数据',
                data    : main.find('tr[data-u]').map(function(){
                    return [[this.getAttribute('data-name'), parseInt( this.getAttribute('data-u') ) || 0]];
                }).get()
            });

            ChartHeight = 400;

            $('#container').height(ChartHeight).highcharts({
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false
                },
                credits:{enabled:false},
                title: {text: title},
                subtitle: {text: 'Source: www.haodingtong.com'},
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y}</b>',
                    percentageDecimals: 1,
                    changeDecimals:0
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            color: '#000000',
                            connectorColor: '#000000',
                            formatter: function() {
                                if(this.percentage > 0){
                                    var name = this.point.name.substr(0,7);
                                    return '<b>'+ name +'</b>: '+ Highcharts.numberFormat(this.percentage, 0)  +' %';
                                }
                            }
                        }
                    }
                },
                series: series,
                navigation: {
                    buttonOptions:{enabled:false}
                },
                colors : colors
            });
        });
    };

    var currentChart = 'bars', currentFunc = bars, ChartHeight = 400;


    if(t){
        t = t.replace(/^\#/, '');
    }
    if(t){
        menu.find('a[data-t='+t+']').addClass("on");
    }else{
        menu.find("a:first").addClass("on");
    }

    $('.HDT_charts_bars').on('click', function(){
        var t = currentChart ? 100 : 300;
        if(currentChart != 'bars'){
            currentFunc = bars;
            $('#container').animate({height:ChartHeight}, t, function(){
                bars();
            });
            currentChart = 'bars';
        }else{
            currentChart = '';
            $('#container').animate({height:0}, 300, function(){
                $(this).empty();
            });
        }
    });
    $('.HDT_charts_pies').on('click', function(){
        var t = currentChart ? 100 : 300;
        if(currentChart != 'pies'){
            currentFunc = pies;
            $('#container').animate({height:ChartHeight}, t, function(){
                pies();
            });
            currentChart = 'pies';
        }else{
            currentChart = '';
            $('#container').animate({height:0}, 300, function(){
                $(this).empty();
            });
        }
    });

    api = new pager('/analysis/list', {t:t,ad:1,area1:area1.val(),area2:area2.val()}, {autorun:true, aftercallback:function(html){
        if(currentChart && html){
            currentFunc.call();
        }
    }});

    var api1 = new pager('/analysis/list', {t:t,ad:1,status_val:2,area1:area1.val(),area2:area2.val()}, {autorun:true, id:"#HDT-main1", aftercallback:function(html){
        if($(html).filter('tr').length<=2){
            $('#HDT-main1').parent().hide();
        }else{
            $('#HDT-main1').parent().show();
        }
    }});

    menu.on('click', 'a[data-t]', function(e){
        var target  = e.currentTarget, t = target.getAttribute('data-t');
        menu.find('a.on').removeClass('on');
        target.className = "on";
        api.set("t", t);
        if(api1){
            api1.set("t", t);
        }
    });

    $('select[name=fliter_uid]').on('change',function(){
    	api.set('fliter_uid', this.value);
    	if(api1){
    		api1.set("fliter_uid", this.value);
        }
    });

    require.async('app/admin.select', function(select){
        select(api);
       if(api1){
           select(api1);
       }
    });

    // api.next();

});