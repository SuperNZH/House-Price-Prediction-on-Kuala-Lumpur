<?php
    namespace home;

    use JasonGrimes\Paginator;
    use models\BaseDao;

    class User extends Home {
        function login() {

            if(isset($_POST['do_submit'])) {
                $name = $_POST['name'];

                $pw =  md5(md5('ew_'.$_POST['pw']));

                $db = new BaseDao();

                $user = $db->get('user', ['id', 'name'], ['name'=>$name, 'pw'=>$pw]);

                if($user) {
                    $db -> update('user', ['ltime'=>time()], ['id'=>$user['id']]);

                    $_SESSION = $user;

                    $_SESSION['user_token'] = md5($user['id'].$_SERVER['HTTP_HOST']);

                    // 将COOKIE购物车和CART表购物车合并
                    $cart_list = unserialize(stripcslashes($_COOKIE['cart_list']));

                    if(is_array($cart_list)) {
                         // 先把表中的购物记录取出
                        $data = $db -> select('cart', '*', ['uid'=>$user['id']]);

                        //将表的数组格式转成Cookie的CART格式
                        $cart_rows = [];

                        foreach($data as $v){
                            $cart_rows[$v['pid']] = $v;
                        }

                        //将COOKIE和CART表合并
                        foreach($cart_list as $k => $v) {
                            if(array_key_exists($k, $cart_rows)) {
                                $db -> update('cart', ['pnum'=>intval($cart_rows[$k]['pnum'] + $cart_list[$k]['pnum'])], ['id'=>intval($cart_rows[$k]['id'])]);
                            }else{

                                $cart_info['atime'] = time();
                                $cart_info['pid'] = intval($k);
                                $cart_info['pnum'] = intval($v['pnum']);
                                $cart_info['uid'] = $user['id'];

                                $db -> insert('cart', $cart_info);
                            }
                        }

                        setcookie('cart_list', '', time()-3600, '/');

                    }


                    $this->success('/', "用户【".$user['name']."】登录成功");

                }else{
                    $this->error('/user/login', '用户名和密码错误');
                }

            }


            $this->assign('title', '用户登录');
            $this->display('user/login');
        }

        function logout() {
            $username = $_SESSION['name'];

            $_SESSION = array();

            if(isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time()-3600, '/');
            }

            session_destroy();

            $this->success('/user/login', '再见【'.$username.'】，退出成功');
        }

        function register() {
            $db = new BaseDao();

            if(!empty($_GET['name'])) {
                $finduser = $db->get('user', ['id', 'name'], ['name'=>$_GET['name']]);

                if($finduser) {
                    echo "false";
                }else{
                    echo "true";
                }
                die();
            }

            if(isset($_POST['do_submit'])) {
                // 验证 ....   和前端一样都要再完成一次验证

                $_POST['pw'] = md5(md5('ew_'.$_POST['pw']));
                $_POST['atime'] = $_POST['ltime'] = time();

                unset($_POST['do_submit']);
                unset($_POST['pw1']);

                if($db->insert('user', $_POST)) {
                    $lastinsertid = $db->id();

                    $user = $db -> get('user', ['id', 'name'], ['id'=>$lastinsertid]);



                    $_SESSION = $user;

                    $_SESSION['user_token'] = md5($user['id'].$_SERVER['HTTP_HOST']);

                    // 注册以后直接登录。

                    $this->success('/', "注册成功");
                }else{
                    $this->error('/user/register', '注册失败');
                }

            }



            $this->assign('title', '用户注册');
            $this->display('user/register');
        }

        // order 我的订单列表
        function order() {

            //获取数据库操作对象
            $db = new BaseDao();

            $num = $_GET['num'] ?? 1;

            $prosql['ORDER'] = ["id"=>"DESC"];
            $where['uid'] = $_SESSION['id'];



            $this->assign('get', $_GET);


            $totalItems = $db->count('order', $where);
            $itemsPerPage = 3;
            $currentPage = $num;
            $urlPattern = '/user/order?num=(:num)';

            $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);


            $start = ($currentPage-1) * $itemsPerPage;
            $prosql['LIMIT'] = [$start, $itemsPerPage];

            $prosql = array_merge($prosql, $where);
            //获取全部的订单, 并能按ord排序
            $data = $db->select('order','*',$prosql);



            foreach($data as $k => $v) {
                $data[$k]['orderdata'] = $db->select('orderdata', '*', ['oid'=>$v['id']]);
            }


            $this->assign('paywayarr', ['1'=>'支付宝', '2'=>'转账付款', '3'=>'货到付款']);

            // 将数据分给模版
            $this->assign('data', $data);
            $this->assign('fpage', $paginator);

            $this->assign('title', '我的订单');
            $this->assign('get', 'order');
            $this->display('user/order');
        }

        //订单详情
        function orderview($order_id){

            $db = new BaseDao();

            $info = $db -> get('order', '*', ['id'=>$order_id, 'uid'=>$_SESSION['id']]);
            $product_list = $db->select('orderdata', '*', ['oid'=>$order_id]);


            $this->assign($info);
            $this->assign('product_list', $product_list);

            $this->assign('paywayarr', ['1'=>'支付宝', '2'=>'转账付款', '3'=>'货到付款']);



            $this->assign('title', '订单详情');
            $this->assign('get', 'order');
            $this->display('user/orderview');
        }

        //删除订单
        function orderdel($order_id) {
            $this->assign('get', 'order');
            $db = new BaseDao();

            $info = $db->get('order', '*', ['id'=>$order_id, 'uid'=>$_SESSION['id'], 'state'=>1]);



            if($info){
                // 删除订单
                if($db->delete('order', ['id'=>$info['id']])) {
                    $orderdata = $db->select('orderdata', ['id', 'pid', 'pnum'], ['oid'=>$info['id']]);

                    // 库存加回去
                    foreach ($orderdata as $v ) {
                        $db -> update('product', ['num[+]'=>$v['pnum']], ['id'=>$v['pid']]);
                    }


                    // 删除订单详情表对应的数据
                    $db->delete('orderdata', ['oid'=>$order_id]);

                    $this->success('/user/order', '删除成功！');
                }else{
                    $this->error('/user/order', '删除失败！');
                }
            }else{
                $this->error('/user/order', '抱歉,已付款订单不能删除...');
            }




        }

        // 我的收藏
        function collect() {
            //获取数据库操作对象
            $db = new BaseDao();

            $num = $_GET['num'] ?? 1;

            $prosql['ORDER'] = ["collect.id"=>"DESC"];
            $where['collect.uid'] = $_SESSION['id'];

            $totalItems = $db->count('collect', ['[>]product'=>['pid'=>'id']],'*',$where);
            $itemsPerPage = 3;
            $currentPage = $num;
            $urlPattern = '/user/collect?num=(:num)';

            $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);


            $start = ($currentPage-1) * $itemsPerPage;
            $prosql['LIMIT'] = [$start, $itemsPerPage];

            $prosql = array_merge($prosql, $where);
            //获取全部的评价, 并能按ord排序
            $data = $db->select('collect',
                ['[>]product'=>['pid'=>'id']],
                ['collect.id','collect.pid','collect.uid','collect.atime','product.name','product.money','product.logo'],
                $prosql
            );



            // 将数据分给模版
            $this->assign('data', $data);
            $this->assign('fpage', $paginator);


            $this->assign('title', '我的收藏');
            $this->assign('get', 'collect');
            $this->display('user/collect');
        }

        function collectdel() {
            $db = new BaseDao();

            if($db->delete('collect', ['pid'=>$_GET['pid'], 'id'=>$_GET['id']])) {
                $db->update('product', ['collectnum[-]'=>1], ['id'=>$_GET['pid']]);

                $this->success('/user/collect', '商品收藏删除成功!');
            }else {
               $this->error('/user/collect', "商品收藏删除失败...");
            }


            $this->assign('get', 'collect');
        }

        //我的咨询
        function ask() {
            //获取数据库操作对象
            $db = new BaseDao();

            $num = $_GET['num'] ?? 1;

            $prosql['ORDER'] = ["ask.atime"=>"DESC"];
            $where['ask.uid'] = $_SESSION['id'];

            $totalItems = $db->count('ask', ['[>]product'=>['pid'=>'id']],'*',$where);
            $itemsPerPage = 3;
            $currentPage = $num;
            $urlPattern = '/user/ask?num=(:num)';

            $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);


            $start = ($currentPage-1) * $itemsPerPage;
            $prosql['LIMIT'] = [$start, $itemsPerPage];

            $prosql = array_merge($prosql, $where);
            //获取全部的评价, 并能按ord排序
            $data = $db->select('ask',
                ['[>]product'=>['pid'=>'id']],
                ['ask.id','ask.pid','ask.uid','ask.uip','ask.atime','ask.uname','ask.replytext','ask.replytime','ask.state','ask.asktext','product.name','product.logo'],
                $prosql
            );



            // 将数据分给模版
            $this->assign('data', $data);
            $this->assign('fpage', $paginator);

            $this->assign('title', '我的咨询');
            $this->assign('get', 'ask');
            $this->display('user/ask');
        }


        // 我的评价
        function comment() {
            //获取数据库操作对象
            $db = new BaseDao();

            $num = $_GET['num'] ?? 1;

            $prosql['ORDER'] = ["comment.atime"=>"DESC"];
            $where['comment.uid'] = $_SESSION['id'];

            $totalItems = $db->count('comment', ['[>]product'=>['pid'=>'id']],'*',$where);
            $itemsPerPage = 3;
            $currentPage = $num;
            $urlPattern = '/user/comment?num=(:num)';

            $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);


            $start = ($currentPage-1) * $itemsPerPage;
            $prosql['LIMIT'] = [$start, $itemsPerPage];

            $prosql = array_merge($prosql, $where);
            //获取全部的评价, 并能按ord排序
            $data = $db->select('comment',
                ['[>]product'=>['pid'=>'id']],
                ['comment.id','comment.pid','comment.uid','comment.uip','comment.atime','comment.uname','comment.content','product.name','product.logo'],
                $prosql
            );



            // 将数据分给模版
            $this->assign('data', $data);
            $this->assign('fpage', $paginator);



            $this->assign('title', '我的评价');
            $this->assign('get', 'comment');
            $this->display('user/comment');
        }

        // 个人信息的设置
        function base() {
            $db = new BaseDao();

            if(isset($_POST['do_submit'])) {
                unset($_POST['do_submit']);

                if($db->update('user', $_POST, ['id'=>$_SESSION['id']])) {
                    $this->success('/user/base', "基本信息设置成功...");
                }else{
                    $this->error('/user/base', "基本信息设置失败...");
                }
            }


            $this->assign($db->get('user', '*', ['id'=>$_SESSION['id']]));

            $this->assign('title', '基本资料');
            $this->assign('get', 'base');
            $this->display('user/base');
        }

        // 个人密码修改 any
        function pw() {
            $db = new BaseDao();

            if(isset($_POST['do_submit'])) {
                if($_POST['pw'] != $_POST['pw1']) {
                    $this->error('/user/pw', '两次密码输入必须一致...');
                }

                if($db->update('user', ['pw'=>md5(md5('ew_'.$_POST['pw']))],['id'=>$_SESSION['id']])) {
                    $this->success('/user/pw', "新密码设置成功!");
                }else{
                    $this->error('/user/pw', '密码设置失败...');
                }
            }


            $this->assign('title', '修改密码');
            $this->assign('get', 'pw');
            $this->display('user/pw');
        }

    }