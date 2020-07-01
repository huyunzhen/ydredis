<?php
namespace Yd;

class YdRedisException extends \Exception
{
}

class YdRedis {

    private $_cfg = null;
    private $_errors = [];
    private $_lastError = '';
    private $_insKey = null;
    private $_instance = null;
    public static $cfgs;
    public static $logger = null;
    public static $instances = [];

    public function setCfg($cfg, $insKey='default') {
        $this->_insKey = $insKey;
        $this->_cfg = self::parseCfg($cfg);
    }

    public static function setCfgs($cfgs = []) {
        foreach($cfgs as &$cfg) $cfg = self::parseCfg($cfg);
        self::$cfgs = $cfgs;
    }

    public static function setLogger($logger) {
        self::$logger = $logger;
    }

    public static function loadConf($confFile) {
        $cfgs = parse_ini_file($confFile, true);
        foreach($cfgs as &$cfg) $cfg = self::parseCfg($cfg);
        self::$cfgs =$cfgs;
    }

    public function __construct($cfg) {
        $this->_cfg = self::parseCfg($cfg);
    }

    public static function parseCfg($cfg = []) {
        if(isset($cfg['address'])) {
            $tmp = explode(':', $cfg['address']);
            $cfg['host'] = $tmp[0];
            $cfg['port'] = isset($tmp[1]) ? $tmp[1] : 6379;
        }
        if(isset($cfg['sentinel_address'])) {
            $cfg['sentis'] = [];
            $sentis = explode(',', $cfg['sentinel_address']);
            foreach($sentis as $row) {
                $row = trim($row);
                $tmp = explode(':', $row);
                if(count($tmp) != 2) {
                    //日志
                    continue;
                }
                $cfg['sentis'][] = [
                    'host' => $tmp[0],
                    'port' => $tmp[1],
                ];
            }
        }
        if(isset($cfg['cluster_address'])) {
            if(isset($cfg['cluster_address'])) {
            }
        }
        $cfg['cmdlog'] = isset($cfg['cmdlog']) && in_array($cfg['cmdlog'], ['on', 'off']) ? $cfg['cmdlog'] : 'off';
        return $cfg;
    }

    public static function ins($key = 'default') {
        if(!isset(self::$cfgs[$key])) {
            throw new YdRedisException("YdRedis {$key} 配置不存在！");
            return null;
        }
        if(!isset(self::$instances[$key])) {
            $cfg = self::$cfgs[$key];
            //连接Redis
            self::$instances[$key] = new self($cfg);
            $result = self::$instances[$key]->connectAuto();
            //$result = self::$instances[$key]->connectAuto();
            //if(!$result) {
            //    $msg = "{$key} 连接失败！";
            //    $this->_error($msg);
            //    self::$logger == null ? trigger_error("YdRedis {$msg}", E_USER_WARNING) : self::$logger->error("YdRedis {$msg}");
            //    throw new YdRedisException($msg);
            //    return null;
            //}
        }
        return self::$instances[$key];
    }

    //连接重连，均调用此方法
    public function connectAuto() {
        if(isset($this->_cfg['sentis'])) {
            $redis = new \Redis();
            $isConnect = false;
            foreach($this->_cfg['sentinels'] as $senti) {
                if($isConnect) break;
                $result = $redis->connect($senti['host'], $senti['port']);
                if($result) $isConnect = true;
            }
            if(!$isConnect) {
                $msg = "sentinel[{$this->_cfg['sentinel_address']}] 连接失败！";
                $this->_error($msg);
                self::$logger == null ? trigger_error("YdRedis {$msg}", E_USER_WARNING) : self::$logger->error("YdRedis {$msg}");
                //throw new YdRedisException($msg);
                return null;
            }
            $master = $redis->rawcommand('SENTINEL', 'get-master-addr-by-name', $this->_cfg['sentinel_mastername']);
            if(!$master) {
                $msg = "sentinel[{$this->_cfg['sentinel_address']}] get-master-addr-by-name 失败！";
                $this->_error($msg);
                self::$logger == null ? trigger_error("YdRedis {$msg}", E_USER_WARNING) : self::$logger->error("YdRedis {$msg}");
                //throw new YdRedisException($msg);
                return null;
            }
            $result = $redis->connect($master[0], $master[1]);
            if(!$result) {
                $msg = "connect {$master[0]}:{$master[1]} 失败！";
                $this->_error($msg);
                self::$logger == null ? trigger_error("YdRedis {$msg}", E_USER_WARNING) : self::$logger->error("YdRedis {$msg}");
                //throw new YdRedisException($msg);
                return null;
            }
            $this->_instance = $redis;
        }
        if(isset($this->_cfg['address'])) {
            $redis = new \Redis();
            try {
                $result = $redis->connect($this->_cfg['host'], $this->_cfg['port'], 5, null, 100);
                if(!$result) {
                    $msg = "connect {$this->_cfg['host']}:{$this->_cfg['port']} 失败！";
                    $this->_error($msg);
                    self::$logger == null ? trigger_error("YdRedis {$msg}", E_USER_WARNING) : self::$logger->error("YdRedis {$msg}");
                    //throw new YdRedisException($msg);
                    return null;
                }
            } catch(\RedisException $e) {
                $msg = "connect {$this->_cfg['host']}:{$this->_cfg['port']} 失败！".$e->getMessage();
                $this->_error($msg);
                self::$logger == null ? trigger_error("YdRedis {$msg}", E_USER_WARNING) : self::$logger->error("YdRedis {$msg}");
                //throw new YdRedisException($msg);
                return null;
            }
            $this->_instance = $redis;
        }
        if(isset($this->_cfg['cluster_address'])) {
            $redis = new RedisCluster(NUll,$this->_cfg['clusters']);
            $this->_instance = $redis;
        }
        $result = $this->_instance->auth($this->_cfg['password']);
        if(isset($this->_cfg['db'])) $result = $redis->select($this->_cfg['db']);
        return true;
    }

    public function reconn() {
        $this->_instance = null;
        $this->connectAuto();
    }

    public function __call($name, $params) {
        //记录日志RedisException: Redis server went away in xxxxxx

        if($this->_instance == null) {
            $msg = "未连接到redis！";
            $this->_error($msg);
            self::$logger == null ? trigger_error("YdRedis {$msg}", E_USER_WARNING) : self::$logger->error("YdRedis {$msg}");
            //throw new YdRedisException($msg);
            return null;
        }
        if(method_exists($this->_instance, $name)) {
            if(self::$logger && $this->_cfg['cmdlog']) {
                $log = "YdRedis {$this->_insKey} {$name} ".json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                self::$logger->info($log);
            }
            return call_user_func_array([$this->_instance, $name], $params);
        } else {
            $msg = "没有找到方法 {$name}！";
            $this->_error($msg);
            self::$logger == null ? trigger_error("YdRedis {$msg}", E_USER_WARNING) : self::$logger->error("YdRedis {$msg}");
            //throw new YdRedisException($msg);
            return null;
        }
    }

    protected function _error($msg) {
        $this->_lastError = $msg;
        if(count($this->_errors) >= 200) {
            array_shift($this->_errors);
        }
        array_push($this->_errors, $msg);
    }

    public function lastError() {
        return $this->_lastError;
    }

}

