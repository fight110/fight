<?php

class Company Extends BaseClass {
    private static $_MYCOMPANY, $is_change = false, $is_init = false, $_notAttrs = array('tablename', 'dbh', 'dbh_slave');
    public function __construct(){
        $this->setFactory('company');
        if(STATIC::$is_init === false){
            $data = $this->findone("");
            STATIC::$_MYCOMPANY = json_decode($data['content'], true);
            if(!STATIC::$_MYCOMPANY){
                STATIC::$_MYCOMPANY = array();
            }
            STATIC::$is_init    = true;
        }
    }


    public function setWEBSTATUS($WEBSTATUS, $time=0){
        $this->WEBSTATUS    = $WEBSTATUS;
        if($WEBSTATUS != 0 && $time != 0){
            $this->WEBSTATUSTIMEOUT     = time() + $time;
        }else{
            $this->WEBSTATUSTIMEOUT     = null;
        }
    }

    public function checkWEBSTATUS(){
        if($this->WEBSTATUS){
            if($this->WEBSTATUSTIMEOUT !== null && $this->WEBSTATUSTIMEOUT < time()){
                $this->WEBSTATUS    = false;
            }
        }

        return $this->WEBSTATUS;
    }

    public function getData(){
        return (array)STATIC::$_MYCOMPANY;
    }

    public function __get($name){
        return STATIC::$_MYCOMPANY[$name];
    }

    public function __set($name, $val){
        if(!in_array($name, STATIC::$_notAttrs)){
            if($this->$name != $val){
                STATIC::$is_change  = true;
            }
            STATIC::$_MYCOMPANY[$name] = $val;
        }else{
            $this->$name    = $val;
        }
        return $val;
    }

    public function __destruct(){
        if(STATIC::$is_change == true){
            $content    = $this->getData();
            $data['id'] = 0;
            $data['content']    = json_encode($content);
            $this->create($data)->insert(true);
            STATIC::$is_change = false;
        }
    }

    public function get_room_info($id){
        $room_list  = $this->room_list;
        if(is_array($room_list)){
            foreach($room_list as $room){
                if($room['id'] == $id){
                    return $room;
                }
            }
        }
        return array();
    }

    public function get_show_id($room_id){
        $show   = $this->show;
        return is_array($show)  ? $show[$room_id]   : 0;
    }

}




