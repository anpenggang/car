<?php
    //连接本地的 Redis 服务
   $redis = new \Redis();
   $redis->connect('127.0.0.1', 6379);
   $auth = $redis->auth('jiangfengloveheibaixiaoyuan'); //设置密码
   //设置 redis 字符串数据
   $redis->set("1072", "0");
   // 获取存储的数据并输出
   echo "Stored string in redis:: " . $redis->get("1060");
