<?php
require_once './vendor/autoload.php';

use \Yd\YdRedis;

$logger = new \Monolog\Logger('ydredis');
$logger->pushHandler(new \Monolog\Handler\StreamHandler('/tmp/ydredis.log', \Monolog\Logger::DEBUG));

//配置加载有两种方式：文件，变量
//从文件中加载配置
YdRedis::loadConf('./redis.conf');

//从变量中加载配置
//YdRedis::setCfgs(parse_ini_file('./redis.conf', true));

//库中自带日志功能，如果需要指定日志文件，可以传指定的logger
YdRedis::setLogger($logger);

print("连接到master\n");
$redis = YdRedis::ins();
$result = $redis->set('a', 'jwtest'.date('Y-m-d H:i:s'));
var_dump($result);
var_dump($redis->get('a'));
var_dump("lastError: ".$redis->lastError());
print("\n\n");
//重连
$redis->reconn();

print("连接到sentinel\n");
$redisSenti = YdRedis::ins('senti');
$result = $redisSenti->set('a', 'jwtest'.date('Y-m-d H:i:s'));
var_dump($result);
var_dump($redisSenti->get('a'));
var_dump("lastError: ".$redisSenti->lastError());
print("\n\n");
//重连
$redisSenti->reconn();

print("连接到cluster\n");
$redisCluster = YdRedis::ins('cluster');
$result = $redisCluster->set('a', 'jwtest'.date('Y-m-d H:i:s'));
var_dump($result);
var_dump($redisCluster->get('a'));
var_dump("lastError: ".$redisCluster->lastError());
print("\n\n");
//重连
$redisCluster->reconn();
