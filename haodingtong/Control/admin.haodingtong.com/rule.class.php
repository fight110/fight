<?php

class Control_rule {
    public static function Action_index($r){
        Flight::validateEditorHasLogin();

        $data   = $r->query;
        $t      = $data->t      ? $data->t  : 'series';
        $options    = array();

        $Rule   = new Rule;
        $list   = $Rule->getRuleList($t, $options);

        $result['t']    = $t;
        $result['list'] = $list;

        Flight::display('rule/index.html', $result);
    }

    public static function Action_add($r, $id=0){
        Flight::validateEditorHasLogin();

        $data   = $r->query;
        $t      = $data->t      ? $data->t  : 'series';

        if($id){
            $Rule           = new Rule;
            $RuleDetail     = new RuleDetail;
            $result['rule'] = $Rule->findone("id={$id}");
            $RuleHash       = $RuleDetail->find("rule_id={$id}", array('key'=>'keyword_id', 'limit'=>100));
        }

        $Keywords   = new Keywords;
        $Factory    = new ProductsAttributeFactory($t);
        $list   = $Factory->getAllList();
        foreach($list as &$row){
            $row['name']    = $Keywords->getName_File($row['keyword_id']);
            $row['percent'] = $RuleHash[$row['keyword_id']]['percent'];
        }

        $result['t']    = $t;
        $result['list'] = $list;

        Flight::display('rule/add.html', $result);
    }

    public static function Action_adding($r, $id=0){
        Flight::validateEditorHasLogin();

        $data   = $r->data;
        $name   = $data->name;
        $field  = $data->field;
        $Rule   = new Rule;
        if($id){
            $Rule->update(array('name'=>$name), "id={$id}");
            $rule_id    = $id;
        }elseif($name && $field){
            $rule   = $Rule->create(array('name' => $name, 'field' => $field));
            $rule->insert();
            $rule_id    = $rule->id;
        }
        if($rule_id){
            $RuleDetail     = new RuleDetail;
            foreach($data as $key => $val){
                if(preg_match("/^rid_(\d+)$/", $key, $matches)){
                    $keyword_id     = $matches[1];
                    $percent        = $val;
                    $detail         = $RuleDetail->create(array('rule_id'=>$rule_id,'keyword_id'=>$keyword_id,'percent'=>$percent));
                    $detail->insert(true);
                }
            }
            SESSION::message("保存成功");
        }
        $returl     = $id   ? $r->referrer  : "/rule/add/$rule_id?t={$field}";

        Flight::redirect($returl);
    }

    public static function Action_user($r, $id){
        Flight::validateEditorHasLogin();

        $data   = $r->query;
        $t      = $data->t      ? $data->t  : 'series';
        $limit  = $data->limit  ? $data->limit  : 100;
        $search = $data->search;
        $area1  = $data->area1;
        $area2  = $data->area2;

        $Rule       = new Rule;
        $RuleUser   = new RuleUser;
        $options['search']  = $search;
        $options['area1']   = $area1;
        $options['area2']   = $area2;
        $list   = $RuleUser->getAllUserList($id, array('page'=>$data->p, 'limit'=>$limit), $options);
        $total  = $RuleUser->getAllUserCount($id, array(), $options);

        $result['rule'] = $Rule->findone("id={$id}");
        $result['t']    = $t;
        $result['list'] = $list;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);
        $result['area1']    = $area1;
        $result['area2']    = $area2;
        $result['search']   = $search;
        $result['id']       = $id;


        Flight::display('rule/user.html', $result);
    }

    public static function Action_user_set($r){
        Flight::validateEditorHasLogin();

        $data       = $r->data;
        $user_id    = $data->user_id;
        $rule_id    = $data->rule_id;
        $field      = $data->field;
        $checked    = $data->checked;

        $RuleUser   = new RuleUser;
        if($checked){
            $RuleUser->create(array('user_id'=>$user_id, 'rule_id'=>$rule_id, 'field'=>$field))->insert(true);
        }else{
            $RuleUser->delete("user_id=$user_id AND rule_id=$rule_id AND field='$field'");
        }

        Flight::json(array('valid'=>true));
    }

    public static function Action_budget($r){
        Flight::validateEditorHasLogin();

        $result['t']    = "budget";
        Flight::display("rule/budget.html", $result);
    }

    public static function Action_budget_install($r){
        Flight::validateEditorHasLogin();

        $Rule       = new Rule;
        $RuleUser   = new RuleUser;
        $RuleDetail = new RuleDetail;
        $User       = new User;
        $Budget     = new Budget;
        $BudgetCount    = new BudgetCount;

        $Hash_Budget    = array();

        $rulelist   = $Rule->find("", array("limit"=>10000));
        $num_user   = 0;
        foreach($rulelist as $rule){
            $rule_id    = $rule['id'];
            $rule_detail_list   = $RuleDetail->getRuleDetailList($rule_id);
            $rule_user_list     = $RuleUser->getRuleUserList($rule_id, array("limit"=>10000));
            foreach($rule_user_list as $u){
                $user_id    = $u['id'];
                $budget     = $u['exp_num'];
                if(!$Hash_Budget[$user_id]){
                    $BudgetCount->setBudget($user_id, $budget);
                    $Hash_Budget[$user_id]  = $u;
                    $num_user++;
                }
                foreach($rule_detail_list as $rule_detail){
                    $field      = $rule['field'];
                    $keyword_id = $rule_detail['keyword_id'];
                    $percent    = $rule_detail['percent'];
                    $Budget->addBudget($user_id, $field, $keyword_id, $percent);
                }
            }
        }

        SESSION::message("总共初始化客户数:{$num_user}");

        Flight::redirect($r->referrer);
    }
}