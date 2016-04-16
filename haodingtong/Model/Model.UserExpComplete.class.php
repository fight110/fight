<?php

class UserExpComplete Extends BaseClass {
    public function __construct(){
        $this->setFactory('user_exp_complete');
    }

    public function set_exp ($user_id, $field, $keyword_id, $name, $value) {
        $data['user_id']    = $user_id;
        $data['field']      = $field;
        $data['keyword_id'] = $keyword_id;
        $data[$name]        = $value;
        return $this->create($data)->insert(true);
    }

    public function get_exp_complete_list($params=array(), $options=array()){
        $user_id    = $params['user_id'];
        $field      = $params['field'];
        $condition[]    = "user_id={$user_id}";
        $condition[]    = "field='{$field}'";
        $options['limit']   = 1000;
        // $options['db_debug']    = true;
        $where  = implode(" AND ", $condition);
        return $this->find($where, $options);
    }

}




