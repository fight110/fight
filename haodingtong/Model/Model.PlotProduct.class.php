<?php

class PlotProduct Extends BaseClass {
    public function __construct(){
        $this->setFactory('plot_product');
    }
    
    public function refresh($data){
        $time_axis          = date('mdHi');
        $time               = substr($time_axis,0,strlen($time_axis)-2);
        $minutes            = substr($time_axis,-2);
        $row                = array();
        $row['product_id']  = $data['product_id'];
        $row['num']         = $data['num'];
        $row['amount']      = $data['price'];
        $row['time_axis']   = $time . (int)($minutes/30);   //30分钟为一组数据
        $this->create($row)->insert(true);
    }
}