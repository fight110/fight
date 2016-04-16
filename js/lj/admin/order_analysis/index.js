

define(['jquery', 'app/selects', 'My97DatePicker/WdatePicker'], function(require, exports, module) {
    var Selects = require('app/selects');
    new Selects('/location/json', {dataType:'json'});
    $('#HDT-clear-date').on('click', function(e){
        $('input[name=date_start]').val('');
        $('input[name=date_end]').val('');
        return false;
    });

    var colors = [
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
    ], menu = $('#HDT-analysis-menu');

    var bars = function(){
        require.async(['jquery/highcharts/highcharts'], function(){
            var main = $('#HDT-main'), title = main.attr('data-name') + menu.find('.on').html(), categories = main.find('tr').map(function(){
                return this.getAttribute('data-name');
            }).get(), series = [], chartType = 'column';
            
            var data_u = main.find('tr[data-u]').map(function(){
                return this.getAttribute('data-u')>>0;
            }).get();
            series.push({
                name    : '订货数据',
                data    : data_u
            });

            if(data_u.length > 6){
                chartType = 'bar';
            }
            
            $('#container').highcharts({
                chart: {type: chartType},
                credits:{enabled:false},
                title: {text: title},
                subtitle: {text: 'Source: www.haodingtong.com'},
                xAxis: {categories: categories},
                yAxis: {min: 0,title: {text: '金额'}},
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
            var main = $('#HDT-main'), title = main.attr('data-name') + menu.find('.on').html(), categories = main.find('tr').map(function(){
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

            $('#container').highcharts({
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

    var currentChart = '', currentFunc = null;

    $('.HDT_charts_bars').on('click', function(){
        var t = currentChart ? 100 : 300;
        if(currentChart != 'bars'){
            currentFunc = bars;
            $('#container').animate({height:400}, t, function(){
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
            $('#container').animate({height:400}, t, function(){
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


});