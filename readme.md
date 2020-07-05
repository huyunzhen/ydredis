YdRedis
---- redis库的封装，主要是加了日志，增加了 redis/sentinel/cluster 配置的支持

说明
--------
本库是基于redis扩展，进行了业务使用上的封装
1. 统一 单节点直接/sentinel/cluster 连接的配置
2. 增加日志，如不配置则写到php的默认日志；也可以写到指定的日志文件。

Copyright & License
-------------------
YdRedis, redis扩展的二次封装, 版权所有 2018- 菁武.
代码遵守 MIT 协议, 见 LICENSE 文件。

Versions & Requirements
-----------------------
0.1.0, PHP >=5.4.0

Usage
-----
Add ``yd/ydredis`` as a dependency in your project's ``composer.json`` file (change version to suit your version of Elasticsearch):
```json
    {
        "require": {
            "yd/beanstalk": "0.1.0"
        }
    }
```

配置 redis.conf
```
;;[demo]
;;;; [可选]redis节点连接地址, 格式：<host>:<port>, 不支持多地址
;;address = 172.16.100.26:6379
;;;; [可选]redis sentinel节点连接地址, 格式：<host>:<port>, 多以逗号(,)分隔
;;sentinel_mastername = mymaster
;;sentinel_address = 172.16.100.26:26379, 172.16.100.26:26380
;;;; [可选]redis cluster连接地址, 格式：<host>:<port>, 多以逗号(,)分隔
;;cluster_address = 172.16.100.26:6379, 172.16.100.26:6380
;;;; [必选] redis密码
;;password = pi2paUAEDrTwfD9MzDnkTGDIm-QB0FLH
;;;; [必选] redis连接超时设置
;;timeout = 0
;;;; [可选] redis连接的db, 最好设置，每个db一个连接, cluster不需要此项
;;db = 0
;;;; [可选], 值on/off，默认off，执行命令写入日志
;;cmdlog = off

[default]
address = 127.0.0.1:6379
password = redisadmin
timeout = 0
db = 0
cmdlog = on

[senti]
mastername = mymaster
sentinel_address = 127.0.0.1:26380, 127.0.0.1:26381, 127.0.0.1:26382
password = redisadmin
timeout = 0
db = 0
cmdlog = off

[cluster]
cluster_address = 127.0.0.1:6390, 127.0.0.1:6391, 127.0.0.1:6392,  127.0.0.1:6393, 127.0.0.1:6394, 127.0.0.1:6395
password = redisadmin
timeout = 0
db = 0
cmdlog = off
```

```
<?php

require_once '../src/ydredis/YdRedis.php';
require_once '../vendor/autoload.php';

$logger = new \Monolog\Logger('ydredis');
$logger->pushHandler(new \Monolog\Handler\StreamHandler('/tmp/ydredis.log', \Monolog\Logger::DEBUG));

\Yd\YdRedis::loadConf('./redis.conf');
//YdRedis::setCfgs(parse_ini_file($confFile, true));
\Yd\YdRedis::setLogger($logger);

print("连接到master\n");
$redis = \Yd\YdRedis::ins();
$result = $redis->set('a', 'jwtest'.date('Y-m-d H:i:s'));
var_dump($result);
var_dump($redis->get('a'));
var_dump("lastError: ".$redis->lastError());
print("\n\n");

print("连接到sentinel\n");
$redisSenti = \Yd\YdRedis::ins('senti');
$result = $redisSenti->set('a', 'jwtest'.date('Y-m-d H:i:s'));
var_dump($result);
var_dump($redisSenti->get('a'));
var_dump("lastError: ".$redisSenti->lastError());
print("\n\n");

print("连接到cluster\n");
$redisCluster = \Yd\YdRedis::ins('cluster');
$result = $redisCluster->set('a', 'jwtest'.date('Y-m-d H:i:s'));
var_dump($result);
var_dump($redisCluster->get('a'));
var_dump("lastError: ".$redisCluster->lastError());
print("\n\n");

?>
```
