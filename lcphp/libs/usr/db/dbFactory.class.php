<?php

/**
 * 
* @ClassName: dbFactory
* @Description: todo(数据库驱动工厂)
* @author zhouchao
* @date 2014-12-30 上午8:35:56
 */
final class dbFactory {

    public static $dbFactory = null; //静态工厂实例
    protected $driver_list = array(); //驱动组

    /**
     * 构造函数
     */

    private function __construct() {
        
    }

    /**
     * 返回工厂实例，单例模式
     */
    public static function factory($driver = null, $tableName = null) {
        //只实例化一个对象
        if (is_null(self::$dbFactory)) {
            self::$dbFactory = new dbFactory();
        }
        if (is_null($driver)) {
            $driver = C("DB_DRIVER");
        }
        if (is_null($tableName)) {
            $tableName = 'empty';
        }
        if (isset(self::$dbFactory->driver_list[$tableName])) {
            return self::$dbFactory->driver_list[$tableName];
        }
        self::$dbFactory->getDriver($driver, $tableName);
        return self::$dbFactory->driver_list[$tableName];
    }

    /**
     * 获得数据库驱动接口
     * @param string $driver
     */
    private function getDriver($driver, $tableName) {

        $class = $driver . 'Driver'; //数据库驱动
        $classFile = PATH_LC . '/libs/usr/db/driver/' . $class . '.class.php'; //加载驱动类库文件
        
        load_file($classFile);
        $this->driver_list[$tableName] = new $class;
        $table = $tableName == 'empty' ? null : $tableName;
        $this->driver_list[$tableName]->connect($table);
    }

    /**
     * 释放连接驱动
     */
    private function close() {
        foreach ($this->driver_list as $db) {
            $db->close();
        }
    }

    /**
     * 析构函数
     * Enter description here ...
     */
    function __destruct() {
        $this->close();
    }

}
