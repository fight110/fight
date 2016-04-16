<?php

class ProductOrder_Perferential {
    public function __construct($product_id){
        $this->product_id   = $product_id;
        $ProductPerferential    = new ProductPerferential;
        $this->perf_list    = $ProductPerferential->get_perf_list($product_id);
    }

    public function perf (ProductOrder_Order $order) {
        $num    = $order->get_num();
        foreach($this->perf_list as $perf) {
            $start_num  = $perf['start_num'];
            $end_num    = $perf['end_num'];
            if($start_num <= $num){
                if($end_num == 0 || $end_num > $num){
                    return $perf['price'];
                }
            }
        }
        return null;
    }
}



