<?php

class GroupToDisplay Extends BaseClass {
    public function __construct($id=null){
        $this->setFactory('group_to_display');
    }

    public function getGroupMember($id,$option){
        if(!id){
            return false;
        }else{
            //$option['db_debug'] = true;
            $option['key'] = 'group_id';
            $option['tablename'] = 'group_to_display gtd left join product_group pg on gtd.group_id = pg.id';
            $option['order'] = 'group_id';
            $condition  = array();
            $condition[] = ' gtd.display_id = "'.$id.'" ';
            $where  = implode(" AND ", $condition);
            $result = $this->find($where,$option);         
            return $result;
        }
    }

    public function createGroupToDisplay($group_id, $display_id){
        return $this->create(array('group_id'=>$group_id, 'display_id'=>$display_id))->insert(true);
    }

    public function getDisplayBySearch($q,$t,$option){
        if($q){
            //$option['db_debug'] = true;
            $option['fields'] = 'pg.name as gname,pg.id as gid, pg.dp_num,pg.defaultimage gimg,pd.id as did,pd.name as dname,pd.defaultimage as dimg,pd.bianhao';
            //$option['tablename'] = ' group_to_display gtd , product_group pg , product_display pd , product_color pc , product p , product_display_member_color pdmc';
            $option['tablename'] = ' product_group_member pgm left join product_color pc on pgm.product_id=pc.product_id AND pgm.color_id=pc.color_id  left join group_to_display gtd on pgm.group_id=gtd.group_id left join  product_group pg on pg.id=pgm.group_id left join product_display pd on gtd.display_id=pd.id ';
            $option['order'] = 'gtd.display_id,gtd.group_id';
            $option['group'] = 'gtd.display_id,gtd.group_id';
            $cond = array();
            //$cond[] = ' gtd.display_id = pd.id AND pd.status =1 AND gtd.group_id = pg.id AND gtd.display_id = pdmc.display_id AND pdmc.product_id = p.id AND pdmc.product_id = pc.product_id AND pdmc.keyword_id = pc.color_id   ';
            $cond[] = 'pc.status = 1  AND pd.status=1 ';
            if($t=='skc_id'){
                $cond[] = 'pc.skc_id = "'.$q.'"';
            }else{
                $cond[] = '( (pg.dp_num="'.$q.'") or  (pc.skc_id = "'.$q.'") )';
            }
            
            $where  = implode(" AND ", $cond);
            $res = $this->find($where,$option);
            return $res;
        }
        return false;
    }
}




