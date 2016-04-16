 define(['jquery'], function(require, exports, module) {
    // var plot    =   $.jqplot
    if($('.Selects').length){
        require.async(['app/selects'], function(Selects){
            new Selects('/location/json', {dataType:'json', selector:'.Selects'});
        });
    }

    var yTickFormatter = function(format, val) {
        return parseInt(val);
    }

    var y2TickFormatter = function(format, val) {        
        return parseInt(val);
    }
    
    require.async('jquery/jqplot/jquery.jqplot.min', function(){
        require.async('jquery/jqplot/plugins/jqplot.highlighter.min',function(){

            $('#display-user').on('click',function(){
                var area1       =   $(".Selects select[name='area1']").val();
                var area2       =   $(".Selects select[name='area2']").val();
                var search_user =   $("#search-user").val();

                $.ajax({
                    url: '/plot/user_plot',
                    type: 'POST',
                    dataType: 'json',
                    data: {area1:area1,area2:area2,search_user:search_user},
                })
                .done(function(json) {
                    var list = json.list; 
                    var len  = list.length;
                    var data = [[],[]];
                    var i    = 10;
                    var ymax = 0;
                    var y2max= 0;
                    var max  = 10;
                    var min  = 1;
                    for(val in list){
                        ymax = (ymax < parseInt(list[val]['amount'])) ? list[val]['amount'] : ymax;
                        y2max= (y2max < parseInt(list[val]['num'])) ? list[val]['num'] : y2max;
                        data[0].push([i,list[val]['amount']]);
                        data[1].push([i,list[val]['num']]);
                        i--;
                    }
                    ymax    = (parseInt(ymax / 10000)+1) *10000; 
                    y2max   = (parseInt(y2max / 100)+1) *100; 
                    if(!data[0].length){
                        require.async('jquery/jquery.notify', function(n){
                            n.message({title:"提示", text:'没有数据！请重新选择条件'}, {expires:2000});
                        });
                        return false;
                    }else{
                        
                    }
                    
                    $('#user-plot-view').empty();
                    var plot = $.jqplot('user-plot-view', data, {
                        // title: {
                        //     show: true, //设置当前标题是否显示 
                        //     text: '客户订量趋势', // 设置当前图的标题  
                        //     location: "w",
                        // },
                        // Turns on animatino for all series in this plot.
                        // animate: true,
                        // Will animate plot on calls to plot.replot({resetAxes:true})
                        // animateReplot: true,
                        // animate: !$.jqplot.use_excanvas,
                        axes: {
                            xaxis: {
                                tickInterval: 1,
                                drawMajorGridlines: false,
                                drawMinorGridlines: true,
                                drawMajorTickMarks: false,
                                rendererOptions: {
                                    tickInset: 0.5,
                                    minorTicks: 1
                                },
                                // label: '时间',
                                max: max,
                                min: min,
                            },
                            yaxis: {
                                tickOptions: {
                                    formatter: yTickFormatter
                                },
                                rendererOptions: {
                                    forceTickAt0: true
                                },
                                label: '金额',
                                min: 0,
                                max: ymax,
                            },
                            y2axis: {
                                tickOptions: {
                                    formatString: "%'d"
                                },
                                rendererOptions: {
                                    // align the ticks on the y2 axis with the y axis.
                                    alignTicks: true,
                                    forceTickAt0: true
                                },
                                label: '数量',
                                min: 0,
                                max: y2max,
                            },
                        },
                        highlighter: {
                            show: true,
                            showLabel: true,
                            tooltipAxes: 'y',
                            sizeAdjust: 7.5,
                            tooltipLocation: 'ne',
                            useAxesFormatters: false,
                            tooltipFormatString: "%'d", //当前一项为false才可使用，仅格式化提示，非axis格式化
                        },
                        legend: {
                            show: true,
                            location: 'nw',
                            labels: ['金额', '数量'],
                        },
                        series: [{
                            pointLabels: {
                                show: true, //显示数据点数值 
                            },
                            yaxis: 'yaxis',
                        }, {
                            pointLabels: {
                                show: true, //显示数据点数值 
                            },
                            yaxis: 'y2axis',
                        }, ],
                    });
                })
            })

            $('#display-product').on('click',function(){
                var area1   =   $(".Selects select[name='area1']").val();
                var area2   =   $(".Selects select[name='area2']").val();
                var post_data = {};
                $(".product-plot select").each(function() {
                    var name = $(this).attr('name');
                    var val  = $(this).val();
                    post_data[name] = val;
                });

                post_data['search_product'] = $("#search-product").val();

                $.ajax({
                    url: '/plot/product_plot',
                    type: 'POST',
                    dataType: 'json',
                    data: post_data,
                })
                .done(function(json) {
                    var list = json.list; 
                    var len  = list.length;
                    var data = [[],[]];
                    var i    = 10;
                    var ymax = 0;
                    var y2max= 0;
                    var max  = 10;
                    var min  = 1;
                    for(val in list){
                        ymax = (ymax < parseInt(list[val]['amount'])) ? list[val]['amount'] : ymax;
                        y2max= (y2max < parseInt(list[val]['num'])) ? list[val]['num'] : y2max;
                        data[0].push([i,list[val]['amount']]);
                        data[1].push([i,list[val]['num']]);
                        i--;
                    }
                    ymax    = (parseInt(ymax / 10000)+1) *10000; 
                    y2max   = (parseInt(y2max / 100)+1) *100; 
                    if(!data[0].length){
                        require.async('jquery/jquery.notify', function(n){
                            n.message({title:"提示", text:'没有数据！请重新选择条件'}, {expires:2000});
                        });
                        return false;
                    }else{
                        
                    }
                    $('#product-plot-view').empty();
                    var plot = $.jqplot('product-plot-view', data, {
                        // title: {
                        //     show: true, //设置当前标题是否显示 
                        //     text: '客户订量趋势', // 设置当前图的标题  
                        //     location: "w",
                        // },
                        // Turns on animatino for all series in this plot.
                        // animate: true,
                        // Will animate plot on calls to plot.replot({resetAxes:true})
                        // animateReplot: true,
                        // animate: !$.jqplot.use_excanvas,
                        axes: {
                            xaxis: {
                                tickInterval: 1,
                                drawMajorGridlines: false,
                                drawMinorGridlines: true,
                                drawMajorTickMarks: false,
                                rendererOptions: {
                                    tickInset: 0.5,
                                    minorTicks: 1
                                },
                                // label: '时间',
                                max: max,
                                min: min,
                            },
                            yaxis: {
                                tickOptions: {
                                    formatter: yTickFormatter
                                },
                                rendererOptions: {
                                    forceTickAt0: true
                                },
                                label: '金额',
                                min: 0,
                                max: ymax,
                            },
                            y2axis: {
                                tickOptions: {
                                    formatString: "%'d"
                                },
                                rendererOptions: {
                                    // align the ticks on the y2 axis with the y axis.
                                    alignTicks: true,
                                    forceTickAt0: true
                                },
                                label: '数量',
                                min: 0,
                                max: y2max,
                            },
                        },
                        highlighter: {
                            show: true,
                            showLabel: true,
                            tooltipAxes: 'y',
                            sizeAdjust: 7.5,
                            tooltipLocation: 'ne',
                            useAxesFormatters: false,
                            tooltipFormatString: "%'d", //当前一项为false才可使用，仅格式化提示，非axis格式化
                        },
                        legend: {
                            show: true,
                            location: 'nw',
                            labels: ['金额', '数量'],
                        },
                        series: [{
                            pointLabels: {
                                show: true, //显示数据点数值 
                            },
                            yaxis: 'yaxis',
                        }, {
                            pointLabels: {
                                show: true, //显示数据点数值 
                            },
                            yaxis: 'y2axis',
                        }, ],
                    });
                })
            })

            $('#display-user').trigger('click');
            $('#display-product').trigger('click');
        })
    })
 })
