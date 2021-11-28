<?php
    namespace admin;

    use models\BaseDao;

    class Link extends Admin{
        public function __construct()
        {
            parent::__construct();// 保证创建子类的时候，父类会被执行
            $this->assign('menumark', 'link');
        }



        /*友情链接列表页面*/
        function index(){
            // 获取数据库操作对象
            $db = new BaseDao();

            // 获取全部的友情链接, 并能按ord排序
            $data = $db->select('link', '*',['ORDER'=>['ord'=>'ASC', "id"=>"DESC"]]);

            // 将数据分给模板
            $this->assign('data', $data);


            // 分配标题
            $this->assign('title', 'Friendship Links');

            // 导入模板
            $this->display('link/index');
        }

        function add(){
            // 如果$__POST['do_submit']存在，说明是添加的动作
            if(isset($_POST['do_submit'])){
                $db = new BaseDao();

                unset($_POST['do_submit']);

                if($db->insert('link', $_POST)){
                    $this->success('/admin/link', 'Add Successfully!');
                }else{
                    $this->error('/admin/link/add', "Add Unsuccessfully!");
                }
            }

            $this->assign('title', 'Add Friendship links');
            $this->display('Link/add');
        }

        function mod($id){// 修改哪个友情链接，就会把它的相应的id传过来
            $db = new BaseDao();

            $this->assign("link", $db->get('link', '*',['id'=>$id]));

            $this->assign('title', 'Edit The Friendship Link');
            $this->display('link/mod');
        }

        function doupdate() {
            $id = $_POST['id'];
            unset($_POST['id']);

            $db = new BaseDao();

            if($db->update('link', $_POST, ['id'=>$id])) {
                $this->success('/admin/link', 'Edit Successfully!');
            }else{
                $this->error('/admin/link/mod/'.$id, 'Edit Unsuccessfully!');
            }
        }


        function del($id){
            $db = new BaseDao();

            if($db->delete('link', ['id'=>$id])){
                $this->success('/admin/link', 'Delete Successfully!');
            }else{
                $this->error('/admin/link', 'Delete Unsuccessfully!');
            }
        }

        function order(){
            $db = new BaseDao();

            $num = 0;
            foreach($_POST['ord'] as $id=>$ord){
                $num += $db->update('link', ['ord'=>$ord], ['id'=>$id]);
            }

            if($num>0) {
                $this->success('/admin/link', 'Reorder Successfully!');
            }else {
                $this->error('/admin/link', 'Reorder Unsuccessfully!');
            }

        }
    }