<?php
    namespace home;

    use models\BaseDao;

    class Index extends Home {
        function index() {
            $db  = new BaseDao();

            // 特价商品
            $this->assign('tjs', $db->select('product', '*', ['istj'=>1, 'state'=>1, 'ORDER'=>['id'=>'DESC'], 'LIMIT'=>3]));

            // 系统公告
            $this->assign('notices', $db->select('article', ['id', 'name'], ['cid'=>1, 'ORDER'=>['id'=>'DESC'], 'LIMIT'=>8]));


            // 新品推荐
            $this->assign('tuijians', $db->select('product', ['id', 'name', 'logo', 'money', 'smoney'], ['istuijian'=>1, 'state'=>1, 'ORDER'=>['id'=>'DESC'], 'LIMIT'=>5]));



            // 分类和部数据数据
            $cats = $db->select('category', ['id', 'catname'], ['pid'=>0, 'ORDER'=>['ord'=>'ASC', 'id'=>'DESC']]);


            $allcats = array();
            foreach($cats as $cat){

                $cat['newlists'] = $db->select('product',
                    ['id', 'name', 'logo', 'money', 'smoney'],
                    ['cid'=>$cat['id'], 'state'=>1, 'ORDER'=>['id'=>'DESC'], 'LIMIT'=>8]);

                $cat['selllists'] = $db->select('product',
                    ['id', 'name', 'logo', 'money', 'smoney'],
                    ['cid'=>$cat['id'], 'state'=>1, 'ORDER'=>['sellnum'=>'DESC'], 'LIMIT'=>5]);

                array_push($allcats, $cat);
            }

           $this->assign('allcats', $allcats);


//            // 首页底部广告


            $this->assign('title', 'EShop Home Page');
            $this->display('index/index');
        }
    }
