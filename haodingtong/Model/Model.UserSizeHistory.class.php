<?php

class UserSizeHistory Extends BaseClass {
    public function __construct(){
        $this->setFactory('user_size_history');
    }

    public function set_size_num ($user_id, $keyword_id, $size_id, $num) {
        $data['user_id']    = $user_id;
        $data['keyword_id'] = $keyword_id;
        $data['size_id']    = $size_id;
        $data['num']        = $num;
        return $this->create($data)->insert(true);
    }

    public function get_size_list($user_id,$keyword_id){
        $condition[]    = "user_id={$user_id}";
        $condition[]    = "keyword_id='{$keyword_id}'";
        $options['limit']   = 1000;
        $options['key'] =   "size_id";
        //$options['db_debug']= true;
        $where  = implode(" AND ", $condition);
        return $this->find($where, $options);
    }

}




