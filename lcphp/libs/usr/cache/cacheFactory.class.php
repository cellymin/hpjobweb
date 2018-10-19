<?php

/**
 * 
* @ClassName: cacheFactory
* @Description: todo(缓存驱动工厂)
* @author zhouchao
* @date 2014-12-30 上午8:32:44
 */
final class cacheFactory {

    public static $cacheFactory = null; //静态工厂实例
    protected $cache_list = array(); //驱动链接组

    /**
     * 构造函数
     */

    private function __construct() {
        
    }

    /**
     * 返回工厂实例，单例模式
     */
    public static function factory($driver = null) {
        //只实例化一个对象
        if (is_null(self::$cacheFactory )) {
            self::$cacheFactory = new cacheFactory();
        }
        if (is_null($driver)) {
            $driver = C("CACHE_DEFAULT_TYPE");
        }
        if (isset(self::$cacheFactory->driver_list[$driver])) {
            return self::$cacheFactory->driver_list[$driver];
        }
        self::$cacheFactory->getDriver($driver);
        return self::$cacheFactory->driver_list[$driver];
    }

    /**
     * 获得数据库驱动接口
     * @param string $driver
     */
    private function getDriver($driver) {
        $class = $driver . 'Cache'; //缓存驱动
        $classFile = PATH_LC . '/libs/usr/cache/driver/'. $class . '.class.php'; //加载驱动类库文件
        load_file($classFile);
        $this->driver_list[$driver] = new $class;
    }



}
