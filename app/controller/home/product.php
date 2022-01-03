<?php
    namespace home;

    use JasonGrimes\Paginator;
    use models\BaseDao;
    use lmonkey\CatTree as CT;

    class Product extends Home {
        function index($id) {
            $db  = new BaseDao();

            $product = $db->get('product', '*', ['id'=>$id]);

            $pid = $product['cid'];

            // 导航

            $nowpath = "Position: <a href='/'>Home</a>";

            $cats = $db->select('category', '*', ['ORDER'=>['ord'=>'ASC', 'id'=>'DESC']]);

            $category =  CT::getList($cats);

            $callid = $category[$pid];


            $path = ltrim($callid['path'].",".$pid, "0,");

            foreach(explode(',', $path) as $v) {
                $cattmp = $category[$v];

                $nowpath .= " &gt; <a href='/plist/{$v}' title='{$cattmp['catname']}'>{$cattmp['catname']}</a>";
            }


            $nowpath .= " &gt; ".$product['name'];

            $this->assign('nowpath', $nowpath);


            //分类导航
            $catsi = $db->select('category', ['id', 'catname'], ['pid'=>$pid, 'ORDER'=>['ord'=>'ASC', 'id'=>'DESC']]);

            $catlist = array();
            foreach($catsi as $cat) {
                $cat['subcats'] = $db->select('category', ['id', 'catname'], ['pid'=>$cat['id'], 'ORDER'=>['ord'=>'ASC', 'id'=>'DESC']]);
                array_push($catlist, $cat);
            }

            $this->assign('catlist', $catlist);


            //热销排行， 所有子分类
            $childs = trim($callid['childs'])=="" ? $pid : $callid['childs'].",".$pid;

            if($childs =='0') {
                $selllists = $db->select('product',
                    ['id', 'name', 'logo', 'money', 'smoney'],
                    ['state'=>1, 'ORDER'=>['sellnum'=>'DESC'], 'LIMIT'=>6]);
            }else{
                $selllists = $db->select('product',
                    ['id', 'name', 'logo', 'money', 'smoney'],
                    ['cid'=>explode(',', $childs), 'state'=>1, 'ORDER'=>['sellnum'=>'DESC'], 'LIMIT'=>6]);
            }

            $this->assign('selllists', $selllists);


            // 咨询记录 ------------------------
            $ask_list = $db->select('ask', ['id','uname', 'atime', 'asktext', 'replytext', 'replytime'], ['pid'=>$id, "ORDER"=>['atime'=>'DESC']]);
            $this->assign('ask_list', $ask_list);

            // 评价记录 -------------------
            $comment_list = $db->select('comment', ['id','uname', 'atime', 'content'], ['pid'=>$id, "ORDER"=>['atime'=>'DESC']]);
            $this->assign('comment_list', $comment_list);
            // 售后服务 ---------------------
            $pagetext = $db->get('page', ['content(pagetext)'], ['id'=>13]);
            $this->assign($pagetext);
            // 首页底部广告

            $this->assign($product);
            $this->assign('title', $product['name']);
            $this->display('product/index');

            $db->update('product', ['clicknum[+]'=>1], ['id'=>$id]);
        }

        /**
         * @param int $pid 当前查找的分类ID
         */
        function plist($pid=0) {
            $db = new BaseDao();

            // 导航

            $nowpath = "Position: <a href='/'>Home</a>";

            $cats = $db->select('category', '*', ['ORDER'=>['ord'=>'ASC', 'id'=>'DESC']]);

            $category =  CT::getList($cats);

            $callid = $category[$pid];


            $path = ltrim($callid['path'].",".$pid, "0,");

           foreach(explode(',', $path) as $v) {
                $cattmp = $category[$v];

                $nowpath .= " &gt; <a href='/plist/{$v}' title='{$cattmp['catname']}'>{$cattmp['catname']}</a>";
           }


            $this->assign('nowpath', $nowpath);


            //分类导航
            $catsi = $db->select('category', ['id', 'catname'], ['pid'=>$pid, 'ORDER'=>['ord'=>'ASC', 'id'=>'DESC']]);

            $catlist = array();
            foreach($catsi as $cat) {
                $cat['subcats'] = $db->select('category', ['id', 'catname'], ['pid'=>$cat['id'], 'ORDER'=>['ord'=>'ASC', 'id'=>'DESC']]);
                array_push($catlist, $cat);
            }

           $this->assign('catlist', $catlist);


            //热销排行， 所有子分类
            $childs = trim($callid['childs'])=="" ? $pid : $callid['childs'].",".$pid;

            if($childs =='0') {
                $selllists = $db->select('product',
                    ['id', 'name', 'logo', 'money', 'smoney'],
                    ['state'=>1, 'ORDER'=>['sellnum'=>'DESC'], 'LIMIT'=>6]);
            }else{
                $selllists = $db->select('product',
                    ['id', 'name', 'logo', 'money', 'smoney'],
                    ['cid'=>explode(',', $childs), 'state'=>1, 'ORDER'=>['sellnum'=>'DESC'], 'LIMIT'=>6]);
            }

            $this->assign('selllists', $selllists);


            // 分类下的数据
            $num = $_GET['num'] ?? 1;


            if($pid != 0 ) {
                $where['cid'] = explode(',', $childs);
            }


            if(!empty($_GET['keyword'])) {
                $where['name[~]'] = $_GET['keyword'];
                $name='&keyword='.$_GET['keyword'];
                $this->assign('name', $name);
            }




            if(!empty($_GET['orderby']) || $_GET['orderby'] !='' ) {
                list($filed, $value) = explode('_', $_GET['orderby']);
                /*
                 *   $filed = clicknum
                 *   $value = desc
                 */
                $prosql['ORDER'][$filed] = [$value];
                $orderby='&orderby='.$_GET['orderby'];
            }else {
                $prosql['ORDER'] = ['cid'=>'ASC',"id" => "DESC"];
            }


            $this->assign('get', $_GET);


            $totalItems = $db->count('product', $where);
            $itemsPerPage = 12;
            $currentPage = $num;
            $urlPattern = '/plist/'.$pid.'?num=(:num)'.$name.$orderby;

            $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);


            $start = ($currentPage-1) * $itemsPerPage;
            $prosql['LIMIT'] = [$start, $itemsPerPage];

            $prosql = array_merge($prosql, $where);
            //获取全部的商品, 并能按ord排序
            $data = $db->select('product', ['id', 'name', 'logo', 'money', 'smoney'],  $prosql);
          //  $data = $db->debug()->select('product', '*',  $prosql);


            $this->assign('pid', $pid);
            $this->assign('order', $_GET);
            // 将数据分给模版
            $this->assign('data', $data);
            $this->assign('fpage', $paginator);


            $this->assign('title', $callid['catname']);
            $this->display('product/plist');
        }

        //添加收藏
        function collectadd() {
            $collect['uid'] = $_SESSION['id'];
            $collect['pid'] = $_GET['pid'];

            $db = new BaseDao();
            $show = '';
            if($db->count('collect', ['uid'=>$collect['uid'], 'pid'=>$_GET['pid']])>0){
                $show = 'You have already collected it.';
            }else{
                $collect['atime'] = time();

                if($db->insert('collect', $collect)) {
                    $db->update('product', ['collectnum[+]'=>1], ['id'=>$_GET['pid']]);
                    $show = 'Collected Successfully!';
                }else{
                    $show = 'Collected Unsuccessfully!';
                }
            }

           echo json_encode(['show'=>$show]);

            exit;
        }

        // 添加咨询
        function askadd() {
            $db = new BaseDao();

            if(isset($_POST['do_submit'])) {
                $_POST['atime'] = time();
                $_POST['uid'] = $_SESSION['id'];
                $_POST['uname'] = $_SESSION['name'];
                $_POST['uip'] = getClientIP();

                unset($_POST['do_submit']);

                if($db->insert('ask', $_POST)) {
                    $db->update('product', ['asknum[+]'=>1], ['id'=>$_POST['pid']]);

                    $atime = date('Y-m-d H:i',$_POST['atime']);

                    $asktext = htmlspecialchars($_POST['asktext']);

$html = <<<html

  <ul class="mat5">
        <li class="fl">Name: {$_POST['uname']}</li>
        <li class="fr">Date: {$atime}</li>
  </ul>
    <div class="padb10 mal10 lh18">
        <div class="mat10 font14">{$asktext}</div>
    </div>
html;



                    $result = true;
                }else{
                    $result = false;
                }

                echo json_encode(['result'=>$result, 'html'=>$html]);
            }
            exit;

        }

        // 添加评价
        function commentadd() {
            $db = new BaseDao();

            if(isset($_POST['do_submit'])) {
                $_POST['atime'] = time();
                $_POST['uid'] = $_SESSION['id'];
                $_POST['uname'] = $_SESSION['name'];
                $_POST['uip'] = getClientIP();

                unset($_POST['do_submit']);

                if($db->insert('comment', $_POST)) {
                    $db->update('product', ['commentnum[+]'=>1], ['id'=>$_POST['pid']]);

                    $atime = date('Y-m-d H:i',$_POST['atime']);

                    $commenttext = htmlspecialchars($_POST['content']);
$html = <<<html
<ul>
    <li class="fl">Name: {$_POST['uname']}</li>
    <li class="fr">Date: {$atime}</li>
</ul>
<div class="pingjia font14">{$commenttext}</div>
html;


                    $result = true;
                }else{
                    $result = false;
                }

                echo json_encode(['result'=>$result, 'html'=>$html]);
            }
            exit;

        }
    }
