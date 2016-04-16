

define(['jquery', 'app/pager', 'app/lazy'], function(require, exports, module) {
    var lazy = require('app/lazy'), pager   = require('app/pager'), api, $main = $('#HDT-main'), $menu = $('#HDT-menu-summary');

    var DataSummary = function(){
        this.hash_x = {};
        this.hash_y = {};
        this.x      = [];
        this.y      = [];
        this.d      = {};
    };
    DataSummary.prototype.add = function(x, y, d){
        if(!this.hash_x[x]){
            this.hash_x[x]  = 1;
            this.x.push(x);
            this.d[x]   = {};
        }
        if(!this.hash_y[y]){
            this.hash_y[y]  = 1;
            this.y.push(y);
        }
        this.d[x][y]    = d;
    };
    DataSummary.prototype.getSeries = function(){
        var result = [];
        for(var i = 0, lx = this.y.length; i < lx; i++){
            var name = this.y[i], series = {name:name, data:[]};
            for(var j = 0, ly = this.x.length; j < ly; j++){
                series.data.push(this.d[this.x[j]][name]>>0);
            }
            result.push(series);
        }
        return result;
    };
    DataSummary.prototype.getData = function(){
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
   '#a6c96a',

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
],
            result = [];
        for(var i = 0, lx = this.x.length; i < lx; i++){
            var name = this.x[i], categories = [], data = [], series = {name:name,color:colors[i],y:0, drilldown:{categories:categories, data:data}};
            for(var j = 0, ly = this.y.length; j < ly; j++){
                var d = this.d[name][this.y[j]]>>0;
                categories.push(this.y[j]);
                data.push(d);
                series.y += d;
            }
            result.push(series);
        }
        return result;
    };
    var bars    = function(){
        require.async(['jquery/highcharts/highcharts'], function(){
            var title   = "万能分析", title_y = "订数", summary = new DataSummary;
            $main.find('tr[data-d]').each(function(){
                var g1 = this.getAttribute('data-g1'), g2 = this.getAttribute('data-g2'), d = this.getAttribute('data-d');
                summary.add(g1, g2, d>>0);
            });

            $('#container').highcharts({
                chart: {type: 'column'},
                title: {text: title},
                xAxis: {categories: summary.x},
                yAxis: {
                    min: 0,
                    title: {text: title_y},
                    stackLabels: {
                        enabled: true,
                        style: {
                            fontWeight: 'bold',
                            color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                        }
                    }
                },
                credits:{enabled:false},
                legend: {
                    align: 'right',
                    verticalAlign: 'top',
                    y: 20,
                    floating: true,
                    backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColorSolid) || 'white',
                    borderColor: '#CCC',
                    borderWidth: 1,
                    shadow: false
                },
                tooltip: {
                    formatter: function() {
                        return '<b>'+ this.x +'</b><br/>'+this.series.name +': '+ this.y +'<br/>'+'总计: '+ this.point.stackTotal;
                    }
                },
                plotOptions: {
                    column: {
                        stacking: 'normal'
                    }
                },
                series: summary.getSeries()
            });
        });
    };

    var pies = function(){
        require.async(['jquery/highcharts/highcharts'], function(){
            var title   = "万能分析", title_y = "订数", summary = new DataSummary;
            $main.find('tr[data-d]').each(function(){
                var g1 = this.getAttribute('data-g1'), g2 = this.getAttribute('data-g2'), d = this.getAttribute('data-d');
                summary.add(g1, g2, d>>0);
            });

            var categories = summary.x,
            data = summary.getData();

            var browserData = [];
            var versionsData = [];
            for (var i = 0; i < data.length; i++) {
                browserData.push({
                    name: categories[i],
                    y: data[i].y,
                    color: data[i].color
                });
                for (var j = 0; j < data[i].drilldown.data.length; j++) {
                    var brightness = 0.2 - (j / data[i].drilldown.data.length) / 5 ;
                    versionsData.push({
                        name: data[i].drilldown.categories[j],
                        y: data[i].drilldown.data[j],
                        color: Highcharts.Color(data[i].color).brighten(brightness).get()
                    });
                }
            }


            $('#container').highcharts({
                chart: {type: 'pie'},
                title: {text: title},
                credits:{enabled:false},
                yAxis: {
                    title: {text: title_y}
                },
                plotOptions: {
                    pie: {
                        shadow: false,
                        center: ['50%', '50%']
                    }
                },
                tooltip: {
                    formatter: function() {
                        return '<b>'+ this.key +'</b><br/>'+'总计: ' + this.y + '<br/>百分比: ' + Highcharts.numberFormat(this.percentage, 0) +'%' ;
                    }
                },
                series: [{
                    name: '1',
                    data: browserData,
                    size: '60%',
                    dataLabels: {
                        formatter: function() {
                            return this.y > 5 ? this.point.name : null;
                        },
                        color: 'white',
                        distance: -30
                    }
                }, {
                    name: '2',
                    data: versionsData,
                    size: '80%',
                    innerSize: '60%',
                    dataLabels: {
                        formatter: function() {
                            return this.percentage > 1 ? '<b>'+ this.point.name +':</b> '+ Highcharts.numberFormat(this.percentage, 0) +'%'  : null;
                        }
                    }
                }]
            });
        });
    }

    var currentChart = 'pies', currentFunc = pies, data = {};

    $main.on('change', 'select,input', function(e){
        api.set(this.name, this.value);
        data[this.name] = this.value;
    });
    $(window).on('hashchange', function(e){
        var hash = location.hash.replace('#', ''), k = hash.split(','), data = api ? api.data : {};
        data['group1']  = k[0];
        data['group2']  = k[1];
        if(api){
            api.setdata(data);
        }else{
            api = new pager('/analysis/summary', data, {autorun:true, aftercallback:function(html){
                if(currentChart && html){
                    currentFunc.call();
                }
            }});
        }
        $menu.find('.on').removeClass('on');
        $menu.find('[data-t]').each(function(){
            if(this.getAttribute('data-t') == hash){
                this.className = 'on';
            }
        });
    }).trigger('hashchange');

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

    require.async('app/admin.select', function(select){
        select(api);
    });

    api.next();

    window.mydownload = function(){
        var $fliter_uid  = $('select[name="fliter_uid"]'), t = 0;
        $fliter_uid.find('option').each(function(){
            if(this.value){
                var value = this.value;
                setTimeout(function(){
                    $('<iframe>').appendTo('body').attr('src', '/analysis/summary?download=1&fliter_uid='+value);
                }, t+=500);
            }
        });
    };


});