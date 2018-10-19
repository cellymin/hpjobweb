<?php

/**
 * 
* @ClassName: log
* @Description: todo(这里用一句话描述这个类的作用)
* @author zhouchao
* @date 2014-12-29 下午5:50:12
 */
class log {

    static $log = array();

    //记录日志内容
    static public function set($message, $log_type = 'notice') {
        if (!C("LOG_START"))
            return;
        if (in_array($log_type, C("LOG_TYPE"))) {
            $date = date("y-m-d h:i:s");
            self::$log [] = $message . "\t[时间]" . $date . "\r\n";
        }
    }

    //存储日志内容
    static public function save($type = 3, $destination = NULL, $extra_headers = NULL) {
        if (!C("LOG_START"))
            return;
        if (is_null($destination)) {
            $destination = PATH_LOG . '/' . date("ymd") . md5(C("LOG_KEY")) . ".log";
        }
        if ($type == 3) {
            if (is_file($destination) && filesize($destination) > C("LOG_SIZE")) {
                rename($destination, dirname($destination) . "/" . time() . ".log");
            }
        }
        error_log(implode("", self::$log), $type, $destination);
    }

    //写入日志内容
    static public function write($message, $type = 3, $destination = NULL, $extra_headers = NULL) {
        if (!C("LOG_START"))
            return;
        dir::create(PATH_LOG);
        if (is_null($destination)) {
            $destination = PATH_LOG . '/' . date("ymd") . md5(C("LOG_KEY")) . ".log";
        }

        if ($type == 3) {
            if (is_file($destination) && filesize($destination) > C("LOG_SIZE")) {
                rename($destination, dirname($destination) . "/" . time() . ".log");
            }
        }
        $now = date("y-m-d h:i:s");
        $message = $message . "\t[时间]" . $now . "\r\n";
        error_log($message, $type, $destination, $extra_headers = null);
    }

}