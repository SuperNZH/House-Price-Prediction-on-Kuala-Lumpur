<?php
    namespace admin;

    use models\BaseDao;
    use Slince\Upload\UploadHandlerBuilder;
    class ad extends Admin{
        public function __construct()
        {
            parent::__construct();// 保证创建子类的时候，父类会被执行
            $this->assign('menumark', 'ad');

            // 有五个广告位置
            $this->assign('allposition', ['1'=>'AD at TOP(980*80)', '2'=>'AD at BOTTOM(980*80)', '3'=>'AD at All TOP(980*80)', '4'=>'AD at All BOTTOM(980*80)', '5'=>'AD at sideshow(730*300)']);
        }



        /*广告列表页面*/
        function index(){
            // 获取数据库操作对象
            $db = new BaseDao();

            // 获取全部的广告, 并能按ord排序
            $data = $db->select('ad', '*',['ORDER'=>['ord'=>'ASC', "id"=>"DESC"]]);

            // 将数据分给模板
            $this->assign('data', $data);


            // 分配标题
            $this->assign('title', 'Ads List');

            // 导入模板
            $this->display('ad/index');
        }

        function add() {
            //如果$_POST['do_submit']存在，说明是添加的动作
            if(isset($_POST['do_submit'])) {
                $path = TEMPDIR."/uploads/ad";

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

                $db = new BaseDao();

                unset($_POST['do_submit']);

                $_POST['logo'] = $newfilename;

                if($db -> insert('ad', $_POST)) {
                    $this->success('/admin/ad', 'Add Successfully!');
                }else{
                    $this->error('/admin/ad/add', 'Add unsuccessfully!');
                }
            }

            $this->assign('title', 'Add Advertisements');
            $this->display('ad/add');
        }

        function mod($id){// 修改哪个广告，就会把它的相应的id传过来
            $db = new BaseDao();

            $this->assign("ad", $db->get('ad', '*',['id'=>$id]));

            $this->assign('title', 'Edit The Ad');
            $this->display('ad/mod');
        }

        function doupdate() {
            $id = $_POST['id'];
            unset($_POST['id']);

            if($_FILES['logo']['error']==0){
                // 除了0就说明有一些其他错误发生，等于0表示有文件上传
                $path = TEMPDIR."/uploads/ad";

                $builder = new UploadHandlerBuilder(); //create a builder.
                $handler = $builder

                    //add constraints
                    ->allowExtensions(['jpg', 'png', 'gif'])
                    ->allowMimeTypes(['image/*'])

                    ->saveTo($path) //save to local
                    ->getHandler();


                $files = $handler->handle();
                $filename = $files['logo']->getUploadedFile()->getClientOriginalName();
                $newfilename = date('Y-md') . '-' . uniqid() . '.' . $files['logo']->getUploadedFile()->getClientOriginalExtension();

                rename($path.'/'.$filename, $path.'/'.$newfilename);

                $_POST['logo'] = $newfilename;
            }


            $db = new BaseDao();
            $logo = $db->get('ad', 'logo', ['id'=>$id]);

            if($db->update('ad', $_POST, ['id'=>$id])) {
                $this->success('/admin/ad', 'Edit Successfully!');
                $path = TEMPDIR."/uploads/ad";
                @unlink($path.'/'.$logo);

            }else{
                $this->error('/admin/ad/mod/'.$id, 'Edit Unsuccessfully!');
            }
        }


        function del($id){
            $db = new BaseDao();

            $logo = $db->get('ad', 'logo', ['id'=>$id]);

            if($db->delete('ad', ['id'=>$id])){
                $path = TEMPDIR."/uploads/ad";

                @unlink($path.'/'.$logo);// unlink拿来删除文件, @用来屏蔽一些报错，比如文件不存在之类的


                $this->success('/admin/ad', 'Delete Successfully!');
            }else{
                $this->error('/admin/ad', 'Delete Unsuccessfully!');
            }
        }

        function order(){
            $db = new BaseDao();

            $num = 0;
            foreach($_POST['ord'] as $id=>$ord){
                $num += $db->update('ad', ['ord'=>$ord], ['id'=>$id]);
            }

            if($num>0) {
                $this->success('/admin/ad', 'Reorder Successfully!');
            }else {
                $this->error('/admin/ad', 'Reorder Unsuccessfully!');
            }

        }
    }