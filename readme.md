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

```
<?php

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
