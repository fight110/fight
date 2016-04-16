<?php

class DevicelistActive Extends BaseClass {
    public function __construct(){
        $this->setFactory('devicelist_active');
    }

    public function getOnlineNum(){
        $find=$this->find('status=1',array('fields'=>'count(*) as total'));
        return $find[0]['total'];
    }
}




