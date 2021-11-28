<?php
    namespace admin;

    use controllers\BaseControllers;
    // admin文件下的控制器都继承这个类，不去继承BaseControllers
    class Admin extends BaseControllers// 只要继承了admin，就可以到这个目录下找东西
    {
        public function __construct()
        {

            $loader = new \Twig\Loader\FilesystemLoader(TEMPDIR.'/app/views/admin');
            $this->twig = new \Twig\Environment($loader, [
                //  'cache' => '/path/to/compilation_cache',
            ]);

            $this->assign('session', $_SESSION);

            if(!ew_login('admin')) {
                $this->error('/admin/login', 'You have not logged in yet, please log in first!');
            }

        }
        protected function display($template)
        {
            $url = getCurUrl();

            $this->assign('url', $url.'/app/views/admin/resource'); // 自己模板下的CSS，js，images
            $this->assign('public', $url.'/app/views/public'); // 所有模板公共的前端CSS，js，images
            $this->assign('res', $url.'/uploads'); // 文件上传的资源位置


            echo $this->twig->render($template . '.html', $this->data);
        }
    }


