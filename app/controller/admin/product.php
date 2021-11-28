<?php
namespace admin;

use lmonkey\CatTree as CT;
use models\BaseDao;
use JasonGrimes\Paginator;
use Slince\Upload\UploadHandlerBuilder;

class Product extends Admin {
    public function __construct()
    {
        parent::__construct();
        $this->assign('menumark', 'product');

        $db = new BaseDao();
        //获取全部的商品分类, 并能按ord排序
        $data = $db->select('category', ['id', 'catname', 'pid', 'ord']);

        $tree = CT::getlist($data);

        // 将数据分给模版
        $this->assign('cats', $tree);
    }

    /**
     * 商品列表页面
     */
    function index() {
        //获取数据库操作对象
        $db = new BaseDao();

        $num = $_GET['num'] ?? 1;


        $where = [];

        if(isset($_GET['state']) && $_GET['state'] !='') {
            $where['state'] = $_GET['state'];
            $state='&state='.$_GET['state'];
        }


        if(!empty($_GET['name'])) {
            $where['name[~]'] = $_GET['name'];
            $name='&name='.$_GET['name'];
        }


        if(!empty($_GET['cid']) || $_GET['cid'] !=0 ) {
            $where['cid'] = $_GET['cid'];
            $cid='&cid='.$_GET['cid'];
        }

        if(!empty($_GET['filter']) || $_GET['filter'] !='' ) {
            list($filed, $value) = explode('|', $_GET['filter']);
            $where[$filed] = $value;
            $filter='&filter='.$_GET['filter'];
        }


        if(!empty($_GET['orderby']) || $_GET['orderby'] !='' ) {
            list($filed, $value) = explode('|', $_GET['orderby']);
            /*
             *   $filed = clicknum
             *   $value = desc
             */
            $prosql['ORDER'][$filed] = [$value];
            $orderby='&orderby='.$_GET['orderby'];
        }else {
            $prosql['ORDER'] = ["id" => "DESC"];
        }


        $this->assign('get', $_GET);


        $totalItems = $db->count('product', $where);
        $itemsPerPage = PNUM;
        $currentPage = $num;
        $urlPattern = '/admin/product?num=(:num)'.$state.$name.$cid.$filter.$orderby;

        $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);


        $start = ($currentPage-1) * $itemsPerPage;
        $prosql['LIMIT'] = [$start, $itemsPerPage];

        $prosql = array_merge($prosql, $where);
        //获取全部的商品, 并能按ord排序
        $data = $db->select('product', '*',  $prosql);
        //$data = $db->debug()->select('product', '*',  $prosql);



        // 将数据分给模版
        $this->assign('data', $data);
        $this->assign('fpage', $paginator);


        // 过滤的数据
        $filter_arr = ['istuijian|1'=>'Rcmd', 'istj|1'=>'SpclOffer', 'wlmoney|0'=>'freePost', 'num|0'=>'soldOut'];
        $this->assign('filter_arr', $filter_arr);


        $orderby_arr["clicknum|desc"]='view(M-L)';
        $orderby_arr["clicknum|asc"]='view(L-M)';
        $orderby_arr["sellnum|desc"]=' sold(M-L)';
        $orderby_arr["sellnum|asc"]='sold(L-M)';
        $orderby_arr["num|desc"]='ivtry(M-L)';
        $orderby_arr["num|asc"]='ivtry(L-M)';
        $orderby_arr["collectnum|desc"]='like(M-L)';
        $orderby_arr["collectnum|asc"]='like(L-M)';
        $orderby_arr["asknum|desc"]='iqry(M-L)';
        $orderby_arr["asknum|asc"]='iqry(L-M)';
        $orderby_arr["commentnum|desc"]='comt(M-L)';
        $orderby_arr["commentnum|asc"]='comt(L-M)';

        $this->assign('orderby_arr', $orderby_arr);


        // 标题
        $this->assign('title', 'Product List');

