<?php
    namespace admin;

    use models\BaseDao;

    class Aclass extends Admin{
        public function __construct()
        {
            parent::__construct();// 保证创建子类的时候，父类会被执行
            $this->assign('menumark', 'aclass');
        }



        /*文章分类列表页面*/
        function index(){
            // 获取数据库操作对象
            $db = new BaseDao();

            // 获取全部的文章分类, 并能按ord排序
            $data = $db->select('class', '*',['ORDER'=>['ord'=>'ASC', "id"=>"DESC"]]);

            // 将数据分给模板
            $this->assign('data', $data);


            // 分配标题
            $this->assign('title', 'Article Classification');

            // 导入模板
            $this->display('aclass/index');
        }

        function add(){
            // 如果$__POST['do_submit']存在，说明是添加的动作
            if(isset($_POST['do_submit'])){
                $db = new BaseDao();

                unset($_POST['do_submit']);

                if($db->insert('class', $_POST)){
                    $this->success('/admin/aclass', 'Add Successfully!');
                }else{
                    $this->error('/admin/aclass/add', "Add Unsuccessfully!");
                }
            }

            $this->assign('title', 'Add Article Classification');
            $this->display('aclass/add');
        }

        function mod($id){// 修改哪个文章分类，就会把它的相应的id传过来
            $db = new BaseDao();

            $this->assign($db->get('class', '*',['id'=>$id]));

            $this->assign('title', 'Edit The Article Classification');
            $this->display('aclass/mod');
        }

        function doupdate() {
            $id = $_POST['id'];
            unset($_POST['id']);

            $db = new BaseDao();

            if($db->update('class', $_POST, ['id'=>$id])) {
                $this->success('/admin/aclass', 'Edit Successfully!');
            }else{
                $this->error('/admin/aclass/mod/'.$id, 'Edit Unsuccessfully!');
            }
        }


        function del($id){
            $db = new BaseDao();

            if($db->delete('class', ['id'=>$id])){
                $this->success('/admin/aclass', 'Delete Successfully!');
            }else{
                $this->error('/admin/aclass', 'Delete Unsuccessfully!');
            }
        }

        function order(){
            $db = new BaseDao();

            $num = 0;
            foreach($_POST['ord'] as $id=>$ord){
                $num += $db->update('class', ['ord'=>$ord], ['id'=>$id]);
            }

            if($num>0) {
                $this->success('/admin/aclass', 'Reorder Successfully!');
            }else {
                $this->error('/admin/aclass', 'Reorder Unsuccessfully!');
            }

        }
    }