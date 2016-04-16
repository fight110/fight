<?php

class RuleUser Extends BaseClass {
    public function __construct(){
        $this->setFactory('rule_user');
    }

    public function getRuleUserList($rule_id, $options){
        $tablename  = "user as u left join rule_user as ru on u.id=ru.user_id";
        $fields     = "u.*";
        $condition[]    = "u.type=1";
        $condition[]    = "ru.rule_id={$rule_id}";
        $where      = implode(' AND ', $condition);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        return $this->find($where, $options);
    }

    public function getAllUserList($rule_id, $options=array(), $more=array()){
        $tablename  = "user as u left join rule_user as ru on u.id=ru.user_id and ru.rule_id={$rule_id}";
        $fields     = "u.*, ru.rule_id";
        $order      = "rule_id desc";
        $search     = $more['search'];
        $area1      = $more['area1'];
        $area2      = $more['area2'];
        $condition[]    = "u.type=1";
        if($area1)  $condition[]    = "u.area1=$area1";
        if($area2)  $condition[]    = "u.area2=$area2";
        if($search){
            $search     = addslashes($search);
            $condition[]    = "u.name like '%{$search}%'";
        }
        $where      = implode(' AND ', $condition);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['order']       = $order;
        return $this->find($where, $options);
    }

    public function getAllUserCount($rule_id, $options=array(), $more=array()){
        $tablename  = "user as u left join rule_user as ru on u.id=ru.user_id and ru.rule_id={$rule_id}";
        $search     = $more['search'];
        $area1      = $more['area1'];
        $area2      = $more['area2'];
        $condition[]    = "u.type=1";
        if($area1)  $condition[]    = "u.area1=$area1";
        if($area2)  $condition[]    = "u.area2=$area2";
        if($search){
            $search     = addslashes($search);
            $condition[]    = "u.name like '%{$search}%'";
        }
        $where      = implode(' AND ', $condition);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        return $this->getCount($where, $options);
    }

    public function createRuleUser($rule_id, $user_id, $field){
        return $this->create(array('rule_id'=>$rule_id, 'user_id'=>$user_id, 'field'=>$field))->insert(true);
    }
}




