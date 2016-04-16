<?php

class Device {
    public function __construct(){
        $HDT_AUTH_KEY 	= $_COOKIE[HDT_AUTH_KEY];
        $Devicelist 	= new Devicelist;
        $this->Devicelist 	= $Devicelist;
        if($HDT_AUTH_KEY){
        	$CACHE_KEY 	= "DEVICE_{$HDT_AUTH_KEY}";
        	$this->data = SESSION::cache($CACHE_KEY, function() use ($Devicelist, $HDT_AUTH_KEY){
        		$data   = $Devicelist->getDevice($HDT_AUTH_KEY);
                if(!$data['id']){
                    $Devicelist->createDevice($HDT_AUTH_KEY);
                    $data = $Devicelist->getAttribute();
                }
                $DevicelistActive = new DevicelistActive();
                $idata=array();
                $dtime=date('Y-m-d H:i:s');
                if($data['key1']   == "强制退出"){
                    SESSION::delete("user");
                    $idata['loginout_time']=$dtime;
                    $idata['status']=0;
                    $idata['info']='强制退出';
                }
                if($data['key1']   == "帐号冲突"){
                    SESSION::message("帐号在其他设备登入");
                    SESSION::delete("user");
                    $idata['loginout_time']=$dtime;
                    $idata['status']=0;
                    $idata['info']='帐号冲突';
                }
                $User   = new User;

                if($data['key1'] != $User->username){
                    $data['key1'] = $User->username;
                    $data['key2'] = $User->name;
                    $Devicelist->update($data, "id={$data['id']}");
                }
                $idata['device_id']=$data['id'];
                $idata['update_time']=$dtime;
                $DevicelistActive->create($idata)->insert(true);

                return $data;
        	}, HDT_AUTH_TIMEOUT, function($data) use ($Devicelist, $HDT_AUTH_KEY){
        		return $data['status']	? true : false;
        	});
        }else{
        	$Devicelist->createDevice();
        	$this->data 	= $Devicelist->getAttribute();
        }
    }

    public function is_auth(){
    	return $this->data['status'] ? true : false;
    }

    public function setMessage($message){
    	$this->data['message']	= $message;
    	$data['message']		= $message;
    	$id 					= $this->data['id'];
    	if($id){
    		$this->Devicelist->update($data, "id={$id}");
    	}
    }

    public function getMessage(){
    	return $this->data['message'];
    }


}




