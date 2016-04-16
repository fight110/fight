<?php

class Control_control {
    public static function Action_index($r){
        
        $data       = $r->data;
        $WEBSTATUS  = $data->WEBSTATUS;
        $time       = $data->time;
        $Company    = new Company;
        if($WEBSTATUS){
            $WEBSTATUS  = $WEBSTATUS == "开启" ? true : false;
            $Company->setWEBSTATUS($WEBSTATUS, $time);
            Flight::redirect("/control");
            exit;
        }
        $result['WEBSTATUS']        = $Company->WEBSTATUS;
        if($timeout = $Company->WEBSTATUSTIMEOUT){
            $now    = time();
            if($now < $timeout){
                $result['restsecond']   = $timeout - $now;
            }
            $result['WEBSTATUSTIMEOUT'] = date("Y-m-d H:i:s", $timeout);
        }

        Flight::display('control/index.html', $result);
    }


}