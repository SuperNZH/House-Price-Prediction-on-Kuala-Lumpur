<?php
    namespace admin;

    use models\BaseDao;

    class Page extends Admin{
        public function __construct()
        {
            parent::__construct();// 保证创建子类的时候，父类会被执行
            $this->assign('menumark', 'page');
        }



        /*单页列表页面*/
        function index(){
            // 获取数据库操作对象
            $db = new BaseDao();

            // 获取全部的单页, 并能按ord排序
            $data = $db->select('page', ['id','name','ord'],['ORDER'=>['ord'=>'ASC', "id"=>"DESC"]]);

            // 将数据分给模板
            $this->assign('data', $data);


            // 分配标题
            $this->assign('title', 'Sin pages');

            // 导入模板
            $this->display('page/index');
        }

        function add(){
            // 如果$__POST['do_submit']存在，说明是添加的动作
            if(isset($_POST['do_submit'])){
                $db = new BaseDao();

                unset($_POST['do_submit']);

                if($db->insert('page', $_POST)){
                    $this->success('/admin/page', 'Add Successfully!');
                }else{
                    $this->error('/admin/page/add', "Add Unsuccessfully!");
                }
            }

            $this->assign('title', 'Add Friendship pages');
            $this->display('page/add');
        }

        function mod($id){// 修改哪个单页，就会把它的相应的id传过来
            $db = new BaseDao();

            $this->assign($db->get('page', '*',['id'=>$id]));

            $this->assign('title', 'Edit the Sin-page');
            $this->display('page/mod');
        }

        function doupdate() {
            $id = $_POST['id'];
            unset($_POST['id']);

            $db = new BaseDao();

            if($db->update('page', $_POST, ['id'=>$id])) {
                $this->success('/admin/page', 'Edit Successfully!');
            }else{
                $this->error('/admin/page/mod/'.$id, 'Edit Unsuccessfully!');
            }
        }


        function del($id){
            $db = new BaseDao();

            if($db->delete('page', ['id'=>$id])){
                $this->success('/admin/page', 'Delete Successfully!');
            }else{
                $this->error('/admin/page', 'Delete Unsuccessfully!');
            }
        }

        function order(){
            $db = new BaseDao();

            $num = 0;
            foreach($_POST['ord'] as $id=>$ord){
                $num += $db->update('page', ['ord'=>$ord], ['id'=>$id]);
            }

            if($num>0) {
                $this->success('/admin/page', 'Reorder Successfully!');
            }else {
                $this->error('/admin/page', 'Reorder Unsuccessfully!');
            }

        }
    }