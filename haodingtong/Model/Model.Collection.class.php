<?php

abstract class Collection { 
    // implements Iterator
    // private $_list;
    private $_condition = array();
    private $_join;
    public $factory;

    public function joinWith($join){
        $this->_join            = $join;
        foreach($join as $key => $val){
            // $class = get_called_class();
            // echo "SET JOIN CONDITION [$class]: $key=$val<br>";
            $this->addCondition("$key=$val");
        }
    }
    private function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
    public function getJoin(){
        return $this->_join;
    }
    public function buildEmptyData($attributes=array()){
        $instance   = $this->getEmptyData($attributes);
        $instance->insert();
        return $instance;
    }
    public function getEmptyData($attributes=array()){
        $attributes = $attributes + $this->getJoin();
        $instance   = $this->create($attributes);
        return $instance;
    }
    public function addCondition($string){
        $this->_condition[]     = $string;
    }
    public function getCondition(){
        return implode(' AND ', $this->_condition);
    }
    public function setFactory($tablename){
        $this->tablename    = $tablename;
        $this->factory      = DataFactory::getInstance($tablename);
        $this->dbh          = $this->factory->saver->dbh;
        $this->dbh_slave    = $this->factory->saver->dbh_slave;
    }
    public function find($whereString="", $options=array()){
        $start      = $options['start']     ? $options['start']     : 0;
        $limit      = $options['limit']     ? $options['limit']     : 10;
        $fields     = $options['fields']    ? $options['fields']    : '*';
        $table      = $options['tablename'] ? $options['tablename'] : $this->tablename;
        $count      = $options['count']     ? $options['count']     : false;
        $key        = $options['key'];
        if($options['page'])    $start      = $limit * ($options['page'] - 1);
        $where      = $this->getWhereString($whereString, $options);
        if(!$where) $where = "1";
        $where     .= " LIMIT $start, $limit";
        if($count){
            $fields = "SQL_CALC_FOUND_ROWS ". $fields;
        }
        $sql        = "SELECT {$fields} FROM {$table} WHERE $where";
        if($options['db_debug'] === true){
            $this->message($sql);
            $time_start = $this->microtime_float();
            // $this->message("开始时间:" . $time_start);
        }
        $sth        = $this->dbh_slave->prepare($sql);
        $sth->execute();
        if($options['db_debug'] === true){
            $time_end = $this->microtime_float();
            $run_time = $time_end - $time_start;
            // $this->message("结束时间:" . $time_end);
            $this->message("执行时间:" . $run_time);
            $this->run_time += $run_time;
        }
        $result = array();
        if($key){
            while($row  = $sth->fetch()){
                $result[$row[$key]] = $row;
            }
        }else{
            while($row  = $sth->fetch()){
                $result[] = $row;
            }
        }
        if($count){
            $sth = $this->dbh_slave->prepare("SELECT FOUND_ROWS() as total");
            $sth->execute();
            $countinfo      = $sth->fetch();
            $this->_total   = $countinfo['total'];
        }
        return $result;
    }
    public function get_count_total(){
        return $this->_total;
    }
    public function findone($whereString="", $options=array()){
        $list       = $this->find($whereString, $options);
        $one        = $list[0]  ? $list[0] : array();
        return $one;
    }
    
    public function getCount($whereString="", $options=array()){
        $table      = $options['tablename'] ? $options['tablename'] : $this->tablename;
        $where      = $this->getWhereString($whereString, $options);
        $sql        = "SELECT COUNT(*) as total FROM {$table} WHERE $where";
        if($options['db_debug'] === true){
            echo $sql,"<br>";
        }
        $sth        = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $row        = $sth->fetch();
        return $row['total'];
    }
    public function update($data, $whereString, $options=array()){
        foreach($data as $key => $val){
            if($options['quote'] !== false) $val    = $this->dbh->quote($val);
            $array[]    = "$key=$val";
        }
        $set        = implode(',', $array);
        $where      = $this->getWhereString($whereString, $options);
        if(!$set || !$where)   return false;
        $sql        = "UPDATE {$this->tablename} SET $set WHERE $where";
        $sth        = $this->dbh->prepare($sql);
        $sth->execute();
        return $sth->rowCount();
    }
    public function delete($whereString, $options=array()){
        $where      = $this->getWhereString($whereString, $options);
        if(!$where) return false;
        $sql        = "DELETE FROM {$this->tablename} WHERE $where";
        $sth        = $this->dbh->prepare($sql);
        $sth->execute();
        return $sth->rowCount();
    }
    public function getWhereString($where, $options){
        $where_ary  = array();
        $order      = $options['order']     ? "ORDER BY {$options['order']}"    : '';
        $group      = $options['group']     ? "GROUP BY {$options['group']}"    : '';
        if($default = $this->getCondition())    $where_ary[]    = $default;
        if($where)                              $where_ary[]    = $where;
        if(!count($where_ary))                  $where_ary[]    = "1";
        $where      = implode(' AND ', $where_ary);
        if($group){
            $where .= " $group";
            if($having = $options['having']){
                $where  .= " having {$having}";
            }
        }
        if($order){
            $where .= " $order";
        }
        return $where;
    }
    public function create($data){
        return $this->factory->create($data);
    }

    // public function rewind() {
    //     return reset($this->_list);
    // }
    // public function current() {
    //     return current($this->_list);
    // }
    // public function key() {
    //     return key($this->_list);
    // }
    // public function next() {
    //     return next($this->_list);
    // }
    // public function valid() {
    //     return key($this->_list) !== null;
    // }

    public function message ($message){
        $this->message_id += 1;
        echo "<p>{$this->message_id} : {$message}</p>\r\n";
    }

    public function __destruct () {
        if($this->run_time) {
            $this->message("总共执行时间:{$this->run_time}");
        }
    }
}




