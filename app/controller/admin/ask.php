<?php
    namespace admin;

    use models\BaseDao;
    use JasonGrimes\Paginator;
    use Slince\Upload\UploadHandlerBuilder;

    class Ask extends Admin {
        public function __construct()
        {
            parent::__construct();
            $this->assign('menumark', 'ask');


        }

        /**
         * 咨询列表页面
         */
        function index() {
            //获取数据库操作对象
            $db = new BaseDao();

            $num = $_GET['num'] ?? 1;

            $prosql['ORDER'] = ["id"=>"DESC"];
            $where = [];


            if(isset($_GET['state']) && $_GET['state'] !='') {
                $where['ask.state'] = $_GET['state'];
                $state='&state='.$_GET['state'];
            }



            if(!empty($_GET['name']) ) {
                $where['product.name[~]'] = $_GET['name'];
                $name='&name='.$_GET['name'];
            }

            if(!empty($_GET['asktext']) ) {
                $where['ask.asktext[~]'] = $_GET['asktext'];
                $asktext='&asktext='.$_GET['asktext'];
            }

            if(!empty($_GET['uname']) ) {
                $where['ask.uname[~]'] = $_GET['uname'];
                $uname='&uname='.$_GET['uname'];
            }



            $this->assign('get', $_GET);


            $totalItems = $db->count('ask',['[>]product'=>['pid'=>'id']],'*',$where);
            $itemsPerPage = 3; # $PNUM
            $currentPage = $num;
            $urlPattern = '/admin/ask?num=(:num)'.$state.$name.$asktext.$uname;

            $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);


            $start = ($currentPage-1) * $itemsPerPage;
            $prosql['LIMIT'] = [$start, $itemsPerPage];

             $prosql = array_merge($prosql, $where);
            //获取全部的咨询, 并能按ord排序
            $data = $db->select('ask',
                ['[>]product'=>['pid'=>'id']],
                ['ask.id','ask.pid','ask.uid','ask.uip','ask.atime','ask.uname','ask.replytext','ask.replytime','ask.state','ask.asktext','product.name','product.logo'],
                $prosql
            );



            // 将数据分给模版
            $this->assign('data', $data);
            $this->assign('fpage', $paginator);

            // 标题
            $this->assign('title', 'Consultation Questions');

            //导入模版
            $this->display('ask/index');
        }




        function reply($id) {
            $db = new BaseDao();

            $data = $db->get('ask',
                ['[>]product'=>['pid'=>'id']],
                ['ask.id','ask.pid','ask.uid','ask.uip','ask.atime','ask.uname','ask.replytext','ask.replytime','ask.state','ask.asktext','product.name','product.logo'],
                ['ask.id'=>$id]

            );

            $this->assign($data);

            $this->assign('title', 'Reply the Consult');
            $this->display('ask/reply');
        }

        function doreply() {
            $id = $_POST['id'];
            unset($_POST['id']);



            $db = new BaseDao();

            if(!empty($_POST['replytext'])) {
                $_POST['replytime'] = time();
                $_POST['state'] = 1;
            }else{
                $_POST['replytime'] = $_POST['state'] = 0;
            }

            if($db->update('ask', $_POST, ['id'=>$id])) {
                $this->success('/admin/ask?state=1', 'Replied Successfully!');
            }else{
                $this->error('/admin/ask/reply/'.$id, 'Fail to Reply...');
            }
        }


        function del($id) {
            $db = new BaseDao();

            if($db->delete('ask', ['id'=>$id])) {
                $this->success('/admin/ask', 'Delete Successfully!');
            }else{
                $this->error('/admin/ask', 'Delete Unsuccessfully!');
            }
        }

        function alldel() {


            $db = new BaseDao();

            $num = 0;
            foreach($_POST['id'] as $id) {
                $num += $db->delete('ask', ['id'=>$id]);
            }

            if($num>0) {
                $this->success('/admin/ask', $num.' consults has been deleted!');
            }else {
                $this->error('/admin/ask', 'Fail to delete...');
            }

        }
    }
