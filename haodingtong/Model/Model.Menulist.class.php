<?php

class Menulist Extends BaseClass {
    public function __construct(){
        $this->setFactory('menulist');
    }

    public function get_mlist($utype) {
        $options['tablename']   = "menulist as m1 left join menulist as m2 on m1.pid=m2.id";
        $options['fields']      = "m1.*,IF(m2.rank, m2.rank, m1.rank) as rank2";
        $options['limit']   = 1000;
        $options['order']   = "rank2,pid,rank";
        $condition[]        = "m1.utype={$utype}";
        $where  = implode(" AND ", $condition);
        $list   = $this->find($where, $options);
        return $list;
    }

}




