<?php

class SaverMysql implements Saver{
    public function __construct($tablename){
        $this->tablename    = $tablename;
        $this->dbh          = Flight::DatabaseMaster();
        $this->dbh_slave    = Flight::DatabaseSlaver();
    }
    public function select($params){
        if(is_numeric($params)){
            $where = "id={$params}";
        }else{
            foreach($params as $column => $val){
                $where_ary[]    = "{$column}={$val}";
            }
            $where = join(' AND ', $where_ary);
        }
        if(!$where) throw new Exception("SQL SELECT where condition is null");

        $sql = "SELECT * FROM {$this->tablename} WHERE $where LIMIT 1";
        // echo $sql;
        $sth = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $row = $sth->fetch();
        return $row; 
    }
    function insert($content, $isDuplicate=false){
        foreach($content as $key => $val){
            $val    = $this->dbh->quote($val);
            $keys[] = $key;
            $vals[] = $val;
            $dups[] = "$key=$val";
        }

        $sql = "INSERT INTO {$this->tablename} ("
            . join(',', $keys). ") VALUES (" 
            . join(',', $vals) 
            . ")";
        if($isDuplicate){
            $sql .= " ON DUPLICATE KEY UPDATE " . join(",", $dups);
        }
        // echo $sql;
// echo $sql;exit;
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $lastInsertId = $this->dbh->lastInsertId();
        return $lastInsertId;
    }
    public function update($id, $content){
        foreach($content as $column => $value){
            $list[] = "$column=" . $this->dbh->quote($value);
        }
        $set = join(',', $list);
        $sql = "UPDATE {$this->tablename} SET {$set} WHERE id=$id";
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        return $sth->rowCount();
    }
    public function delete($id){
        $sql = "DELETE FROM {$this->tablename} WHERE id=$id";
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        return $sth->rowCount();
    }
    public function getList($where="1", $options=array()){
        $fields     = $options['fields']    ? $options['fields']    : '*';
        $start      = $options['start']     ? $options['start']     : 0;
        $limit      = $options['limit']     ? $options['limit']     : 10;
        $order      = $options['order']     ? "ORDER BY {$options['order']}" : '';
        if(is_numeric($options['page'])){
            $start  = $limit * ($options['page'] - 1);
        }
        $sql        = "SELECT {$fields} FROM {$this->tablename} WHERE $where $order LIMIT $start, $limit";
        // echo $sql, "<br>";
        $sth        = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $list       = $sth->fetchAll();
        return $list;
    }
    public function getCount($where="1"){
        $sql        = "SELECT COUNT(*) as total FROM {$this->tablename} WHERE $where";
        $sth        = $this->dbh_slave->prepare($sql);
        $sth->execute();
        $info       = $sth->fetch();
        return $info['total'];
    }
    public function updateList($where="1", $options=array()){
        $set        = $options['set'];
        if(!$set)   return false;

        $sql        = "UPDATE {$this->tablename} set $set WHERE $where";
        $sth        = $this->dbh->prepare($sql);
        $sth->execute();
        return $sth->rowCount();
    }
    public function deleteList($where="1", $options=array()){
        $sql        = "DELETE FROM {$this->tablename} WHERE $where";
        $sth        = $this->dbh->prepare($sql);
        $sth->execute();
        return $sth->rowCount();
    }
    public function prepare($sql){
        $sth        = $this->dbh->prepare($sql);
        $sth->execute();
        return $sth;
    }
}