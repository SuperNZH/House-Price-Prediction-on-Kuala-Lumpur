<?php
    namespace admin;

    use models\BaseDao;

    class AdminUser extends Admin{
        public function __construct()
        {
            parent::__construct();// 保证创建子类的时候，父类会被执行
            $this->assign('menumark', 'adminuser');
        }



        /*管理员列表页面*/
        function index(){
            // 获取数据库操作对象
            $db = new BaseDao();

            // 获取全部的管理员, 并能按ord排序
            $data = $db->select('admin', '*',['ORDER'=>"id"]);

            // 将数据分给模板
            $this->assign('data', $data);


            // 分配标题
            $this->assign('title', 'MGT list');

            // 导入模板
            $this->display('adminuser/index');
        }

        function add(){
            // 如果$__POST['name']存在且不为空，说明是添加的动作
            if(!empty($_POST['name'])){
                $db = new BaseDao();

                $_POST['atime'] =  $_POST['ltime'] = time();// 因为新创建所以添加管理员的时间和最后访问的时间是相同的
                $_POST['pw'] = md5(md5('e_'.$_POST['pw']));// 密码首先有一个e_的前缀，在第一次md5加密的基础上再进行以此加密

                if($db->insert('admin', $_POST)){
                    $this->success('/admin/adminuser', 'Add Successfully!');
                }else{
                    $this->error('/admin/adminuser/add', "Add Unsuccessfully!");
                }
            }

            $this->assign('title', 'Add Friendship admins');
            $this->display('adminuser/add');
        }

        function mod($id){// 修改哪个管理员，就会把它的相应的id传过来
            $db = new BaseDao();

            $this->assign($db->get('admin', '*',['id'=>$id]));

            $this->assign('title', 'Edit The Administrator');
            $this->display('adminuser/mod');
        }

        function doupdate() {
            $id = $_POST['id'];
            unset($_POST['id']);

            if(!empty($_POST['pw'])){
                $_POST['pw'] = md5(md5('e_'.$_POST['pw']));
            }else{
                unset($_POST['pw']);
            }

            $db = new BaseDao();

            if($db->update('admin', $_POST, ['id'=>$id])) {
                $this->success('/admin/adminuser', 'Edit Successfully!');
            }else{
                $this->error('/admin/adminuser/mod/'.$id, 'Edit Unsuccessfully!');
            }
        }


        function del($id){
            $db = new BaseDao();

            if($id==9){// admin的编号是9，但是一般来说刚建表后，第一个是1的做成admin
                $this->error('/admin/adminuser', 'Sorry! You can not delete Super Admin!');
                exit;
            }

            if($db->delete('admin', ['id'=>$id])){
                $this->success('/admin/adminuser', 'Delete Successfully!');
            }else{
                $this->error('/admin/adminuser', 'Delete Unsuccessfully!');
            }
        }

    }