        //导入模版
        $this->display('product/index');
    }

    function add() {
        //如果$_POST['do_submit']存在，说明是添加的动作
        if(isset($_POST['do_submit'])) {
            $path = TEMPDIR."/uploads/product";

            $builder = new UploadHandlerBuilder(); //create a builder.
            $handler = $builder

                //add constraints

                ->allowExtensions(['jpg', 'png', 'gif'])
                ->allowMimeTypes(['image/*'])

                ->saveTo($path) //save to local
                ->getHandler();


            $files = $handler->handle();
            $filename = $files['logo']->getUploadedFile()->getClientOriginalName();

            $newfilename =  date('Y-md') . '-' . uniqid() . '.' . $files['logo']->getUploadedFile()->getClientOriginalExtension();

            rename($path.'/'.$filename, $path.'/'.$newfilename);





            $_POST['logo'] = $newfilename;

            $_POST['istj'] = isset($_POST['istj']) ? 1 : 0;



            $db = new BaseDao();

            $_POST['atime'] = empty($_POST['atime']) ? time() : strtotime($_POST['atime']);

            unset($_POST['do_submit']);

            if($db -> insert('product', $_POST)) {
                $this->success('/admin/product', 'Add Successfully!');
            }else{
                $this->error('/admin/product/add', 'Add Unsuccessfully!');
            }
        }

        $this->assign('title', 'Post Goods');
        $this->display('product/add');
    }



    function mod($id) {
        $db = new BaseDao();

        $this->assign($db->get('product', '*', ['id'=>$id]));

        $this->assign('title', '修改商品');
        $this->display('product/mod');
    }

    function doupdate() {
        if($_FILES['logo']['error'] == 0) {
            $path = TEMPDIR."/uploads/product";

            $builder = new UploadHandlerBuilder(); //create a builder.
            $handler = $builder

                //add constraints

                ->allowExtensions(['jpg', 'png', 'gif'])
                ->allowMimeTypes(['image/*'])

                ->saveTo($path) //save to local
                ->getHandler();


            $files = $handler->handle();
            $filename = $files['logo']->getUploadedFile()->getClientOriginalName();

            $newfilename =  date('Y-md') . '-' . uniqid() . '.' . $files['logo']->getUploadedFile()->getClientOriginalExtension();

            rename($path.'/'.$filename, $path.'/'.$newfilename);





            $_POST['logo'] = $newfilename;
        }


        $id = $_POST['id'];
        unset($_POST['id']);

        $_POST['atime'] = empty($_POST['atime']) ? time() : strtotime($_POST['atime']);
        $_POST['istj'] = isset($_POST['istj']) ? 1 : 0;

        $db = new BaseDao();

        if($db->update('product', $_POST, ['id'=>$id])) {
            $this->success('/admin/product', 'Edit Successfully!');
        }else{
            $this->error('/admin/product/mod/'.$id, 'Edit unsuccessfully!');
        }
    }


    function del($id) {
        $db = new BaseDao();

        if($db->delete('product', ['id'=>$id])) {
            $this->success('/admin/product', 'Delete Successfully!');
        }else{
            $this->error('/admin/product', 'Delete unsuccessfully!');
        }
    }

    function alldel() {


        $db = new BaseDao();

        $num = 0;
        foreach($_POST['id'] as $id) {
            $num += $db->delete('product', ['id'=>$id]);
        }

        if($num>0) {
            $this->success('/admin/product', $num.' products are deleted!');
        }else {
            $this->error('/admin/product', 'Delete unsuccessfully!');
        }

    }


    function state($state) {


        $db = new BaseDao();

        $num = 0;
        foreach($_POST['id'] as $id) {
            $num += $db->update('product',['state'=>$state], ['id'=>$id]);
        }

        $mess=[' Shelve Off ', ' Shelve On '];

        if($num>0) {
            $this->success('/admin/product', $num.''.$mess[$state].'successfully!');
        }else {
            $this->error('/admin/product', 'batch'.$mess[$state].'unsuccessfully!');
        }

    }

    function tuijian($tuijian) {


        $db = new BaseDao();

        $num = 0;
        foreach($_POST['id'] as $id) {
            $num += $db->update('product',['istuijian'=>$tuijian], ['id'=>$id]);
        }

        $mess=[' Cancel recommend ', ' Recommend all '];

        if($num>0) {
            $this->success('/admin/product', $num.''.$mess[$tuijian].'successfully!');
        }else {
            $this->error('/admin/product', 'batch'.$mess[$tuijian].'unsuccessfully!');
        }

    }
}
