<?php
    namespace home;

    use JasonGrimes\Paginator;
    use models\BaseDao;

    class Article extends Home {
        function index($id) {
            $db = new BaseDao();

            $article = $db->get('article', '*', ['id'=>$id]);

            $class = $db -> select('class', '*', ['ORDER'=>['ord'=>'ASC', 'id'=>'ASC']]);
            $this->assign('class', $class);


            $classes = array();

            foreach($class as $v ){
                $classes[$v['id']] = $v['catname'];
            }

            $this->assign('nowpath', " &gt; News &gt; <a href='/alist/{$article['cid']}'>{$classes[$article['cid']]}</a> &gt; ".$article['name']);


            $this->assign('cname', $classes[$article['cid']]);

            $this->assign($article);

            //获取相关文章， 即同分类下的文章
            $data = $db->select('article', ['id', 'name', 'atime', 'clicknum'], ['cid'=>$article['cid'], 'LIMIT'=>6]);

            $this->assign('data', $data);

            $this->assign('title', $article['name']);

            $this->display('article/index');

            $db->update('article', ['clicknum[+]'=>1], ['id'=>$id]);
        }

        function alist($cid=0) {
            $db = new BaseDao();

            $class = $db -> select('class', '*', ['ORDER'=>['ord'=>'ASC', 'id'=>'ASC']]);
            $this->assign('class', $class);


            $classes = array();

            foreach($class as $v ){
                $classes[$v['id']] = $v['catname'];
            }

            $this->assign('nowpath', " &gt; News Center &gt; <a href='/alist/{$cid}'>{$classes[$cid]}</a>");
            $this->assign('cname', $classes[$cid]);



            // 分类下的数据
            $num = $_GET['num'] ?? 1;


            if($cid != 0 ) {
                $where['cid'] = $cid;
            }


            $totalItems = $db->count('article', $where);
            $itemsPerPage = 10;
            $currentPage = $num;
            $urlPattern = '/alist/'.$cid.'?num=(:num)';

            $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);


            $start = ($currentPage-1) * $itemsPerPage;
            $where['LIMIT'] = [$start, $itemsPerPage];


            //获取全部的商品, 并能按ord排序
            $data = $db->select('article', ['id', 'name', 'atime', 'clicknum'],  $where);
            //  $data = $db->debug()->select('article', '*',  $where);

            // 将数据分给模版
            $this->assign('data', $data);
            $this->assign('fpage', $paginator);




            $this->assign('title', 'News Center');
            $this->display('article/alist');
        }
    }
