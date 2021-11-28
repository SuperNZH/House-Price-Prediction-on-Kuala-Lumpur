<?php
    namespace admin;
    use controllers\BaseControllers;
    use Gregwar\Captcha\CaptchaBuilder;
    use models\BaseDao;

    class Login extends BaseControllers {
        function index(){
            $this->display('admin/login/index');
        }


        function vcode() {
            $builder = new CaptchaBuilder;
            $builder->build();

            $_SESSION['code'] = strtoupper($builder->getPhrase());// 先放到Session里面

            header('Content-type: image/jpeg');
            $builder->output();
        }

        function dologin(){
            if(strtoupper($_POST['code']) != $_SESSION['code']){
                $this->error('/admin/login', 'Incorrect Captcha code input!');
                exit;
            }

            $name = $_POST['name'];

            $pw = md5(md5('e_'.$_POST['pw']));

            $db = new BaseDao();

            $user = $db->get('admin', ['id','name'], ['name'=>$name, 'pw'=>$pw]);

            if($user){
                $db ->update('admin', ['ltime'=>time()], ['id'=>$user['id']]);
                $_SESSION = $user;

                $_SESSION['admin_token'] = md5($user['id'].$_SERVER['HTTP_HOST']);// 用当前用户以及服务器一起做token,token也要加密一下

                $this->success('/admin', 'Login Successfully!');

            }else{
                $this->error('/admin/login', 'Incorrect account name or password!');
            }
        }

        function logout() {
            $_SESSION = array();

            if(isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time()-3600, '/');
            }

            session_destroy();

            $this->success('/admin/login', 'Administrator quit!');
        }


    }


