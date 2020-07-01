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
0.1.0, PHP >=5.4.0 (in progress)

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

```
<?php
require 'vendor/autoload.php';
use Yd\Beanstalk\Client;

$cfg = [
    'persistent' => true, 
    'host' => '127.0.0.1', 
    'port' => 11300, 
    'timeout' => 1,                 //连接超时设置
    'stream_timeout' => 1,          //数据流超时设置
    'force_reserve_timeout' => 1,   //强制 reserve 设置超时，默认1秒
];
$client = new Client($cfg);
$tube = 'flux';

//队列生产者示例
$client->connect();
$client->useTube($tube);
$client->put(
    23,                         // 设置任务优先级23Give the job a priority of 23.
    0,                          // 不等待，直接发送任务到ready队列
    60,                         // 设置任务1分钟的执行时间
    '/path/to/cat-image.png'    // 任务的内容
);
$client->disconnect();

//队列消费者示例
$client = new Client();
$client->connect();
$client->watch($tube);

while(true) {
    $job = $client->reserve();  // 阻塞，直到有可用的任务
    // 网络原因，会导致取不到可用的任务，而返回false
    // 极个别情况下会形成死循环
    if($job === false) {
        sleep(1);
        continue;
    }
    // $job 实例如下：
    // array('id' => 123, 'body' => '/path/to/cat-image.png')

    // 设置任务执行中
    $result = touch($job['body']);

    if($result) {
        $client->delete($job['id']);
    } else {
        $client->bury($job['id']);
    }
}

// 断开连接
// $client->disconnect();

?>
```

单元测试
-----------------
此库中包括单元测试， 你需要先启动 beanstalkd 实例

$ beanstalkd -VV -l 127.0.0.1 -p 11300

执行如下命令，运行单元测试：

$ cd /path/to/beanstalk/src
$ phpunit -c ../phpunit.xml

[1] http://www.phpunit.de/manual/current/en/installation.html
