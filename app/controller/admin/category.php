<?php
namespace admin;

use models\BaseDao;

use lmonkey\CatTree as CT;

class category extends Admin {
    public function __construct()
    {
        parent::__construct();
        $this->assign('menumark', 'category');

        $db = new BaseDao();
        //获取全部的商品分类, 并能按ord排序
        $data = $db->select('category', ['id', 'catname', 'pid', 'ord']);

        $tree = CT::getlist($data);

        // 将数据分给模版
        $this->assign('cats', $tree);

    }

    /**
     * 商品分类列表页面
     */
    function index() {

        // 标题
        $this->assign('title', 'Goods Categories');

        //导入模版
        $this->display('category/index');
    }

    function add() {
        //如果$_POST['do_submit']存在，说明是添加的动作
        if(isset($_POST['do_submit'])) {
            $db = new BaseDao();

            unset($_POST['do_submit']);

            if($db -> insert('category', $_POST)) {
                $this->success('/admin/category', 'Add Successfully!');
            }else{
                $this->error('/admin/category/add', 'Add Unsuccessfully!');
            }
        }

        $this->assign('title', 'Add Category');
        $this->display('category/add');
    }

    function mod($id) {
        $db = new BaseDao();

        $this->assign('childs', $_GET['childs']);
        $this->assign($db->get('category', '*', ['id'=>$id]));

        $this->assign('title', 'Edit the Goods Category');
        $this->display('category/mod');
    }

    function doupdate() {
        $id = $_POST['id'];
        unset($_POST['id']);

        $_POST['childs'] .= ",".$id;// 获得所有的子分类

        //如果在自己之下，就不能接着往下循环了
        if(in_array($_POST['pid'], explode(",", $_POST['childs']))) {
            $this->error('/admin/category', "You cannot modify a category to itself or its subclasses...");
            exit;
        }


        $db = new BaseDao();

        unset($_POST['childs']);



        if($db->update('category', $_POST, ['id'=>$id])) {
            $this->success('/admin/category', 'Edit Successfully!');
        }else{
            $this->error('/admin/category/mod/'.$id, 'Edit Unsuccessfully!');
        }
    }


    function del($id) {


        //如果子类存在，则不能直接删除父类
        if($_GET['childs'] != '') {
            $this->error('/admin/category', "Cannot delete non-empty categories..");
            exit;
        }

        $db = new BaseDao();

        if($db->delete('category', ['id'=>$id])) {
            $this->success('/admin/category', 'Delete Successfully!');
        }else{
            $this->error('/admin/category', 'Delete Unsuccessfully!');
        }
    }

    function order() {


        $db = new BaseDao();

        $num = 0;
        foreach($_POST['ord'] as $id=>$ord) {
            $num += $db->update('category', ['ord'=>$ord], ['id'=>$id]);
        }

        if($num>0) {
            $this->success('/admin/category', 'Reorder Successfully!');
        }else {
            $this->error('/admin/category', 'Reorder Unsuccessfully!');
        }

    }
}
