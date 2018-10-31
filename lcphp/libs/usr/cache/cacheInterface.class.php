<?php


/**
 * 
* @ClassName: cacheInterface
* @Description: todo(缓存处理接口)
* @author zhouchao
* @date 2014-12-30 上午8:33:03
 */
interface cacheInterface{
    public function set($name, $data, $time = null, $path = null);//设置缓存,如果过期删除
    public function get($name, $path = null);//获得缓存数据
    public function is_cache($name, $time = null,$path = null);//是否存在缓存,如果过期删除
    public function del($name, $path = null);//删除缓存
    public function delall();//删除所有缓存数据
}
?>
