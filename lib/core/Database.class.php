<?php

class Database extends PDO {
    function __construct($dsn, $username="", $password="", $driver_options=array()) {
        parent::__construct($dsn,$username,$password, $driver_options);
        $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
        // $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('DBStatement', array($this)));
    }
}

class Database1 extends PDO {
    function __construct($dsn, $username="", $password="", $driver_options=array()) {
        parent::__construct($dsn,$username,$password, $driver_options);
        $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
        // $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('DBStatement', array($this)));
    }
}

// class DBStatement extends PDOStatement {
//     public $dbh;
//     protected function __construct($dbh) {
//         $this->dbh = $dbh;
//     }
// }

// mysql table
//      methods :
//          insert
//          update
//          delete
class table {
    function __construct($tablename, $column_names=array(), $dbh=NULL) {
        if(!$tablename)                 throw new Exception("tablename cannot be null");
        if(count($column_names) < 1)    throw new Exception("table[{$tablename}] column cannot be null");
        $this->tablename    = $tablename;
        $this->column_names = $column_names;
        $this->dbh = $dbh ? $dbh : Flight::DatabaseMaster();
    }

    function insert($contents=array()){
        foreach($this->column_names as $column){
            $keys[] = '?';
            $vals[] = $contents[$column];
        }
        $sql = "INSERT INTO {$this->tablename} ("
            . join(',', $this->column_names). ") VALUES (" 
            . join(',', $keys) 
            . ")";
        $sth = $this->dbh->prepare($sql);
        $sth->execute($vals);
        $lastInsertId = $this->dbh->lastInsertId();
        return $lastInsertId;
    }
    function getList($where="1", $limit=10, $start=0, $fields="*"){
        $sql = "SELECT $fields FROM {$this->tablename} WHERE $where";
        if($limit) $sql .= " LIMIT $start, $limit";
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $list = $sth->fetchAll();
        return $list;
    }
    function getLine($where="1", $fields="*"){
        $sql = "SELECT $fields FROM {$this->tablename} WHERE $where LIMIT 1";
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $row = $sth->fetch();
        return $row;
    }
    function update($where, $contents, $options=array()){
        if(!$where){
            if($options['FORCE'] == TRUE)
                $where = "1";
            else
                throw new Exception("update[{$this->tablename}] where cannot be null");
        }

        if(count($contents) < 1) throw new Exception("update[{$this->tablename}] set is null");
        foreach($contents as $column => $value){
            $list[] = "$column=" . $this->dbh->quote($value);
        }
        $set = join(',', $list);
        $sql = "UPDATE {$this->tablename} SET {$set} WHERE $where";
        // echo $sql;
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        return $sth->rowCount();
    }
    function delete($where, $options=array()){
        if(!$where){
            if($options['FORCE'])
                $where = "1";
            else
                throw new Exception("delete[{$this->tablename}] where cannot be null");
        }
        $sql = "DELETE FROM {$this->tablename} WHERE $where";
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        return $sth->rowCount();
    }
}







