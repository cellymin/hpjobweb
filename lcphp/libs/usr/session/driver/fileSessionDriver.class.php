<?php


load_file(PATH_LC . '/libs/usr/session/sessionAbstract.class.php');
/**
 *
* @ClassName: fileSessionDriver
* @Description: todo(文件SESSION驱动)
* @author zhouchao
* @date 2014-12-30 上午8:37:58
 */
class fileSessionDriver extends sessionAbstract {

    public $session_save_path; //SESSION储存路径
    private $session_file; //当前用户SESSION文件

    function __construct() {
        parent::__construct();
        $this->session_save_path = PATH_SESSION;
    }

    function open() {
        return true;
    }

    function read($sid) {
        $this->session_file = $this->session_save_path . '/' . $sid;
        if (!is_file($this->session_file)) {
            return false;
        }
        return file_get_contents($this->session_file);
    }

    function write($sid, $data) {
        return file_put_contents($this->session_file, $data) ? true : false;
    }

    function destroy($sid) {
        if (is_file($this->session_file)) {

            file_put_contents('/var/www/html/hpjobweb/time.txt',date('Y-m-d H:i:s',time()).'----'.$sid.PHP_EOL,FILE_APPEND);

            unlink($this->session_file);
        }
    }

    function gc() {

        foreach (glob($this->session_save_path . "/*") as $file) {

            if (filemtime($file) + $this->session_lifetime < time()) {

                file_put_contents('/var/www/html/hpjobweb/time.txt',date('Y-m-d H:i:s',time()).'------'.$this->session_lifetime.'----'.filemtime($file).'----'.time().PHP_EOL,FILE_APPEND);

                unlink($file);
            }
        }
        return true;
    }

}

?>
