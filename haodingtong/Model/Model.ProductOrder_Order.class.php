<?php

class ProductOrder_Order {
    public function __construct($user_id, $product_id){
        $this->OrderList    = new OrderList;
        $this->user_id      = $user_id;
        $this->product_id   = $product_id;
        $options['limit']   = 1000;
        $this->myorderlist  = $this->OrderList->find("user_id={$user_id} AND product_id={$product_id}", $options);
        $this->old_data     = array();
        $this->new_data     = array();
        $this->dif_data     = array();
        $this->data         = array();
        foreach($this->myorderlist as $order){
            $this->set_order($order['product_color_id'], $order['product_size_id'], $order['num']);
        }
    }

    public function add ($product_color_id, $product_size_id, $num) {
        $this->new_data[$product_color_id][$product_size_id]    = $num;	
        $old_data   = $this->old_data[$product_color_id][$product_size_id];
        $this->dif_data[$product_color_id][$product_size_id]    = $num - $old_data;
        $this->data[$product_color_id][$product_size_id]        = $num;
    }

    public function set_order ($product_color_id, $product_size_id, $num) {
        $this->old_data[$product_color_id][$product_size_id]    = $num;
        $this->data[$product_color_id][$product_size_id]        = $num;
    }

    public function get_num () {
        $count = 0;
        foreach($this->data as $product_color_id => $color_hash){
            foreach($color_hash as $product_size_id => $num){
                $count += $num;
            }
        }
        return $count;
    }

    public function validate (Product $product) {
        $product_id  = $product->id;
        if($product->basenum){
            $basenum = $product->basenum;
            $ProductSize = new ProductSize();
            foreach($this->data as $product_color_id => $color_hash){
                foreach($color_hash as $product_size_id => $num){
                    if($num > 0 && 0 != ($num % $basenum)){
                        return $this->failure("订量都必须为{$basenum}的倍数");
                    }
                    $size_mininum = $ProductSize->get_size_mininum($product_id,$product_size_id);
                    if($num > 0 && $size_mininum > 0 && $num < $size_mininum){
                        $size_name = Keywords::cache_get($product_size_id);
                        return $this->failure("尺码{$size_name}未达起订量{$size_mininum}");
                    }
                }
            }
        }else{
            $ProductSize = new ProductSize();
            foreach($this->data as $product_color_id => $color_hash){
                foreach($color_hash as $product_size_id => $num){
                    if($num > 0){
                        $size_mininum = $ProductSize->get_size_mininum($product_id,$product_size_id);
                        if($size_mininum > 0 && $num < $size_mininum){
                            $size_name = Keywords::cache_get($product_size_id);
                            return $this->failure("尺码{$size_name}未达起订量{$size_mininum}");
                        }
                    }
                }
            }
        }

        // 如果是现货
        if($product->isspot == 2){
            $stock          = new ProductOrder_Stock($product_id);
            $dif_data       = $this->dif_data;
            if(!$stock->set_dif_order($dif_data)){
                return $this->failure(implode("<br>", $stock->error_list));
            }
            $this->stock    = $stock;
        }
        $this->product  = $product;
        $ProductOrder   = ProductOrder::getInstance($this->user_id, $product_id);
        $ProductOrder->on('Save', $this);
        return array('error'=>0);
    }

    public function onSave () {
        if($this->stock) {
            $this->stock->save();
        }
        $userIndicator = UserIndicator::getInstance($this->user_id);
        $userIndicator->refresh($this->product);
    }

    public function failure ($message) {
        return array('error'=>1, 'message'=>$message);
    }

    public function save(ProductOrder $ProductOrder) {
        $OrderList  = $this->OrderList;
        $user_id    = $this->user_id;
        $product_id = $this->product_id;
        $product    = $ProductOrder->product;
        $user       = $ProductOrder->user;
        //零售价
        $unit_price = $product->price;

        //买断价
        $unit_price_purchase     = $product->price_purchase;
        $Company = new Company;
        $company = $Company->getData();
        
        if($user->discount==0){
            $discount_unit_price = 0;
        }elseif($unit_price_purchase>0 && (!$company['price_purchase_type']) && $user->discount_type ){
            // 买断价设置到用户时，买断用户使用买断价做折后单价
            $discount_unit_price = $unit_price_purchase;
        }elseif($unit_price_purchase>0 && $company['price_purchase_type']){
            //买断价设置到单款时，存在买断价就使用买断价做折后单价
            $discount_unit_price = $unit_price_purchase;
        }else{
            // 判断是否有促销政策
            $Perferential       = new ProductOrder_Perferential($product_id);
            $perf_price         = $Perferential->perf($this);
            if(is_numeric($perf_price)){
                $discount_unit_price    = $perf_price;
            }else{
                // 获取商品折扣 优先品类折扣 其次客户折扣
                $discount   = $user->get_user_product_discount($user, $product);
                $discount_unit_price    = $discount * $unit_price;
            }
        }
        // 总代计算
        $master     = ProductOrder_UserMaster::getMasteruser($user->id);
        $zd_user_id = $master->user_id;
        if($zd_user_id){ //如果存在总代
            $zd_discount    = $master->get_user_product_discount($product);
            $zd_discount_unit_price = $zd_discount * $unit_price;
        }else{ // 否则默认自己的
            $zd_user_id     = $user_id;
            $zd_discount_unit_price = $discount_unit_price;
        }

        $size_group_id      = $product->size_group_id;
        $SizeGroup          = SizeGroup::getInstance($size_group_id);
        $size_list          = $SizeGroup->get_size_list();
        $OrderListUserProductColor  = new OrderListUserProductColor;
        $OrderListAgent     = new OrderListAgent;
        $OrderListArea      = new OrderListArea;

        foreach($this->data as $product_color_id => $color_hash){
            $product_color  = array("user_id"=>$user_id, "product_id"=>$product_id, "product_color_id"=>$product_color_id, "unit_price"=>$unit_price, "discount_unit_price"=>$discount_unit_price);
            foreach($color_hash as $product_size_id => $num){
                if($num == 0){
                    $where      = "user_id={$user_id} AND product_id={$product_id} AND product_color_id={$product_color_id} AND product_size_id={$product_size_id}";
                    $OrderList->delete($where);
                }else{
                    $amount             = $unit_price * $num;
                    $discount_amount    = $discount_unit_price * $num;
                    $zd_discount_amount = $zd_discount_unit_price * $num;
                    $OrderList->create_order($user_id, $product_id, $product_color_id, $product_size_id, $num, 
                        $unit_price, $discount_unit_price, $amount, $discount_amount,
                        $zd_user_id, $zd_discount_unit_price, $zd_discount_amount);
                    $product_color['num']               += $num;
                    $product_color['amount']            += $amount;
                    $product_color['discount_amount']   += $discount_amount;
                }
            }
            foreach($size_list as $size_key => $size) {
                $key    = 's' . ($size_key + 1);
                $num    = $color_hash[$size['size_id']];
                $product_color[$key]    = $num;
            }
            $OrderListUserProductColor->create($product_color)->insert(true);

            // 更新总代汇总信息
            $OrderListAgent->refresh_agent_product($zd_user_id, $product_id,$product_color_id);
            // 更新区域汇总信息
            $OrderListArea->refresh_area_product($user->area1, $product_id,$product_color_id);
        }
        // 更新商品汇总信息
        $OrderList->refresh_index_product($product_id);
    }

}



