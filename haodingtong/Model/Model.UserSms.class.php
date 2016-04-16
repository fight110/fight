<?php

class UserSms Extends BaseClass {
    public function __construct(){
        $this->setFactory('user_sms');
    }

    public function create_sms($user_id, $message_id){
        $sms = $this->create(array('uid'=>$user_id, 'mid'=>$message_id));
        return $sms->insert(true);
    }

    public function getMySmsNum($user_id){
        $condition[]    = "status=0";
        $condition[]    = "uid in ({$user_id})";
        $where  = implode(" AND ", $condition);
        return $this->getCount($where);
    }

	public function get_my_messagelist($options, $user_id=0){
        $tablename  = "user_sms as s left join message as m on s.mid=m.id";
        $fields     = "s.id as sid,s.status,s.post_time as read_time, m.*";
        if($user_id)    $condition[]    = "s.uid={$user_id}";
        $where      = implode(' AND ', $condition);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['order']       = "m.post_time desc";
        return $this->find($where, $options);
    }

    public function get_message_detail($sid){
    	$tablename  = "user_sms as s left join message as m on s.mid=m.id";
        $fields     = "s.id as sid,s.status,s.post_time as read_time, m.*";
        if($sid)    $condition[]    = "s.id={$sid}";
        $where      = implode(' AND ', $condition);
        $options['tablename']   = $tablename;
        $options['fields']      = $fields;

        $info   = $this->findone($where,$options);
        return $info;
    }
}




