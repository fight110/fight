<?php

class ProductsAttributeFactory Extends BaseClass {
    private $is_created = false;
    private $_factory_list  = array(
        'price_band',
        'wave',
        'style',
        'series',
        'category',
        'medium',
        'classes',
        'size',
        'color',
        'user_level',
        'theme',
        'season',
        'sxz',
        'neiwaida',
        'changduankuan',
        'nannvzhuan',
        'brand',
        'color_group',
        'size_group',
        'fabric',
        'df1',
        'df2',
        'df3',
        'df4',
        'df5',
        'property',
        'edition',
        'contour',
        'main_push',
        'designer'
    );
    private $_error         = false, $data = array();

    public function __construct($field){
        $this->setFactory("products_attr");
        if(in_array($field, $this->_factory_list)){
            $this->field            = $field;
            $this->data['field']    = $field;
        }else{
            $this->_error   = "field[{$field}] is not exist";
        }
    }

    public function getError(){
        return $this->_error;
    }

    public function createItem($name){
        $Keywords   = new Keywords;
        $kid        = $Keywords->getKeywordId($name);
        if($kid){
            $this->createItemByKid($kid);
        }
        return $kid;
    }

    public function createItemByKid($kid){
        $object = $this->data;
        $object['keyword_id']   = $kid;
        $target = $this->create($object);
        $this->is_created = true;
        $id = $target->insert();
        $this->update(array("rank"=>$id), "id={$id}");
        return $id;
    }

    public function getAllList($params=array()){
        $that = $this;
        $field = $this->data['field'];
        $cache_time     = is_numeric($params['cache_time']) ? $params['cache_time'] : 60;
        $cache  = new Cache(function() use ($that, $field){
            $where  = "field='{$field}'";
            $list   = $that->find($where, array("limit" => 1000, "order" => "rank"));
            foreach($list as &$row){
                $row['keywords']['name']    = Keywords::cache_get(array($row['keyword_id']));
            }
            return $list;
        }, $cache_time);
        if($cache_time == 0){
            $this->is_created = true;
        }
        return $cache->get("ProductAttrList_{$this->data['field']}", array());
    }

    public function getAllHash(){
        $where  = "field='{$this->data['field']}'";
        $list   = $this->find($where, array("limit" => 1000, "order" => "rank"));
        $hash   = array();
        foreach($list as &$row){
            $hash[$row['keyword_id']]   = $row;
        }
        return $hash;
    }

    public function getNameHash(){
        $options['tablename'] = "products_attr as pa left join keywords as k on pa.keyword_id=k.id";
        $options['fields']    = "pa.keyword_id,k.name";
        $options['limit']     = "1000";
        $where  = "field='{$this->data['field']}'";
        $list   = $this->find($where, $options);
        $hash   = array();
        foreach ($list as $key => &$row) {
            $hash[$row['name']] = $row;
        }
        return $hash;
    }

    public function get_group_list($key=false){
        $tablename  = "products_attr as a left join products_attr_group as g on g.attr_id=a.keyword_id";
        $fields     = "a.*,g.group_id,g.id as gid";
        $condition[]    = "a.field='{$this->field}'";
        $where      = implode(' AND ', $condition);

        $options['tablename']   = $tablename;
        $options['fields']      = $fields;
        $options['key']         = $key;
        $options['limit']       = 1000;
        $options['order']       = "a.rank";
        // $options['db_debug']    = true;
        $list       = $this->find($where, $options);
        return $list;
    }

    public static function fetch($list, $field, $column_id, $t=null){
        $list = Flight::listFetch($list, "products_attr", $column_id, "keyword_id", "field='{$field}'", $t);
        return $list;
    }

    public function __destruct() {
        if($this->is_created){
            Cache::clearCache("ProductAttrList_{$this->data['field']}");
        }
    }


}




