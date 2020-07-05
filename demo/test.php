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

