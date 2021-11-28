<?php
    namespace admin;// 类前缀

    use models\BaseDao;

    class Index extends Admin {

        function __construct()
        {
            // $this->assign("menumark", 'index');
            parent::__construct();
        }

        function index(){

            $db = new BaseDao();

            $this->assign("title", "Background Home Page");

            $tongji = [];// 用在index的统计页面

            // 商品的统计 ->debug()可以用来看语法对没对
            // 上架的商品
            $tongji['product_up'] = $db->count('product', ['state'=>1]);// 1是上架的意思

            // 下架的商品
            $tongji['product_down'] = $db->count('product', ['state'=>0]);// 0是下架的意思
            // 缺货的商品 - 上架了且数量=1的商品
            $tongji['product_empty'] = $db->count('product', ['state'=>1, 'num[<]'=>1]);
            // 推荐的商品
            $tongji['product_tuijian'] = $db->count('product', ['state'=>1, 'istuijian'=>1]);

            // 访客统计
            $today = strtotime(date('y-m-d'));// 今天的时间戳
            $yesterday = strtotime(date('y-m-d', strtotime('-1 day')));// 昨天的时间戳



            // 今日访客  用时间戳去判读是否今天
            $tongji['iplog_today'] = $db->count('iplog', ['atime[>=]'=>$today]);// atime 是用户访问的时间

            // 昨日访客
            $tongji['iplog_lastday'] = $db->count('iplog', ['atime[>=]'=>$yesterday, 'atime[<]'=>$today]);// 大于昨天且小于今天的就是昨日访客

            // 累计访客
            $tongji['iplog_all'] = $db->count('iplog');// select * from e_iplog;

            // 所有注册用户数量
            $tongji['iplog_user'] = $db->count('user');// select * from e_user;

            // 交易数据 -- select*from e_order;有三个order,但是一个order不止买一个东西 用 select * from e_orderdata;可以看到有很多数据-6个


            // 今日订单
            $tongji['order_today'] = $db -> count('order', ['atime[>=]'=>$today]);

            $money_today = $db->sum('order', 'productmoney', ['atime[>=]'=>$today]);
            $money_today=(float)$money_today;
            $tongji['money_today'] = number_format($money_today, 2, '.','');
            // 昨日订单
            $tongji['order_lastday'] = $db -> count('order', ['atime[>=]'=>$yesterday, 'atime[<]'=>$today]);

            $money_lastday = $db->sum('order', 'productmoney', ['atime[>=]'=>$yesterday, 'atime[<]'=>$today]);
            $money_lastday=(float)$money_lastday;
            $tongji['money_lastday'] = number_format($money_lastday, 2, '.','');


            // 全部订单
            $tongji['order_all'] = $db -> count('order');

            $money_all = $db->sum('order', 'productmoney');
            $money_all=(float)$money_all;
            $tongji['money_all'] = number_format($money_all, 2, '.','');




            $this->assign($tongji);

            $this->display("index/index");
        }
    }