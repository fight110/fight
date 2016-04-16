<?php

class ProductStockUnit {
    public function __construct($data=array()){
        if(!is_array($data))    $data = array();
        $this->data     = $data;
    }

    public function get_live_num () {
        $totalnum   = $this->data['totalnum'];
        $ordernum   = $this->data['ordernum'];
        $giftnum    = $this->data['giftnum'];
        return $totalnum - $ordernum - $giftnum;
    }

    public function add_ordernum ($dif_num) {
    	$this->data['ordernum']	+= $dif_num;
    }

    public function save () {
    	$id 			= $this->data['id'];
        if($id){
            $ProductStock   = new ProductStock;
            $ProductStock->update($this->data, "id={$id}");
        }
    }

}




