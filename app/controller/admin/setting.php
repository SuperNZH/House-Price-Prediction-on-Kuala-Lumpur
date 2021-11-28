<?php
    namespace admin;

    use models\BaseDao;
    use Slince\Upload\UploadHandlerBuilder;
    class Setting extends Admin{
        public function __construct()
        {
            parent::__construct();// 保证创建子类的时候，父类会被执行
            $this->assign('menumark', 'setting');
        }



        /*广告列表页面*/
        function index(){
            // 获取数据库操作对象
            $db = new BaseDao();

            // 获取全部的广告, 并能按ord排序
            $data = $db->select('setting', '*');

            // 将数据分给模板
            foreach ($data as $v){
                $this->assign($v['skey'], $v['svalue']);
            }


            // 分配标题
            $this->assign('title', 'Site Settings');

            // 导入模板
            $this->display('setting/mod');
        }



        function doupdate() {


            if($_FILES['web_logo']['error']==0){
                // 除了0就说明有一些其他错误发生，等于0表示有文件上传
                $path = TEMPDIR."/uploads";

                $builder = new UploadHandlerBuilder(); //create a builder.
                $handler = $builder

                    //add constraints
                    ->allowExtensions(['jpg', 'png', 'gif'])
                    ->allowMimeTypes(['image/*'])

                    ->saveTo($path) //save to local
                    ->getHandler();


                $files = $handler->handle();
                $filename = $files['web_logo']->getUploadedFile()->getClientOriginalName();
                $newfilename = date('Y-md') . '-' . uniqid() . '.' . $files['web_logo']->getUploadedFile()->getClientOriginalExtension();



                rename($path.'/'.$filename, $path.'/'.$newfilename);

                $_POST['web_logo'] = $newfilename;

            }


            $db = new BaseDao();
            $num = 0;
            foreach ($_POST as $k => $v){
                $num+=$db->update('setting', ['svalue'=>$v], ['skey'=>$k]);

            }



            if($num){
                $this->success('/admin/setting', 'Site information setup is complete!');
            }else{
                $this->error('/admin/setting', 'Site information setup unsuccessfully!');
            }

        }



    }