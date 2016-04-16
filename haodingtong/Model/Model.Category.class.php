<?php

class Category {
    private $_node      = array();
    public function __construct(){
        $this->factory  = DataFactory::getInstance('category');
    }

    public function getCategoryChain($pid){
        $currentCategory    = $this->factory->select($pid);
        if(!$currentCategory->id) return array();

        $where              = "lft<={$currentCategory->lft} AND rgt>={$currentCategory->rgt} order by lft";
        $chain              = $this->factory->saver->getList($where);
        return $chain;
    }
    public function addNode($pid, $name){
        if($pid == 0){
            $parent         = (object) array('id' => 0, 'lft' => 1);
        }else{
            $parent         = $this->getNode($pid);
        }
        if(!$parent->lft) return false;

        $node   = $this->factory->create(array('lft' => $parent->lft + 1, 'rgt' => $parent->lft + 2, 'pid' => $pid, 'name' => $name));
        $this->factory->saver->updateList("lft>={$node->lft}", array('set' => "lft=lft+2"));
        $this->factory->saver->updateList("rgt>={$node->lft}", array('set' => "rgt=rgt+2"));
        $node->insert();
        return $node;
    }
    public function removeNode($id){
        if($id == 0){
            $node           = (object) array('id' => 0, 'lft' => 1);
        }else{
            $node           = $this->getNode($id);
        }
        if(!$node->lft) return false;

        $distance           = $node->rgt - $node->lft + 1;
        $this->factory->saver->deleteList("lft>={$node->lft} AND rgt<={$node->rgt}");
        $this->factory->saver->updateList("lft>={$node->lft}", array('set' => "lft=lft-{$distance}")); 
        $this->factory->saver->updateList("rgt>={$node->lft}", array('set' => "rgt=rgt-{$distance}")); 
        return true;
    }
    public function getNode($id){
        if($node = $this->_node[$id])   return $node;
        $node               = $this->factory->select($id);
        $this->_node[$id]   = $node;
        return $node;
    }
    public function getCurrent($id){
        $node   = $this->getNode($id);
        return $node->getData();
    }
    public function getChildren($pid){
        if(!is_numeric($pid))   return array();

        $children           = $this->factory->saver->getList("pid={$pid} order by lft desc", array('limit' => 100));
        return $children;
    }
    public function getAllChildren($pid){
        if($pid == 0){
            $where          = "1 order by lft desc";
        }else{
            $currentCategory    = $this->factory->select($pid);
            if(!$currentCategory->id) return array();

            $where              = "lft>{$currentCategory->lft} AND rgt<{$currentCategory->rgt} order by lft desc";
        }
        $all                = $this->factory->saver->getList($where, array('limit' => 10000));
        return $all;
    }
    public function getChildrenTree($pid){
        $all    = $this->getAllChildren($pid);
        $source = array();
        foreach($all as $row){
            $source[$row['pid']][]    = $row;
        }
        $tree   = $this->buildTree($source, $pid);
        return $tree;
    }
    public function buildTree($source, $pid){
        if(!is_array($source) || !array_key_exists($pid, $source))  return array();
        $list   = $source[$pid];
        foreach($list as &$row){
            if(array_key_exists($row['id'], $source)){
                $row['children']    = $this->buildTree($source, $row['id']);
            }
        }
        return $list;
    }

    public function getIdByName($name, $pid=null){
        $condition[]    = "name='".addslashes($name)."'";
        if(null !== $pid){
            $condition[]    = "pid={$pid}";
        }
        $where  = implode(" AND ", $condition);
        $list   = $this->factory->saver->getList($where);
        return $list[0]['id'];
    }
}




