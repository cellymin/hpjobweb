<?php


load_file(PATH_LC . '/libs/usr/session/sessionAbstract.class.php');
/**
 * 
* @ClassName: memcacheDriver
* @Description: todo(基于MEMCACHE的SESSION处理引擎)
* @author zhouchao
* @date 2014-12-30 上午8:37:58
 */
class memcacheDriver extends sessionAbstract {

	/**
	 * Memcache连接对象
	 * @access private
	 * @var Object
	 */
	private $memcache;
	
    function __construct() {
       
    }

    function open() {
    	$options = C("SESSION_OPTIONS");
    	$this->memcache = new Memcache();
    	$this->memcache->connect($options['host'], $options['port'], 2.5);
        return true;
    }

	/**
     * 获得缓存数据
     * @param string $sid
     * @return boolean
     */
    public function read($sid){
        return $this->memcache->get($sid);
    }

    /**
     * 写入SESSION
     * @param string $sid
     * @param string $data
     * @return mixed
     */
    public function write($sid, $data)
    {
        return $this->memcache->set($sid, $data);
    }

    /**
     * 删除SESSION
     * @param string $sid SESSION_id
     * @return boolean
     */
    public function destroy($sid)
    {
        return $this->memcache->delete($sid);
    }

    /**
     * 垃圾回收
     * @return boolean
     */
    public function gc()
    {
        return true;
    }

}

?>
