

define(['jquery', 'app/pager'], function(require, exports, module) {
    var pager   = require('app/pager'), t = location.hash, menu = $('#HDT-menu-analysis'), $main = $('#HDT-main'), colors = [
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
], stype=1;

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
        var colors = Highcharts.getOptions().colors,
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

    var bars = function(){
        require.async(['jquery/highcharts/highcharts'], function(){
            var main = $('#HDT-main'), title = menu.find('.on').html(), categories = main.find('tr').map(function(){
                return this.getAttribute('data-name');
            }).get(), series = [], chartType = 'bar', total = 0;//'column';

            if(stype!=1){
                var data_u = main.find('tr[data-p]').map(function(){
                    var d  = this.getAttribute('data-p')>>0;
                    total += d;
                    return d;
                }).get();
            }else{
                var data_u = main.find('tr[data-u]').map(function(){
                    var d  = this.getAttribute('data-u')>>0;
                    total += d;
                    return d;
                }).get();
            }

            // var target = [], newCategory = [], others = [], data_u_length = data_u.length, min_percent = 0.3 / data_u_length;
            // console.log(min_percent)
            // if(data_u_length > 10 && total) {
            //     for(var i = 0, len = data_u.length; i < len; i++) {
            //         var d = data_u[i], c = categories[i], percent = d / total;
            //         if(percent > min_percent) {
            //             target.push(d);
            //             newCategory.push(c);
            //         }else{
            //         console.log(percent)
            //             others.push([c,d]);
            //         }
            //     }
            //     if(others.length) {
            //         var others_num = 0, others_name;
            //         for(i = 0, len = others.length; i < len; i++) {
            //             others_num += others[i][1];
            //         }
            //         target.push(others_num);
            //         newCategory.push("其他");
            //         data_u = target;
            //         categories = newCategory;
            //     }
            // }
            series.push({
                name    : '订货数据',
                data    : data_u
            });
            // var flag_r = false, data_r = main.find('tr[data-r]').map(function(){
            //     var total = this.getAttribute('data-exp'),
            //         r = parseInt( this.getAttribute('data-r') ) || 0;
            //     if(r) flag_r = true;
            //     return r;
            // }).get();
            // if(flag_r && data_r.length){
            //     series.push({
            //         name    : '公司指引',
            //         data    : data_r
            //     });
            // }
            // var flag_b = false, data_b = main.find('tr[data-b]').map(function(){
            //     var b = parseInt( this.getAttribute('data-b')) || 0;
            //     if(b) flag_b = true;
            //     return b;
            // }).get();
            // if(flag_b && data_b.length){
            //     series.push({
            //         name    : '自定预算',
            //         data    : data_b
            //     });
            // }

            if(data_u.length > 10){
                ChartHeight = data_u.length * 20;
                if(ChartHeight < 400) {
                    ChartHeight = 400;
                }
            }else{
                ChartHeight = 400;
            }

            $('#container').height(ChartHeight).highcharts({
                chart: {type: chartType},
                credits:{enabled:false},
                title: {text: title},
                subtitle: {text: 'Source: www.haodingtong.com'},
                xAxis: {categories: categories},
                yAxis: {min: 0,title: {text: (stype!=1?'金额':'订数')}},
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td><td style="padding:0"><b>{point.y:f}</b>'+(stype!=1?' 元':' 件')+'</td></tr>',
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

            if(stype==1){
                series.push({
                    center  : ['50%', '50%'],
                    type    : 'pie',
                    name    : '订货数据',
                    data    : main.find('tr[data-u]').map(function(){
                        return [[this.getAttribute('data-name'), parseInt( this.getAttribute('data-u') ) || 0]];
                    }).get()
                });
                }else{
            series.push({
                center  : ['50%', '50%'],
                type    : 'pie',
                name    : '订货数据',
                data    : main.find('tr[data-p]').map(function(){
                    return [[this.getAttribute('data-name'), parseInt( this.getAttribute('data-p') ) || 0]];
                }).get()
            });
            }
            // var flag_r = false, data_r = main.find('tr[data-r]').map(function(){
            //     var r =  parseInt( this.getAttribute('data-r') * this.getAttribute('data-budget') / 100 ) || 0;
            //     if(r) flag_r = true;
            //     return [[this.getAttribute('data-name'), r]];
            // }).get();
            // if(flag_r && data_r.length){
            //     series.push({
            //         center  : ['90%', '25%'],
            //         type    : 'pie',
            //         name    : '公司指引',
            //         data    : data_r
            //     });
            //     series[0]['center'][0] = '40%';
            // }
            // var flag_b = false, data_b = main.find('tr[data-b]').map(function(){
            //     var b = parseInt( this.getAttribute('data-b') * this.getAttribute('data-budget') / 100 ) || 0
            //     if(b) flag_b = true;
            //     return [[this.getAttribute('data-name'), b]];
            // }).get();
            // if(flag_b && data_b.length){
            //     series.push({
            //         center  : ['90%', '75%'],
            //         type    : 'pie',
            //         name    : '自定预算',
            //         data    : data_b
            //     });
            //     series[0]['center'][0] = '40%';
            // }

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
                    pointFormat: '{series.name}: <b>{point.y}</b>'+(stype!=1?' 元':' 件')+'',
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

    var bars2    = function(){
        require.async(['jquery/highcharts/highcharts'], function(){
            var title   = "万能分析", title_y = (stype==1?"订数":'金额'), summary = new DataSummary;
            if(stype==1){
            $main.find('tr[data-d]').each(function(){
                var g1 = this.getAttribute('data-g1'), g2 = this.getAttribute('data-g2'), d = this.getAttribute('data-d');
                summary.add(g1, g2, d>>0);
            });}else{
                $main.find('tr[data-p]').each(function(){
                    var g1 = this.getAttribute('data-g1'), g2 = this.getAttribute('data-g2'), d = this.getAttribute('data-p');
                    summary.add(g1, g2, d>>0);
                });
            }

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

    var pies2 = function(){
        require.async(['jquery/highcharts/highcharts'], function(){
            var title   = "万能分析", title_y = (stype==1?"订数":'金额'), summary = new DataSummary;
            if(stype==1){
            	$main.find('tr[data-d]').each(function(){
                    var g1 = this.getAttribute('data-g1'), g2 = this.getAttribute('data-g2'), d = this.getAttribute('data-d');
                    summary.add(g1, g2, d>>0);
                });
            }else{
            	$main.find('tr[data-p]').each(function(){
                    var g1 = this.getAttribute('data-g1'), g2 = this.getAttribute('data-g2'), d = this.getAttribute('data-p');
                    summary.add(g1, g2, d>>0);
                });
            }
            

            var categories = summary.x,
            data = summary.getData();
            var browserData = [];
            var versionsData = [];
            for (var i = 0; i < data.length; i++) {
                browserData.push({
                    name: {'showname':categories[i]},
                    y: data[i].y,
                    color: data[i].color
                });
                for (var j = 0; j < data[i].drilldown.data.length; j++) {
                    var brightness = 0.2 - (j / data[i].drilldown.data.length) / 5 ;
                    versionsData.push({
                        name: {'showname':data[i].drilldown.categories[j],'subtotal':data[i].y},
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
                        return '<b>'+ this.key.showname +'</b><br/>'+'总计: ' + this.y + '<br/>百分比: ' + (this.key.subtotal?(Math.round(this.y/this.key.subtotal*100)+'% ('+Highcharts.numberFormat(this.percentage, 0)+'%)'):(Highcharts.numberFormat(this.percentage, 0)+'%'))  ;
                    }
                },
                series: [{
                    name: '1',
                    data: browserData,
                    size: '60%',
                    dataLabels: {
                        formatter: function() {
                            return this.y > 5 ? this.point.name.showname : null;
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
                           // return this.percentage > 1 ? '<b>'+ this.point.name +':</b> '+ Highcharts.numberFormat(this.percentage, 0) +'%'  : null;
                        	return this.percentage > 1 ? '<b>'+ this.point.name.showname +':</b> '+ (Math.round(this.y/this.key.subtotal*100)) +'%'+' ('+Highcharts.numberFormat(this.percentage, 0)+'%)'  : null;
                        }
                    }
                }]
            });
        });
    }

    var currentChart = 'bars', currentFunc = bars, ChartHeight = 400, currentShow = 'step1', clickBar='';

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
        clickBar = 1;
        if(currentChart != 'bars'){
            if(currentShow == 'step1'){
        		currentFunc = bars;
        	}else{
        		currentFunc = bars2;
        	}
            $('#container').animate({height:ChartHeight}, t, function(){
                if(currentShow == 'step1'){
                	bars();
            	}else{
            		bars2();
            	}
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
        clickBar = 1;
        if(currentChart != 'pies'){
        	if(currentShow == 'step1'){
        		currentFunc = pies;
        	}else{
        		currentFunc = pies2;
        	}

            $('#container').animate({height:ChartHeight}, t, function(){
                if(currentShow == 'step1'){
                	pies();
            	}else{
            		pies2();
            	}
            });
            currentChart = 'pies';
        }else{
            currentChart = '';
            $('#container').animate({height:0}, 300, function(){
                $(this).empty();
            });
        }
    });

    var api = new pager('/analysis/list_all', {t:t}, {autorun:false, aftercallback:function(html){
        if(currentChart && html && $('#HDT-main tr[data-u]').length){
            currentFunc.call();
        }
    }});

    var api2 = new pager('/analysis/summary', {} , {autorun:false, aftercallback:function(html){
        if(currentChart && html && $('#HDT-main tr[data-d]').length){
            currentFunc.call();
        }
    }});

    api.next();

    menu.on('click', 'a[data-t]', function(e){
        var target  = e.currentTarget, t = target.getAttribute('data-t');
        menu.find('a.on').removeClass('on');
        target.className = "on";
        api.set("t", t);
        api.set("p", 1);
        currentShow = 'step1';
        $('#HDT-show-all').val('none');
        if(clickBar!=1){
        	currentChart = 'bars'
        }
        if(currentChart=='bars'){
        	currentFunc = bars;
        }else{
        	currentFunc = pies;
        }
        api.next();
    });

    $('#HDT-select-menu').on('change', 'select', function(e){
        var target = e.currentTarget;
        if(target.name == "category_id") {
            $.get('/location/get_classes_list', {category_id:target.value}, function(html){
                $menu.find('select[name=classes_id]').replaceWith(html);
            });
            api.set(target.name, target.value, true);
            api.set('classes_id', 0, true);
            api.reload();
            api2.set(target.name, target.value, true);
            api2.set('classes_id', 0, true);
            api2.reload();
        }else{
        	api.set(target.name, target.value);
        	api2.set(target.name, target.value);
        }
        if(currentShow=='step1'){
        	api.next();
        }else{
        	api2.next();
        }
    });

    $('#HDT-show-all').on('change', function(){
    	var nowVal = $(this).val();
    	if(nowVal=='none'){
    		$('#HDT-main').html('');
    		api.set("p", 1);
    		currentShow = 'step1';
    		if(clickBar!=1){
            	currentChart = 'bars'
            }
            if(currentChart=='bars'){
            	currentFunc = bars;
            }else{
            	currentFunc = pies;
            }
    		api.next();
    	}else{
    		$('#HDT-main').html('');
    		currentShow = 'step2';
    		var group1 = menu.find('a.on').attr('data-group');
    		var group2 = nowVal;
    		if(clickBar!=1){
            	currentChart = 'pies'
            }
            if(currentChart=='bars'){
            	currentFunc = bars2;
            }else{
            	currentFunc = pies2;
            }
    		api2.set("p", 1);
    		api2.set("group1",group1);
    		api2.set("group2",group2);
    		api2.next();
    	}
        /*api.set('show_all', this.value);
        if(api1){
            api1.set('show_all', this.value);
        }*/
    });

    $('select[name=show_type]').on('change',function(){
    	stype = this.value;
    	currentFunc.call();
    })
    $('select[name=orderby]').on('change', function(){
    	$('#HDT-main').html('');
        api.set('orderby', this.value);
		api.set("p", 1);
		currentShow = 'step1';
		if(clickBar!=1){
        	currentChart = 'bars'
        }
        if(currentChart=='bars'){
        	currentFunc = bars;
        }else{
        	currentFunc = pies;
        }
        $('#HDT-show-all').val('none');
		api.next();
    });
    
    var myfun = function(){
    	if(currentShow=='step1'){
        	api.next();
        }else{
        	api2.next();
        }
    }
    
    var apiArr = [api,api2];
    require.async('lj/fancybox',function(fancybox){
    	fancybox(apiArr,myfun);
    });
});