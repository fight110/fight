<?php

class PlotUser Extends BaseClass {
    public function __construct(){
        $this->setFactory('plot_user');
    }

    public function refresh($data){
        $time_axis          = date('mdHi');
        $time               = substr($time_axis,0,strlen($time_axis)-2);
        $minutes            = substr($time_axis,-2);
        $row                = array();
        $row['user_id']     = $data['user_id'];
        $row['num']         = $data['num'];
        $row['amount']      = $data['price'];
        $row['discount_amount']      = $data['discount_price'];
        $row['zd_discount_amount']   = $data['zd_discount_price'];
        $row['time_axis']   = $time . (int)($minutes/30);   //30分钟为一组数据
        $this->create($row)->insert(true);
    }
}