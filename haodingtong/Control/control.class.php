<?php

class Control_control {
    public static function Action_index($r){
        $data       = $r->data;
        $WEBSTATUS  = $data->WEBSTATUS;
        $time       = $data->time;
        $site       = $data->site;
        $Company    = new Company;
        if($site){
            SESSION::set("HDT_SITE", $site);
        }
        echo SESSION::get("HDT_SITE");
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

    public static function Action_auth($r, $id=0){
        $data       = $r->query;
        $p          = $data->p;
        $limit      = $data->limit  ? $data->limit : 10;
        $domain     = $data->domain;
        $q          = $data->q;
        $status     = $data->status;
        $logout     = $data->logout;
        $Devicelist = new Devicelist;
        $options['page']    = $p;
        $options['limit']   = $limit;
        $options['count']   = true;
        $condition['domain']    = $domain;
        $condition['q']         = $q;
        if($id){
            if($logout == 1){
                $Devicelist->update(array('key1'=>'强制退出'), "id={$id}");
            }else{
                $Devicelist->setStatus($id, $status);
            }
            Flight::redirect($r->referrer);
            return;
        }
        $list       = $Devicelist->getDevicelist($condition, $options); 
        $total      = $Devicelist->get_count_total();
        $domainlist = $Devicelist->getDeviceDomainList();
        $result['list'] = $list;
        $result['domainlist']   = $domainlist;
        $result['pagelist'] = Pager::build(Pager::DEFAULTSTYLE, $data, $total, $limit);
        $result['domain']   = $domain;
        $result['q']        = $q;

        Flight::display('control/auth.html', $result);
    }

    public static function Action_auth_logout($r) {
        $Devicelist     = new Devicelist;
        $Devicelist->update(array('key1'=>'强制退出'), "1");
        Flight::redirect($r->referrer);
    }


}