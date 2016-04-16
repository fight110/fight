<?php

class UserTarget extends BaseClass
{

    public function __construct($id = null)
    {
        $this->setFactory('user_target');
    }

    public function getUserTargetOrder($user_id, $key, $key_id = '')
    {
        if (! $user_id) {
            return false;
        }
        $options = array();
        $condition = array();
        $userTarget = array();
        $condition['user_id'] = $user_id;
        $order_list = new OrderList();
        $options['group'] = 'o.user_id,' . $key;
        // $options['db_debug']=true;
        $options['fields_more'] = $key . ' as other_id ';
        $options['key'] = 'other_id';
        $userOrderList = $order_list->getOrderUserList($condition, $options);
        $userTarget = $this->find('user_id="' . $user_id . '"');
        foreach ($userTarget as $key => $val) {
            $other_id = $val['other_id'];
            $userTarget[$key]['orderinfo'] = array(
                'num' => $userOrderList[$other_id]['num'],
                'price' => $userOrderList[$other_id]['price'],
                'discount_price' => $userOrderList[$other_id]['discount_price']
            );
            if ($val['target_num']) {
                $userTarget[$key]['percent_exp_num'] = sprintf("%d%%", $userOrderList[$other_id]['num'] / $val['target_num'] * 100);
            }
            if ($val['target_price']) {
                $userTarget[$key]['percent_exp_price'] = sprintf("%d%%", $userOrderList[$other_id]['discount_price'] / $val['target_price'] * 100);
            }
        }
        return $userTarget;
    }
}




