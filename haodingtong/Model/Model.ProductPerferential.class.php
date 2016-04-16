<?php

class ProductPerferential Extends BaseClass {
    public function __construct($id=null){
        $this->setFactory('product_perferential');
        if(is_numeric($id)){
            $this->setAttribute($this->findone("id={$id}"));
        }
    }

    public function create_perferential ($data) {
        $Product    = new Product;
        $product    = $Product->findone("kuanhao='{$data['kuanhao']}'");
        $data['product_id'] = $product['id'];
        return $this->create($data)->insert(true);
    }

    public function get_perf_list ($product_id) {
        $options['limit']   = 10;
        $condition[]    = "product_id={$product_id}";
        $condition[]    = "status=1";
        $where  = implode(" AND ", $condition);
        return $this->find($where, $options);
    }

}




