<?php
    namespace home;

    use models\BaseDao;

    class Order extends Home {
        // 加入购物车
        function cartadd() {
            $db = new BaseDao();

            $cart['atime'] = time();  // 购物时间
            $cart['pid'] = intval($_GET['pid']);   // 选购的商品ID
            $cart['pnum'] = intval($_GET['pnum']); // 选购的商品数量


            // 从数据库中查找用户选购商品的信息
            $product = $db->get('product', '*', ['id'=>$cart['pid']]);


            $result = '-1';

            if($product['num'] >= $cart['pnum']) {
                // 如果是登录用户， 就用表来管理购物车
                if(ew_login('user')) {
                    $cart['uid'] = $_SESSION['id'];

                    //先查询cart表， 看当前用户是否购买过这个商品
                    $carttab = $db->get('cart', "*", ['uid'=>$cart['uid'], 'pid'=>$cart['pid']]);

                    if($carttab['pnum']) {
                        $result = $db -> update('cart', ['pnum[+]'=>$cart['pnum']], ['id'=>$carttab['id']]) ? true : false;
                    }else{
                        $result = $db -> insert('cart', $cart) ? true : false;
                    }

                }else{

                    // 从Cookie中获取购物记录
                    $cart_list = unserialize(stripcslashes($_COOKIE['cart_list']));

                    $product_index = $cart['pid'];   // 每条记录的下标

                    // 如果购物车已经有相同商品， 只增加数量
                    if(is_array($cart_list[$product_index])){
                        $cart_list[$product_index]['pnum'] = $cart_list[$product_index]['pnum'] + $cart['pnum'];
                    }else {

                        $cart_list[$product_index] = $cart;  //在数组中作为购物的一条数据
                    }

                    $result = is_array($cart_list[$product_index]) ? true : false;
                    // 在COOKIE中添加记录
                    setcookie('cart_list', serialize($cart_list), 0, '/');
                }

            }

            echo json_encode(array('result'=>$result));
            exit;
        }

        function add() {
            $db = new BaseDao();

            $cart_list = cart_list();

            $info_list = $cart_list['list'];

            $this->assign('info_list', $info_list);

            $money = $cart_list['money'];

            $this->assign('money', $money);


            //如果订单提交
            if(isset($_POST['do_submit'])) {

                //如果没用够买商品， 就不能生成订单
                !count($info_list) && $this->error('/order/add', "购物车商品为空...");

                //如果没有选择支付方式，也不能生成订单
                !$_POST['payway'] && $this->error('/order/add', "请选择支付方式...");

                $order_count = $db->count('order');

                if($order_count == 0) {
                    $sql_order['id'] = date('md').'001';
                }

                $sql_order['productmoney'] = $money['order_productmoney'];
                $sql_order['wlmoney'] =  $money['order_wlmoney'];
                $sql_order['money'] = $money['order_money'];
                $sql_order['atime'] = time();
                $sql_order['payway'] =  $_POST['payway'];
                $sql_order['content'] =  $_POST['content'];

                if(ew_login('user')) {
                    $sql_order['uid'] = $_SESSION['id'];
                    $sql_order['uname'] = $_SESSION['name'];
                }

                $sql_order['utname'] = $_POST['tname'];
                $sql_order['uphone'] = $_POST['phone'];
                $sql_order['uaddress'] = $_POST['province'].$_POST['city'].$_POST['address'];

            //    dd($sql_order);

                // 入库
                if($db->insert('order', $sql_order)) {
                    $order_id = $db->id();

                    foreach($info_list as $v){
                        $sql_orderdata['pid'] = $v['pid'];
                        $sql_orderdata['pname'] = $v['name'];
                        $sql_orderdata['plogo'] = $v['logo'];
                        $sql_orderdata['pmoney'] = $v['money'];
                        $sql_orderdata['pnum'] = $v['pnum'];
                        $sql_orderdata['oid'] = $order_id;

                        $db->insert('orderdata', $sql_orderdata);

                        //将产品库存减少
                        $db->update('product', ['num[-]'=>$v['pnum']], ['id'=>$v['pid']]);

                    }

                    //清空购物车
                    if(ew_login('user')) {
                        $db->delete('cart', ['uid'=>$_SESSION['id']]);
                    }else{
                        setcookie('cart_list', '', 0, '/');
                    }

                    $this->success('/order/pay/'.$order_id, '订单提交成功');


                }else{
                    $this->error('/order/add', '订单提交失败');
                }

            }





            $tmpay = $db->select('payway', "*", ['state'=>1,'ORDER'=>["ord"=>'ASC', 'id'=>'ASC']]);

            $payway = [];

            foreach($tmpay as $v) {
                $payway[$v['mark']] = $v;
            }

            $this->assign('payway', $payway);

            // 收货信息
            $this->assign('info', $db->get('user','*', ['id'=>$_SESSION['id']]));



            $this->assign('title', '填写收货信息');
            $this->display('order/add');
        }

        function cartnum() {
            $db = new BaseDao();

            $pid = $_GET['pid'];
            $pnum = $_GET['pnum'];
            $where = ['uid'=>$_SESSION['id'], 'pid'=>$pid];
            if(ew_login('user')) {
                $result = $pnum ? $db->update('cart', ['pnum'=>$pnum], $where) : $db->delete('cart', $where);
            }else{
                $cart_list = unserialize(stripcslashes($_COOKIE['cart_list']));

                if($pnum) {
                    $cart_list[$pid]['pnum'] = $pnum;
                    $result = is_array($cart_list[$pid]) ? true : false;
                }else{
                    unset($cart_list[$pid]);
                    $result = is_array($cart_list[$pid]) ? false : true;
                }

                //重新写回Cookie
                setcookie('cart_list', serialize($cart_list), 0, '/');
            }



            // 重新获取价格
            $cart_list = cart_list();

            echo json_encode(['result'=>$result, 'money'=>$cart_list['money']]);
            exit;

        }

        // 选择支付方式
        function pay($order_id) {

            $db = new BaseDao();


            $tmpay = $db->select('payway', "*", ['state'=>1,'ORDER'=>["ord"=>'ASC', 'id'=>'ASC']]);

            $payway = [];

            foreach($tmpay as $v) {
                $payway[$v['mark']] = $v;
            }

            $this->assign('cache_payway', $payway);


            $order = $db -> get('order', '*', ['id'=>$order_id, 'state'=>1]);

            !$order['id'] && $this->error('/order/pay/'.$order_id, "订单号错误");

            $this->assign($order);



            $this->assign('title', '选择支付方式');
            $this->display('order/pay');
        }


        function plist() {
            $db = new BaseDao();
            $info = $db -> get('order', '*', ['id'=>$_GET['id'], 'uphone'=>$_GET['uphone']]);
            $product_list = $db->select('orderdata', '*', ['oid'=>$_GET['id']]);


            $this->assign($info);
            $this->assign('product_list', $product_list);


            $this->assign('title', '订单查询');
            $this->assign('get', $_GET);
            $this->display('order/plist');
        }
    }

    //购物车列表 --- 商品列表 --- 价格
    function cart_list() {
        $db = new BaseDao();

        if(ew_login('user')) {
            $cart_list = $db->select('cart', '*', ['uid'=>$_SESSION['id']]);
        }else{
            $cart_list = unserialize(stripcslashes($_COOKIE['cart_list']));
        }

        $money = [];
        $info_list = [];

        $money['order_productmoney'] =0;
        $money['order_wlmoney'] = 0;

        foreach($cart_list as $v){

            $product = $db -> get('product', ['id(pid)', 'name', 'logo', 'money', 'wlmoney', 'num(product_maxnum)'], ['id'=>$v['pid']]);

            $product_index = $v['pid'];
            $info_list[$product_index] = $product;
            $info_list[$product_index]['pnum'] = intval($v['pnum']);

            $money['order_productmoney'] += $v['pnum'] *  $product['money'];
            $money['order_wlmoney'] += $product['wlmoney'];
        }

        $money['order_money'] = $money['order_productmoney'] + $money['order_wlmoney'];

        $money['order_money'] = number_format($money['order_money'], 1, '.', '');
        $money['order_productmoney'] = number_format($money['order_productmoney'], 1, '.', '');
        $money['order_wlmoney'] = number_format($money['order_wlmoney'], 1, '.', '');

        return ['list'=>$info_list, 'money'=>$money];
        exit;
    }