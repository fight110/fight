<?php

class Control_comment {
    public static function Action_add($r){
        Flight::validateUserHasLogin();

        $data   = $r->data;
        $user   = new User;
        if($data->score){
            $ProductComment     = new ProductComment;
            switch ($data->score) {
                case 5  : 
                    $data->content  = $data->content ? "很好,{$data->content}!"   : "很好!";
                    break;
                case 4  : 
                    $data->content  = $data->content ? "好,{$data->content}!"     : "好!";
                    break;
                case 3  : 
                    $data->content  = $data->content ? "合格,{$data->content}!"   : "合格!";
                    break;
                case 2  : 
                    $data->content  = $data->content ? "不好,{$data->content}!"   : "不好!";
                    break;
                case 1  : 
                    $data->content  = $data->content ? "差,{$data->content}!"     : "差!";
                    break;
                default : 1;
            }
            $target     = array();
            $target['score']            = $data->score;
            $target['product_id']       = $data->product_id;
            $target['content']          = $data->content;
            $comment    = $ProductComment->create_comment($user->id, $target);
        }else{
            $message    = "提交失败";
        }

        if($message){
            $result     = array('valid'=>false, 'message'=>$message);
        }else{
            if($data->ref == "iphone"){
                $html       = "<li><span class='rate'><em>{$comment['score']}分</em></span><span class='say'><em>说</em>{$comment['content']}</span>";
                $html       .= "<span class='user'>{$user->name}</span></li>";
            }else{
                $html       = "<li><span class='rate'>打了<em>{$comment['score']}分</em></span><span class='say'><em>说</em>{$comment['content']}</span>";
                $Location   = new Location;
                $areaid     = $user->area2  ? $user->area2 : $user->area1;
                $location   = $Location->getCurrent($areaid);
                if($location['name']) $html .= "来自{$location['name']}的";
                $html       .= "<span class='user'>{$user->name}</span></li>";
            }
            
            $result     = array('valid'=>true, 'html'=>$html);
        }

        Flight::json($result);
    }

    public static function Action_list($r, $id){
        Flight::validateUserHasLogin();

        $ProductComment     = new ProductComment;
        $User               = new User;
        $Location           = Cache::getLocation();
        $condition  = array();
        $condition[]    = "product_id={$id}";
        $condition[]    = "user_id={$User->id}";
        $where      = implode(' AND ', $condition);
        $list       = $ProductComment->find($where, array("order"=>"id desc", "limit"=>20, "page"=>$r->query->p));
        $list       = Flight::listFetch($list, 'user', 'user_id', 'id');
        foreach($list as &$row){
            $area1  = $row['user']['area1'];
            $area2  = $row['user']['area2'];
            $row['location']    = $Location->get("Location-{$area1}-{$area2}", array($area1, $area2));
        }

        $result['list'] = $list;

        Flight::display("comment/list.html", $result);
    }

}