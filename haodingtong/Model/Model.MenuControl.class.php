<?php

class MenuControl Extends BaseClass {
    public function __construct(){
        $this->setFactory('menu_control');
    }
  
    public function getUserMenuByType($type=1,$all=0){
        if(is_numeric($type)&&$type){
            $option = array(); 
            $condition = array();
            $condition[] = ' user_tid = "'.$type.'" ';
            if(!$all){
                $condition[] = ' is_open=1 ';
            }
            $condition[] = 'mc.menu_id=m.id';
            $condition[] = 'mc.menu_cid=c.id';
            $condition[] = 'c.status = 1';
            $condition[] = 'm.status = 1';
            
            $option['fields'] = 'c.id as cid,c.name as cname,c.link as clink,m.id as mid,m.name as mname,m.link as mlink,m.open_new,m.id_name,is_open,mc.id';
            $option['limit'] = 100;
            $option['tablename'] = ' menu_control mc , menu m , menu_category c ';
            $option['order'] = 'c.order_id,m.order_id';
            //$option['db_debug'] = true;
            $where  = implode(" AND ", $condition);       
            $res = $this->find($where,$option);
            $result = array();
            foreach($res as $rval){
                $result[$rval['cid']][] = $rval;
            }
            return $result;
        }
        return ;
    }
}




