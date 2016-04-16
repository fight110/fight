<?php

class Devicelist Extends BaseClass {
    public function __construct(){
        $this->setFactory('devicelist');
    }

    public function createDevice($HDT_AUTH_KEY=null){
    	if($HDT_AUTH_KEY){
    		list($id, $HDT_AUTH_KEY_TAG)	= explode(":", $HDT_AUTH_KEY);
    		$data['id']	= $id;
    	}else{
    		$HDT_AUTH_KEY_TAG = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
    	}
    	$data['tag']	= $HDT_AUTH_KEY_TAG;
    	$data['domain']	= $_SERVER['HTTP_HOST'];
    	$data['status']	= HDT_AUTH_DEVICE_STATUS 	? 1 : 0;
    	$data['post_time']	= date('Y:m:d H:i:s');
    	$data['useragent']	= $_SERVER['HTTP_USER_AGENT'];
    	$deviceID 		= $this->create($data)->insert();

    	$insertData=array();
    	$dataTime=date('Y-m-d H:i:s');
    	$insertData['device_id']=$deviceID;
    	$insertData['update_time']=$dataTime;
    	$DevicelistActive = new DevicelistActive();
    	$DevicelistActive->create($insertData)->insert();

    	if(null === $HDT_AUTH_KEY){
    		$HDT_AUTH_VAL 	= "{$deviceID}:{$HDT_AUTH_KEY_TAG}";
    		setcookie(HDT_AUTH_KEY, $HDT_AUTH_VAL, time() + 3600 * 24 * 365 * 10, "/");
    	}
    	if($deviceID){
    		$data['id']		= $deviceID;
    	}
    	$this->setAttribute($data);
    	return $deviceID;
    }

    public function getDevice($HDT_AUTH_KEY){
    	list($id, $tag) 	= explode(':', $HDT_AUTH_KEY);
    	return $this->findone("id={$id} AND tag='{$tag}'");
    }

    public function getDevicelist($cond=array(), $options=array()){
    	$condition 	= array();
    	$domain 	= $cond['domain'];
    	$q 			= addslashes($cond['q']);
    	if($domain)		$condition[]	= "domain='". addslashes($domain)."'";
    	if(isset($q))			$condition[]	= "(message like '%{$q}%' or key1 like '%{$q}%' or key2 like '%{$q}%')";
    	$options['order']	= 'edit_time desc';
    	$where 		= implode(" AND ", $condition);
    	$list 		= $this->find($where, $options);
    	return $list;
    }

    public function getDeviceDomainList(){
    	$options['limit']	= 100;
    	$options['group']	= "domain";
    	$options['fields']	= "domain, count(*) as num";
    	$where 	= "1";
    	return $this->find($where, $options);
    }

    public function setStatus($id, $status){
    	$this->update(array('status'=>$status ? 1 : 0), "id={$id}");
    }

    public function getDevicelistActive($cond=array(), $options=array()){
        $condition 	= array();
        $q 			= addslashes($cond['q']);
        if(isset($q))			$condition[]	= "(d.message like '{$q}%' or d.key1 like '{$q}%' or d.key2 like '{$q}%')";

        $options['order']	= 'd.edit_time desc';
        $options['fields']='d.*,da.status as dstatus,da.login_time,da.update_time,da.loginout_time';
        $options['tablename']=' devicelist d left join devicelist_active da on d.id=da.device_id ';

        $where 		= implode(" AND ", $condition);
        $list 		= $this->find($where, $options);
        return $list;
    }
}




