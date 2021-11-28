<?php
    namespace models;

    use Medoo\Medoo;

    class BaseDao extends Medoo{
        function __construct()
        {
            // 连接数据库的一些方法

            // Connect the database. 改成options，才能调用父类构造方法
            $options = [
                'type' => 'mysql',
                'database_name' => DBNAME,
                'server' => HOST,
                'port' => PORT,
                'username' => USER,
                'password' => PASS,
                'prefix' => TABPREFIX
            ];
            parent::__construct($options);
        }
    }
