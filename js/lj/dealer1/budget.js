

define(['jquery'], function(require, exports, module) {
    var $budget = $('#HDT-budget'), $main = $('#HDT-main'), 
        $budget_detail_list = $main.find('input.HDT-budget-detail'),
        $budget_percent_list = $main.find('input.HDT-budget-percent'),
        $budget_percent_total = $('#HDT-budget-percent-total'),
        $form   = $main.find('form'),
        checked = $budget.val() ? true : false;

    var Budget = {
        changedetail  : function(budget){
            if(budget>>0){
                var total = 0;
                for(var i = 0, len = $budget_detail_list.length; i < len; i++){
                    var percent = parseInt( $budget_detail_list[i].value / budget * 100 );
                    $budget_percent_list[i].value = percent;
                    total += $budget_detail_list[i].value>>0;
                }
                percent = parseInt( total / budget * 100 );
                console.log(budget)
                Budget.setpercenttotal(percent);
            }
        },
        changepercent : function(budget){
            if(budget>>0){
                var percent = 0;
                for(var i = 0, len = $budget_percent_list.length; i < len; i++){
                    var detail = parseInt( budget * $budget_percent_list[i].value / 100 );
                    $budget_detail_list[i].value = detail;
                    percent += $budget_percent_list[i].value>>0;
                }
                Budget.setpercenttotal(percent);
            }
        },
        setpercenttotal : function(percent){
            if(percent > 100){
                $budget_percent_total.html('<font color=red>' + percent + '</font>' + '%');
            }else{
                $budget_percent_total.html(percent + '%');
            }
        }
    };

    var init = function(){
        var budget = $budget.val();
        if(budget){
            Budget.changedetail(budget);
        }
    };
    init();

    require.async(['app/keyborad'], function(keyborad){
        var k = new keyborad('#HDT-main', {selector:"input:not([type=submit])"});
        k.on('change', function(e, input){
            var budget = $budget.val(), total = 0;
            if(input.className.indexOf('HDT-budget-total') >= 0){
                if(budget){
                    checked = true;
                    Budget.changepercent(budget);
                }
            }else if(input.className.indexOf('HDT-budget-detail') >= 0){
                if(checked == false){
                    $budget_detail_list.each(function(){
                        total += this.value>>0;
                    });
                    $budget.val(budget = total);
                }
                Budget.changedetail(budget);
            }else if(input.className.indexOf('HDT-budget-percent') >= 0){
                if(budget){
                    Budget.changepercent(budget);
                }
            }
        });

        $form.on('submit', function(){
            var data = $form.serialize();
            $.post('/dealer1/budget_save', data, function(json){
                var message = json.valid ? '保存成功' : json.message;
                require.async('jquery/jquery.notify', function(n){
                    n.message({title:"提示", text:message}, {expires:1500});
                });
            }, 'json');
            k.close();
            return false;
        });
    });

    

    
});