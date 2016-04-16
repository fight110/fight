<?php

class Rule Extends BaseClass {
    public function __construct(){
        $this->setFactory('rule');
    }

    public function getRuleList($field, $options){
        $field      = addslashes($field);
        return $this->find("field='{$field}'", $options);
    }

    public function createRule($field, $name){
        return $this->create(array('field'=>$field, 'name'=>$name))->insert();
    }


    public function getUserRule($user_id, $field, $more=array()){
        $tablename  = "rule_user as ru left join rule_detail as rd on ru.rule_id=rd.rule_id";
        $fields     = "rd.percent,rd.keyword_id";
        $condition[]    = "ru.user_id={$user_id}";
        $condition[]    = "ru.field='{$field}'";
        $where          = implode(' AND ', $condition);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['limit']       = 100;
        if($more['key'])    $options['key'] = $more['key'];
        return $this->find($where, $options);
    }

    public function createUserRule($user_id, $rule_name, $field, $data=array()){
        $rule_id    = $this->createRule($field, $rule_name);
        $RuleDetail = new RuleDetail;
        $RuleUser   = new RuleUser;
        $RuleDetail->createRuleDetail($rule_id, $data);
        $RuleUser->createRuleUser($rule_id, $user_id, $field);
    }

}




