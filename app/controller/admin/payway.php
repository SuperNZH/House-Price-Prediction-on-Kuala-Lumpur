<?php
    namespace admin;

    use models\BaseDao;

    class Payway extends Admin {
        public function __construct()
        {
            parent::__construct();
            $this->assign('menumark', 'payway');
        }

        /**
         * 支付方式列表页面
         */
        function index() {
            //获取数据库操作对象
            $db = new BaseDao();

            //获取全部的支付方式, 并能按ord排序
            $data = $db->select('payway', '*', ['ORDER'=>['ord'=>'ASC', "id"=>"ASC"]]);

            // 将数据分给模版
            $this->assign('data', $data);


            // 标题
            $this->assign('title', 'Payway');

            //导入模版
            $this->display('payway/index');
        }



        function mod($id) {
            $db = new BaseDao();

            $this->assign($db->get('payway', '*', ['id'=>$id]));

            $this->assign("qt", ['1'=>'On', '0'=>'Off']);


            $this->assign('title', 'Change Payway');
            $this->display('payway/mod');
        }

        function doupdate() {
            $id = $_POST['id'];
            unset($_POST['id']);

            $db = new BaseDao();

            if($db->update('payway', $_POST, ['id'=>$id])) {
                $this->success('/admin/payway', 'Edit Successfully!');
            }else{
                $this->error('/admin/payway/mod/'.$id, 'Edit Unsuccessfully!');
            }
        }



        function order() {


            $db = new BaseDao();

            $num = 0;
            foreach($_POST['ord'] as $id=>$ord) {
                $num += $db->update('payway', ['ord'=>$ord], ['id'=>$id]);
            }

            if($num>0) {
                $this->success('/admin/payway', 'Reorder Successfully!');
            }else {
                $this->error('/admin/payway', 'Reorder Unsuccessfully!');
            }

        }
    }
