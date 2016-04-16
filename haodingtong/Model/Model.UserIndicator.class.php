<?php

class UserIndicator Extends BaseClass {
	private static $_instances = array();
	public static function getInstance($user_id) {
		$instance = STATIC::$_instances[$user_id];
		if(!$instance) {
			$instance = new UserIndicator($user_id);
			STATIC::$_instances[$user_id]	= $instance;
		}
		return $instance;
	}
    public function __construct($user_id=0){
        $this->setFactory('user_indicator');
        $this->user_id 	= $user_id;
    }

    public function refresh (Product $product=null) {
    	$user_id 	= $this->user_id;
    	$OrderList 	= new OrderList;
    	$list 		= $this->get_user_indicator_list($user_id,0);
    	foreach($list as $indicator) {
    		$field 		= $indicator['field'];
    		$keyword_id = $indicator['keyword_id'];
    		if($field && $product && $product->$field != $keyword_id) {
    			continue;
    		}
            if($field2 && $product && $product->$field2 !=$keyword_id2) {
                continue;
            }
    		$this->refresh_unit($indicator);	
    	}
    }

    public function refresh_unit ($indicator) {
    	if(0 == $indicator['status']) {
    		return;
    	}

    	$OrderList 	= new OrderList;
    	$id 		= $indicator['id'];
    	$type 		= $indicator['type'];
    	$user_id 	= $this->user_id;
		switch ($type) {
			case 2 :
				$params = array("master_uid"=>$user_id);
				break;
			case 3 :
				$params = array("ad_id"=>$user_id);
				break;
            case 4 :
                $params = array();
                break;
            case 5:
                $params = array("area1"=>$user_id);
                break;
            case 6:
                $params = array("property"=>$user_id);
                break;
			default :
				$params = array("user_id"=>$user_id);
		}
		$field 	= $indicator['field'];
		if($field) {
			$params[$field]	= $indicator['keyword_id'];
		}
		$field2 = $indicator['field2'];
		if($field2) {
		    $params[$field2]	= $indicator['keyword_id2'];
		}
        $Company    = new Company;
        $company    = $Company->getData();
        $params['ex_classes'] = $company['ex_classes'];
		$analysis = $OrderList->getOrderAnalysisCount($params);
		$indicator = array();
		$indicator['ord_pnum']	= $analysis['pnum'];
		$indicator['ord_skc']	= $analysis['skc'];
		$indicator['ord_num']	= $analysis['num'];
		$indicator['ord_amount']	= $analysis['price'];
        if($type==1){
		  $indicator['ord_discount_amount'] =$analysis['discount_price'];
		}else{
          $indicator['ord_discount_amount'] =$analysis['zd_discount_price'];
        }
        $this->update($indicator, "id={$id}");
    }

    public function get_user_indicator_list ($user_id,$fliter=1) {
        $options['limit']   = 100;
        $options['order']	= "field,keyword_id,field2,keyword_id2";
        $condition[]    = "user_id={$user_id}";
        if($fliter){
            $condition[] = "type in (1,2,3,4)";
        }
        $where  = implode(" AND ", $condition);
        return $this->find($where, $options);
    }
    
    public function get_user_indicator_list_admin ($user_id) {
        $options['limit']   = 100;
        $options['order']	= "field";
        $condition[]    = "user_id={$user_id}";
        $condition[]    = "field2=''";
        $User           = new User($user_id);
        $condition[]    = "type={$User->type}";
        $where  = implode(" AND ", $condition);
        return $this->find($where, $options);
    }
    
    public function set_indicator ($user_id, $field, $keyword_id,$field2,$keyword_id2, $data) {
        $User   =   new User($user_id);
        
        if($User->type == 3 && $User->username == "0") {
            $type = 4;
        }else{
            $type = $User->type;
        }
        $data['user_id']    = $user_id;
        $data['type']       = $type;
        $data['field']      = $field;
        $data['keyword_id'] = $keyword_id;
        $data['field2']      = $field2;
        $data['keyword_id2'] = $keyword_id2;
        return $this->create($data)->insert(true);
    }
    
    public function set_indicator_user($user_id,$type,$exp_num,$exp_amount){
        $data['user_id']    =   $user_id;
        $data['type']       =   $type;
        $data['exp_num']    =   $exp_num;
        $data['exp_amount'] =   $exp_amount;
        
        return $this->create($data)->insert(true);
    }

    public function get_indicator ($user_id, $field='', $keyword_id=0) {
    	$condition[]	= "user_id={$user_id}";
    	$condition[]	= "field='{$field}'";
    	$condition[]	= "keyword_id={$keyword_id}";
        $condition[]    = "field2=''";
        $condition[]    = "keyword_id2=0";
    	$where 	= implode(" AND ", $condition);
    	return $this->findone($where);
    }
    
    public function get_indicator_type_list($where,$options=array(),$type=1){
        switch ($type) {
            case 5:
                $options['tablename']  =   "user_indicator as ui left join location as l on ui.user_id=l.id";
                $options['order']      =   "l.id,ui.field,ui.keyword_id,ui.field,ui.keyword_id2";
                $options['fields']     =   "ui.*,l.id,l.name";
                break;
            case 6:
                $options['tablename']  =   "user_indicator as ui";
                $options['order']      =   "ui.user_id,ui.field,ui.keyword_id,ui.field,ui.keyword_id2";
                $options['fields']     =   "ui.*";
                break;
            default:
                $options['tablename']  =   "user_indicator as ui left join user as u on ui.user_id=u.id";
                $options['order']      =   "u.id,ui.field,ui.keyword_id,ui.field,ui.keyword_id2";
                $options['fields']     =   "ui.*,u.id,u.username,u.name,u.type,(ui.ord_num/ui.exp_num) as num_percent,(ui.ord_discount_amount/ui.exp_amount) as price_percent";
                if($options['order_more']){
                    $options['order'] = $options['order_more'] . " DESC," . $options['order']; 
                }
                break;
        }
    	return $this->find($where,$options);
    }
    
    public function get_gather_info($where,$options=array()){
        $options['fields']      =   "sum(ui.exp_num) as exp_num,sum(ui.exp_amount) as exp_amount,sum(ui.ord_num) as ord_num,sum(ui.ord_amount) as ord_amount,sum(ui.ord_discount_amount) as ord_discount_amount";
        $options['tablename']   =   "user_indicator as ui left join user as u on ui.user_id=u.id";
        //$options['db_debug']    =   true;
        return $this->findone($where,$options);
    }
    
    public function refresh_indicator(){
            //更新区域指标
            $options['tablename']   =   "user_indicator as ui left join user as u on ui.user_id=u.id";
            $options['fields']      =   "u.area1,sum(ui.exp_num) as exp_num,sum(ui.exp_amount) as exp_amount";
            $options['group']       =   "u.area1";
            $where = "ui.type=1 and ui.field='' and u.area1!=0";
            $area1_list=$this->find($where,$options);
            foreach ($area1_list as $key => $value) {
                $this->set_indicator_user($value['area1'],5,$value['exp_num'],$value['exp_amount']);
            }
            //更新客户类型指标
            $options['fields']      =   "u.property,sum(ui.exp_num) as exp_num,sum(ui.exp_amount) as exp_amount";
            $options['group']       =   "u.property";
            $property_list=$this->find($where,$options);
            foreach ($property_list as $key => $value) {
                $this->set_indicator_user($value['property'],6,$value['exp_num'],$value['exp_amount']);
            }
    }
}
