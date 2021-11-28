<?php
    namespace home;

    use models\BaseDao;

    class Page extends Home {
        function index($id) {
            $db = new BaseDao();


            $page = $db->get('page', '*', ['id'=>$id]);


            $this->assign('nowpath', " &gt; 帮助中心 &gt; ".$page['name']);

            $this->assign($page);

            $this->assign('title', $page['name']);

            $this->display('page/index');
        }
    }
