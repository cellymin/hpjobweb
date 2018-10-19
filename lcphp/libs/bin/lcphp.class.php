<?php
/**
 * 
* @ClassName: lcphp
* @Description: todo(这里用一句话描述这个类的作用)
* @author zhouchao
* @date 2014-12-29 下午5:45:21
 */

class lcphp {

    function __construct() {
        if (method_exists($this, '__init')) {
            $this->__init();
        }
    }

    //魔术方法__get
    function __get($var) {
        return isset($this->$var) ? $this->$var : NULL;
    }

    //魔术方法__set
    function __set($var, $value) {
        if (property_exists($this, $var)) {
            $this->$var = $value;
        }
    }

    //魔术方法__call
    function __call($method, $args) {
        if (method_exists($this, $method)) {
            $this->$method($args);
        }
    }

}