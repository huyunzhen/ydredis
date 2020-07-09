<?php
require_once '../src/ydredis/YdRedis.php';
require_once './vendor/autoload.php';

use \Yd\YdRedis;

$logger = new \Monolog\Logger('ydredis');
$logger->pushHandler(new \Monolog\Handler\StreamHandler('/tmp/ydredis.log', \Monolog\Logger::DEBUG));

$loggerSentinel = new \Monolog\Logger('ydredis_sentinel');
$loggerSentinel->pushHandler(new \Monolog\Handler\StreamHandler('/tmp/ydredis_sentinel.log', \Monolog\Logger::DEBUG));

$loggerCluster = new \Monolog\Logger('ydredis_cluster');
$loggerCluster->pushHandler(new \Monolog\Handler\StreamHandler('/tmp/ydredis_cluster.log', \Monolog\Logger::DEBUG));

//配置加载有两种方式：文件，变量
//从文件中加载全局配置
YdRedis::loadConf('./redis.conf');

//从变量中加载全局配置
//YdRedis::setCfgs(parse_ini_file('./redis.conf', true));

//可以不使用全局配置，而单独创建实例对像
////参数：
//    $insKey: 此实例的唯一标识符，指 redis.conf 中的 [default]/[senti]/[cluster], 写日志时会用到此项
//    $cfg：redis的配置
//$cfg = [
//    'db'       => 0,
//    'cmdlog'   => 1,
//    'timeout'  => 0,
//    'password' => 'redisadmin',
//    'address'  => '127.0.0.1:6379',
//];
//$ydredis = new YdRedis('default', $cfg);

//库中自带日志功能，如有需要，可以指定全局日志, 实例对象日志
//全局logger
YdRedis::setDefaultLogger($logger);
//实力对象日志
//$ydredis->setLogger($logger);

print("连接到master, 使用全局日志\n");
$redis = YdRedis::ins();
$result = $redis->set('a', 'jwtest'.date('Y-m-d H:i:s'));
var_dump($result);
var_dump($redis->get('a'));
var_dump("lastError: ".$redis->lastError());
print("\n\n");
var_dump($redis->get('a'));
//重连
$redis->reconn();

print("连接到sentinel, 使用实力对象日志\n");
//$redisSenti = YdRedis::ins('senti');
$cfgs = parse_ini_file('./redis.conf', true);
$redisSenti = new YdRedis('senti', $cfgs['senti']);
$redisSenti->setLogger($loggerSentinel);
$result = $redisSenti->set('a', 'jwtest'.date('Y-m-d H:i:s'));
var_dump($result);
var_dump($redisSenti->get('a'));
var_dump("lastError: ".$redisSenti->lastError());
print("\n\n");
//重连
$redisSenti->reconn();

print("连接到cluster, 使用实力对象日志\n");
$redisCluster = YdRedis::ins('cluster');
$redisCluster->setLogger($loggerCluster);
$result = $redisCluster->set('a', 'jwtest'.date('Y-m-d H:i:s'));
var_dump($result);
var_dump($redisCluster->get('a'));
var_dump("lastError: ".$redisCluster->lastError());
print("\n\n");
//重连
$redisCluster->reconn();
