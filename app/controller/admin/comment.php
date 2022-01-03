<?php
    namespace admin;

    use models\BaseDao;
    use JasonGrimes\Paginator;
    use Slince\Upload\UploadHandlerBuilder;

    class Comment extends Admin {
        public function __construct()
        {
            parent::__construct();
            $this->assign('menumark', 'comment');


        }

        /**
         * 评价列表页面
         */
        function index() {
            //获取数据库操作对象
            $db = new BaseDao();

            $num = $_GET['num'] ?? 1;

            $prosql['ORDER'] = ["comment.atime"=>"DESC"];
            $where = [];





            if(!empty($_GET['name']) ) {
                $where['product.name[~]'] = $_GET['name'];
                $name='&name='.$_GET['name'];
            }

            if(!empty($_GET['content']) ) {
                $where['comment.content[~]'] = $_GET['content'];
                $content='&content='.$_GET['content'];
            }

            if(!empty($_GET['uname']) ) {
                $where['comment.uname[~]'] = $_GET['uname'];
                $uname='&uname='.$_GET['uname'];
            }



            $this->assign('get', $_GET);


            $totalItems = $db->count('comment', ['[>]product'=>['pid'=>'id']],'*',$where);
            $itemsPerPage = 3;
            $currentPage = $num;
            $urlPattern = '/admin/comment?num=(:num)'.$name.$content.$uname;

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

            // 标题
            $this->assign('title', 'Comments List');

            //导入模版
            $this->display('comment/index');
        }




        function mod($id) {
            $db = new BaseDao();

            $data = $db->get('comment',
                ['[>]product'=>['pid'=>'id']],
                ['comment.id','comment.pid','comment.uid','comment.uip','comment.atime','comment.uname','comment.content','product.name','product.logo'],
                ['comment.id'=>$id]

            );

            $this->assign($data);

            $this->assign('title', 'Reply Comment');
            $this->display('comment/mod');
        }

        function doupdate() {
            $id = $_POST['id'];
            unset($_POST['id']);



            $db = new BaseDao();


            if($db->update('comment', $_POST, ['id'=>$id])) {
                $this->success('/admin/comment', 'Edit Successfully!');
            }else{
                $this->error('/admin/comment/mod/'.$id, 'Edit Unsuccessfully!');
            }
        }


        function del($id) {
            $db = new BaseDao();

            if($db->delete('comment', ['id'=>$id])) {
                $this->success('/admin/comment', 'Delete Successfully!');
            }else{
                $this->error('/admin/comment', 'Edit Unsuccessfully!');
            }
        }

        function alldel() {


            $db = new BaseDao();

            $num = 0;
            foreach($_POST['id'] as $id) {
                $num += $db->delete('comment', ['id'=>$id]);
            }

            if($num>0) {
                $this->success('/admin/comment', $num.' comments has been deleted');
            }else {
                $this->error('/admin/comment', 'Fail to delete...');
            }

        }
    }
