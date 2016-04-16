<?php

class HDTImportUser {
	private static $_keywords = array();
    private static $_location = array();
    public function __construct($row){
        $this->error_list   = array();
        $this->location    = new Location;
    	$this->init($row);
    }

    public function attr($key, $value=null) {
    	if(null !== $value) {
    		$this->data[$key]	= $value;
    	}
    	return $this->data[$key];
    }

    public function attrs () {
    	return $this->data;
    }

    public function init ($row) {
        $data       = array();
        $rid        = 1;
        $typearr   	= array("管理员"=>0, "总经理"=>3, "AD"=>3, "总代"=>2, "终端"=>1,"设计师"=>9);
        $data['name']       = trim($row[$rid++]);//客户名称
        $data['username']   = trim($row[$rid++]);//账号
        $data['password']   = trim($row[$rid++]);//密码
        $this->zongdai_name = trim($row[$rid++]);//所属上级
        $area1              = trim($row[$rid++]);
        $area2              = trim($row[$rid++]);
        list($area1_id, $area2_id)  = $this->make_location($area1, $area2);
        $data['area1']      = $area1_id;//销售大区
        $data['area2']      = $area2_id;//二级区域
        $user_type          = $typearr[trim($row[$rid++])];
        $data['type']       = is_numeric($user_type) ? $user_type : 1;//账户类型
        $property           = trim($row[$rid++]);
        $data['property'] =   $property ? $this->make_keyword($property, 'property') : 0;//终端属性
        $data['is_stock']  = trim($row[$rid++]) ? 1 : 0;//是否备货账户
        $this->ad_name = trim($row[$rid++]);//分管AD
        $data['user_level'] = $this->make_keyword(trim($row[$rid++]), 'user_level');//用户等级
        $discount           = trim($row[$rid++]);//用户折扣
        if($discount > 1){
            $data['discount']   = $discount / 100;
        }else{
            $data['discount']   = is_numeric($discount) ? $discount : 1 ;
        }
        $data['exp_price']  = trim($row[$rid++]);//指标金额
        $data['exp_num']    = trim($row[$rid++]);//指标数量
        $brands = trim($row[$rid++]);//限制品牌
        if($brands){
            $brand_list = explode(";", $brands);
            $brand_id_list = array();
            foreach($brand_list as $brand){
                $brand_id = $this->make_keyword($brand, 'brand');
                $brand_id_list[]    = $brand_id;
            }
            $permission_brand = implode(",", $brand_id_list);
            $data['permission_brand'] = $permission_brand;
        }
        $data['discount_type']  = trim($row[$rid++]) ? 1 : 0;//买断类型
        $data['mulit_name']     = trim($row[$rid++]);   //多店订货

        $this->data         = $data;
    }



    public function error ($message) {
        $this->error_list[] = $message;
    }

    private function make_keyword ($keyword, $field) {
        $kid    = STATIC::$_keywords[$keyword];

        if(!$kid){
            $kid    = Keywords::cache_get_id(array($keyword));
            if($kid){
                $Factory    = new ProductsAttributeFactory($field);
                $Factory->createItemByKid($kid);
            }
            STATIC::$_keywords[$keyword]  = $kid;
        }
        return $kid;
    }

    private function make_location ($area1, $area2) {
        $area1_id   = STATIC::$_location[$area1]['id'];
        if(!$area1_id){
            $area1_id       = $this->location->getIdByName($area1);
            if(!$area1_id){
                $node       = $this->location->addNode(0, $area1);
                $area1_id   = $node->id;
            }
            $node   = array('id'=>$area1_id, 'children'=>array());
            STATIC::$_location[$area1]  = $node;
        }
        $area2_id   = STATIC::$_location[$area1]['children'][$area2];
        if(!$area2_id){
            $area2_id       = $this->location->getIdByName($area2, $area1_id);
            if(!$area2_id){
                $node       = $this->location->addNode($area1_id, $area2);
                $area2_id   = $node->id;
            }
            STATIC::$_location[$area1]['children'][$area2]  = $area2_id;
        }
        return array($area1_id, $area2_id);
    }

}




