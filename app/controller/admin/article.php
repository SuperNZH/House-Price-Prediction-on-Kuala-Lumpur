<?php
    namespace admin;

    use models\BaseDao;
    use JasonGrimes\Paginator;
    use Slince\Upload\UploadHandlerBuilder;
    use const http\Client\Curl\POSTREDIR_301;

    class Article extends Admin{
        public function __construct()
        {
            parent::__construct();// 保证创建子类的时候，父类会被执行
            $this->assign('menumark', 'article');

            $db = new BaseDao();
            $data = $db -> select('class', '*', ['ORDER'=>["ord"=>"ASC", "id"=>"DESC"]]);
            $this->assign('aclass', $data);
        }



        /*文章列表页面*/
        function index(){
            // 获取数据库操作对象
            $db = new BaseDao();

            $num = $_GET['num'] ?? 1;

            $prosql['ORDER'] = ["id"=>"DESC"];
            $where = [];


            if(!empty($_GET['cid']) || $_GET['cid'] != 0) {
                //按分类
                $where['cid'] = $_GET['cid'];
                $cid='&cid='.$_GET['cid'];// 翻页后，还要接着保持同一个分类
            }

            if(!empty($_GET['name']) ) {
                $where['name[~]'] = $_GET['name'];// like的模糊查询
                $name='&name='.$_GET['name'];// 翻页后，还要接着保持同一个关键词
            }

            $this->assign('get', $_GET);// 保持搜索限制输入的字符串持续存在


            $totalItems = $db->count('article', $where);
            $itemsPerPage = PNUM;
            $currentPage = $num;
            $urlPattern = '/admin/article?num=(:num)'.$cid.$name;

            $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);


            $start = ($currentPage-1) * $itemsPerPage;
            $prosql['LIMIT'] = [$start, $itemsPerPage];

            $prosql = array_merge($prosql, $where);
            //获取全部的文章, 并能按ord排序
            $data = $db->select('article', ['id', 'name', 'atime', 'clicknum'],  $prosql);




            // 将数据分给模板
            $this->assign('data', $data);
            $this->assign('fpage', $paginator);

            // 分配标题
            $this->assign('title', 'Article List');

            // 导入模板
            $this->display('article/index');
        }

        function add(){
            // 如果$__POST['do_submit']存在，说明是添加的动作
            if(isset($_POST['do_submit'])){
                $db = new BaseDao();

                $_POST['atime'] = empty($_POST['atime']) ? time() : strtotime($_POST['atime']);

                unset($_POST['do_submit']);

                if($db->insert('article', $_POST)){
                    $this->success('/admin/article', 'Add Successfully!');
                }else{
                    $this->error('/admin/article/add', "Add Unsuccessfully!");
                }
            }

            $this->assign('title', 'Post Article');
            $this->display('article/add');
        }


        function upload(){
            $path = TEMPDIR."/uploads/article";// 后端找的路径

            $builder = new UploadHandlerBuilder(); //create a builder.
            $handler = $builder

                //add constraints

                ->allowExtensions(['jpg', 'png', 'gif'])
                ->allowMimeTypes(['image/*'])

                ->saveTo($path) //save to local
                ->getHandler();


            $files = $handler->handle();
            $filename = $files['file']->getUploadedFile()->getClientOriginalName();

            $newfilename =  date('Y-md') . '-' . uniqid() . '.' . $files['file']->getUploadedFile()->getClientOriginalExtension();

            rename($path.'/'.$filename, $path.'/'.$newfilename);

            $url = getCurUrl();


            $arr['src'] = $url.'/uploads/article/'. $newfilename;// 前端找的路径
            echo json_encode($arr);






        }

        function mod($id){// 修改哪个文章，就会把它的相应的id传过来
            $db = new BaseDao();

            $this->assign($db->get('article', '*',['id'=>$id]));

            $this->assign('title', 'Edit The Article');
            $this->display('article/mod');
        }

        function doupdate() {
            $id = $_POST['id'];
            unset($_POST['id']);

            $_POST['atime'] = empty($_POST['atime']) ? time() : strtotime($_POST['atime']);

            $db = new BaseDao();

            if($db->update('article', $_POST, ['id'=>$id])) {
                $this->success('/admin/article', 'Edit Successfully!');
            }else{
                $this->error('/admin/article/mod/'.$id, 'Edit Unsuccessfully!');
            }
        }


        function del($id){
            $db = new BaseDao();

            if($db->delete('article', ['id'=>$id])){
                $this->success('/admin/article', 'Delete Successfully!');
            }else{
                $this->error('/admin/article', 'Delete Unsuccessfully!');
            }
        }

        function alldel(){
            $db = new BaseDao();

            $num = 0;
            foreach($_POST['id'] as $id){
                $num += $db->delete('article', ['id'=>$id]);
            }

            if($num>0) {
                $this->success('/admin/article', $num.' of Selected Articles Deleted Successfully!');
            }else {
                $this->error('/admin/article', 'Delete Selected Unsuccessfully!');
            }

        }
    }