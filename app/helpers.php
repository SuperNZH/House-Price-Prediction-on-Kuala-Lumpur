<?php
//通用函数放这儿
if(!function_exists('dd')) {
    function dd(...$args) {
        http_response_code(500);

        foreach($args as $x){
            var_dump($x);
        }

        die(1);
    }
}

function getCurUrl() {

    $url = 'http://';

    if(isset($_SERVER['SERVER_HTTPS']) && $_SERVER['SERVER_HTTPS'] == 'on') {
        $url = 'https://';
    }

    //判断端口
    if($_SERVER['SERVER_PORT'] != '80') {
        // https://www.eduwork.cn:8080
        $url .= $_SERVER['SERVER_NAME'] .':'.$_SERVER['SERVER_PORT'];
    }else{
        $url .= $_SERVER['SERVER_NAME'];
    }


    return $url;

}

function ew_login($utype){// 检查一下是前台用户还是后台用户
    return md5($_SESSION['id'].$_SERVER['HTTP_HOST']) == $_SESSION[$utype.'_token'] ? 1 : 0;// 比如后台的admin是admin_token。 1代表登陆成功，0代表失败
//    dd($_SESSION['id'].$_SERVER['HTTP_HOST'], $_SESSION[$utype.'_token'], $utype);
}

//获取用户IP， 定义一个函数getIP()
function getClientIP(){
    if (getenv("HTTP_CLIENT_IP")) {
        $ip = getenv("HTTP_CLIENT_IP");
    }elseif(getenv("HTTP_X_FORWARDED_FOR")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    }elseif(getenv("REMOTE_ADDR")) {
        $ip = getenv("REMOTE_ADDR");
    }else{
        $ip = "Unknow";
    }
    return $ip;
}