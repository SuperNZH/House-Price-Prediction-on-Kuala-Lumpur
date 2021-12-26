<?php
    namespace home;

    use models\BaseDao;

    class Page extends Home {
        function index($id) {
            $db = new BaseDao();


            $page = $db->get('page', '*', ['id'=>$id]);


            $this->assign('nowpath', " &gt; Help Center &gt; ".$page['name']);

            $this->assign($page);

            $this->assign('title', $page['name']);

            $this->display('page/index');
        }
    }
