<?php
namespace admin;

use models\BaseDao;
use JasonGrimes\Paginator;
use Slince\Upload\UploadHandlerBuilder;

class User extends Admin {
    public function __construct()
    {
        parent::__construct();
        $this->assign('menumark', 'user');


    }

    /**
     * 用户列表页面
     */
    function index() {
        //获取数据库操作对象
        $db = new BaseDao();

        $num = $_GET['num'] ?? 1;

        $prosql['ORDER'] = ["id"=>"DESC"];
        $where = [];

        // 排序
        if(!empty($_GET['orderby'])) {
            $prosql['ORDER'] = [$_GET['orderby']=>'DESC'];
            $orderby = '&orderby='.$_GET['orderby'];
        }else{
            $prosql['ORDER'] = ["id"=>"DESC"];
        }


        if(!empty($_GET['name']) ) {
            $where['name[~]'] = $_GET['name'];
            $name='&name='.$_GET['name'];
        }

        if(!empty($_GET['phone']) ) {
            $where['phone[~]'] = $_GET['phone'];
            $phone='&phone='.$_GET['phone'];
        }

        if(!empty($_GET['email']) ) {
            $where['email[~]'] = $_GET['email'];
            $email='&email='.$_GET['email'];
        }



        $this->assign('get', $_GET);


        $totalItems = $db->count('user', $where);
        $itemsPerPage = PNUM;
        $currentPage = $num;
        $urlPattern = '/admin/user?num=(:num)'.$orderby.$name.$phone.$email;

        $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);


        $start = ($currentPage-1) * $itemsPerPage;
        $prosql['LIMIT'] = [$start, $itemsPerPage];

        $prosql = array_merge($prosql, $where);
        //获取全部的用户, 并能按ord排序
        $data = $db->select('user', '*',  $prosql);



        // 将数据分给模版
        $this->assign('data', $data);
        $this->assign('fpage', $paginator);

        // 标题
        $this->assign('title', 'Customer List');

        //导入模版
        $this->display('user/index');
    }




    function mod($id) {
        $db = new BaseDao();

        $this->assign($db->get('user', '*', ['id'=>$id]));

        $this->assign('title', 'Modify Customer');
        $this->display('user/mod');
    }

    function doupdate() {
        $id = $_POST['id'];
        unset($_POST['id']);



        $db = new BaseDao();

        if(!empty($_POST['pw'])) {
            $_POST['pw'] = md5(md5('ew_'.$_POST['pw']));
        }else{
            unset($_POST['pw']);
        }

        if($db->update('user', $_POST, ['id'=>$id])) {
            $this->success('/admin/user', 'Modify Successfully!');
        }else{
            $this->error('/admin/user/mod/'.$id, 'Fail to modify!');
        }
    }


    function del($id) {
        $db = new BaseDao();

        if($db->delete('user', ['id'=>$id])) {
            $this->success('/admin/user', 'Delete Successfully!');
        }else{
            $this->error('/admin/user', 'Delete Unsuccessfully!');
        }
    }

    function alldel() {


        $db = new BaseDao();

        $num = 0;
        foreach($_POST['id'] as $id) {
            $num += $db->delete('user', ['id'=>$id]);
        }

        if($num>0) {
            $this->success('/admin/user', $num.' users account Deleted!');
        }else {
            $this->error('/admin/user', 'Deleted Failed!');
        }

    }
}
