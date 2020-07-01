<?php
require_once './src/redis/YdRedis.php';
require_once './vendor/autoload.php';


$logger = new \Monolog\Logger('ydredis');
$logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__.'/my_app.log', \Monolog\Logger::DEBUG));


\Yd\YdRedis::loadConf('./test/redis.conf');
//YdRedis::setCfgs(parse_ini_file($confFile, true));
\Yd\YdRedis::setLogger($logger);
$yd = \Yd\YdRedis::ins();
var_dump($yd->get('a'));
var_dump("lastError: ".\Yd\YdRedis::ins()->lastError());
