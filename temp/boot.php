<?php  if(!defined('PATH_LC')){exit;}define('TEMP_DIR_EXISTS',1);function M($tableName = null, $full = null) {
    return new Model($tableName, $full);
}


function K($model) {
    $modelArr = get_model_file($model);
    if (!is_file($modelArr[0])) {
        error(L("functions_k_is_file") . $modelArr[0], false);
    }
    load_file($modelArr[0]);
    $modelClass = $modelArr[1] . 'Model';
    if (!class_exists($modelClass, false)) {
        error(L("functions_k_error"), false);
    }

    $model = new $modelClass($model);
    return $model;
}


function R($tableName = null, $full = null) {
    return new relationModel($tableName, $full);
}


function V($tableName = null, $full = null) {
    return new viewModel($tableName, $full);
}


function get_model_file($path) {
    $path = rtrim($path, '/');
    $path = str_ireplace("Model.php", '', $path);
    $path = str_replace(C("MODEL_FIX"), '', $path);
    $pathArr = explode('/', $path);
    $arr = array();
    switch (count($pathArr)) {
        case 1:
            $arr['app_group'] = '';
            $arr['app'] = PATH_APP . '/';
            $arr['model'] = $pathArr[0];
            break;
        case 2:
            $arr['app_group'] = PATH_APP_GROUP . '/';
            $arr['app'] = $pathArr[0] . '/';
            $arr['model'] = $pathArr[1];
            break;
        default:
            return array($path . C("MODEL_FIX") . '.php', array_pop($pathArr));
    }
    $modelFile = $arr['app_group'] . $arr['app'] . 'model/' . $arr['model'] . C("MODEL_FIX") . '.php';
    return array($modelFile, array_pop($pathArr));
}


function get_control_file($path) {
    $path = rtrim($path, '/');
    $path = str_ireplace(C("CONTROL_FIX") . ".php", '', $path);
    $path = str_replace(C("CONTROL_FIX"), '', $path);
    $pathArr = explode('/', $path);
    $arr = array();
    switch (count($pathArr)) {
        case 1:
            $arr['app_group'] = '';
            $arr['app'] = PATH_APP . '/';
            $arr['control'] = $pathArr[0];
            break;
        case 2:
            $arr['app_group'] = PATH_APP_GROUP . '/';
            $arr['app'] = $pathArr[0] . '/';
            $arr['control'] = $pathArr[1];
            break;
        default:
            return array($path . C("CONTROL_FIX") . '.php', array_pop($pathArr));
    }
    $controlFile = $arr['app_group'] . $arr['app'] . 'control/' . $arr['control'] . C("CONTROL_FIX") . '.php';
    return array($controlFile, array_pop($pathArr));
}


function Control($control) {
    $controlArr = get_control_file($control);
    static $_control = array();
    $name = md5($controlArr[0]);
    if (isset($_control [$name])) {
        return $_control [$name];
    }
    $control_file = $controlArr[0];
    load_file($control_file);
    $controlClass = $controlArr[1] . "Control";
    if (class_exists($controlClass, false)) {
        $_control [$name] = new $controlClass();
        return $_control [$name];
    } else {
        error($control_file . L("functions_control_error") . $control[1], false);
    }
}


function md5_d($var) {
    return md5(serialize($var));
}


function dir_create($dirName, $auth = 0777) {
    $dirName = str_replace("\\", "/", $dirName);
    $dirPath = substr($dirName, "-1") == "/" ? $dirName : $dirName . "/";
    if (is_dir($dirPath))
        return true;
    $dirs = explode('/', $dirPath);
    $dir = '';
    
    foreach ($dirs as $v) {
        $dir .= $v . '/';
        if (is_dir($dir))
            continue;
        @mkdir($dir, $auth,true);
        
    }
    return is_dir($dirPath);
}


function O($class, $method = '', $args = array()) {
    static $result = array();
    $name = empty($args) ? $class . $method : $class . $method . md5_d($args);
    if (!isset($result [$name])) {
        $class = new $class ();
        if (!empty($method) && method_exists($class, $method)) {
            if (!empty($args)) {
                $result [$name] = call_user_func_array(array(&$class, $method), $args);
            } else {
                $result [$name] = $class->$method();
            }
        } else {
            $result [$name] = $class;
        }
    }
    return $result [$name];
}



function load_file($file = "") {
    static $fileArr = array();
    static $time = array();
    if (!isset($time['start'])) {
        $time['start'] = microtime(true);
    }
    if (empty($file)) {
        return $fileArr;
    }
    $file_path = realpath($file);
    if (!is_file($file)) {
        error($file . L("functions_load_file_is_file"), false);
    }
    $name = md5($file);
    if (isset($fileArr [$name])) {
        return $fileArr [$name];
    }
    if (is_file($file_path)) {
        include $file;
        $fileArr[$name] = array();
        $fileArr [$name]['path'] = $file_path;
        $fileArr[$name]['time'] = number_format(microtime(true) - $time['start'], 3);
        if (function_exists("memory_get_usage")) {
            $fileArr [$name]['memory'] = number_format(memory_get_usage() / pow(1024, 1), 0) . "kb";
        } else {
            $fileArr [$name]['memory'] = "0kb";
        }
        return true;
    } elseif (C("DEBUG")) {
        error($file . C("load_file_debug"), false);
    }
}


function url_remove_param($var, $url = null) {
        return url::url_remove_param($var, $url);
}


function get_size($size, $decimals = 2) {
    switch (true) {
        case $size > pow(1024, 3):
            return round($size / pow(1024, 3), $decimals) . " GB";
        case $size > pow(1024, 2):
            return round($size / pow(1024, 2), $decimals) . " MB";
        case $size > pow(1024, 1):
            return round($size / pow(1024, 1), $decimals) . " KB";
        default:
            return $size . 'B';
    }
}


function array_defined($arr) {
    foreach ($arr as $k => $v) {
        $k = strtoupper($k);
        if (is_string($v)) {
            define($k, $v);
        } elseif (is_numeric($v)) {
            defined($k, $v);
        } elseif (is_bool($v)) {
            $v = $v ? 'true' : 'false';
            define($k, $v);
        }
    }
    return true;
}


function C($name = null, $value = null) {
    static $config = array();
    if (is_null($name)) {
        return $config;
    }
    if (is_string($name)) {
        $name = strtolower($name);
        if (!strstr($name, '.')) {
            if (is_null($value)) {
                if (isset($config[$name]) && !is_array($config[$name])) {
                    $config[$name] = trim($config[$name]);
                }
                return isset($config [$name]) && !empty($config[$name]) ? $config [$name] : null;
            }
            $config [$name] = $value;
            return $config[$name];
        }
        $name = array_change_key_case_d(explode(".", $name), 0);
        if (is_null($value)) {
            return isset($config [$name[0]] [$name[1]]) ? $config [$name[0]][$name[1]] : null;
        }
        $config [$name[0]] [$name[1]] = $value;
    }
    if (is_array($name)) {
        $config = array_merge($config, array_change_key_case_d($name, 0));
        return true;
    }
}
function L($name = null, $value = null) {
    static $languge = array();
    if (is_null($name)) {
        return $languge;
    }
    if (is_string($name)) {
        $name = strtolower($name);
        if (!strstr($name, '.')) {
            if (is_null($value))
                return isset($languge [$name]) ? $languge [$name] : null;
            $languge [$name] = $value;
            return $languge[$name];
        }
        $name = array_change_key_case_d(explode(".", $name), 0);
        if (is_null($value)) {
            return isset($languge [$name[0]] [$name[1]]) ? $languge [$name[0]][$name[1]] : null;
        }
        $languge [$name[0]] [$name[1]] = $value;
    }
    if (is_array($name)) {
        $languge = array_merge($languge, array_change_key_case_d($name, 0));
        return true;
    }
}
function tplCompile($tplFile = null) {
    static $file = array();
    if (is_null($tplFile))
        return $file;
    $file[] = $tplFile;
}


function array_change_key_case_d($arr, $type = 0) {
    $function = $type ? 'strtoupper' : 'strtolower';
    $newArr = array();
    if (!is_array($arr) || empty($arr))
        return $newArr;
    foreach ($arr as $k => $v) {
        $k = $function($k);
        if (is_array($v)) {
            $newArr[$k] = array_change_key_case_d($v, $type);
        } else {
            $newArr[$k] = $v;
        }
    }
    return $newArr;
}


function array_change_value_case($arr, $type = 1) {
    $function = $type ? 'strtoupper' : 'strtolower';
    $newArr = array();
    foreach ($arr as $k => $v) {
        if (is_array($v)) {
            $newArr[$k] = array_change_value_case($v, $type);
        } else {
            $newArr[$k] = $function($v);
        }
    }

    return $newArr;
}


function php_merge($filenameArr, $delSpace = false) {
    if (!is_array($filenameArr)) {
        $filenameArr = array($filenameArr);
    }
    $str = '';
    foreach ($filenameArr as $file) {
        $data = trim(file_get_contents($file));
        $data = substr($data, 0, 5) == '<?php' ? substr($data, 5) : $data;
        $data = substr($data, - 2) == '?>' ? substr($data, 0, - 2) : $data;
        if ($delSpace) {
            $data = strip_space($data);
        }
        $str.=$data;
    }
    return $str;
}


function strip_space($data) {
    $data = trim(trim($data), "<?php");
    $data = trim($data, '?>');
    $preg = array(
        '/(?<!\\\\)\/\*[^;\]\}\)\'"]+?\*\//is',
        '/(?<=[,;{}])\s*\/\/.*/im',
        '/^\s*\/\/.*/im',


    );
    $data = preg_replace($preg, '', $data);
    return trim($data);
}


function throw_exception($msg, $type = "exceptionLC") {
    if (class_exists("exceptionLC")) {
        throw new $type($msg);
    } else {
        error($msg);
    }
}


function error($error, $showFile = true) {
    $exception = array();
    $backtrace = debug_backtrace();
    $exception ['message'] = "<b>[ERROR]</b> " . $error . "<br/>";
    if ($showFile) {
        $exception['message'].="\t<b>[FILE]</b> " . $backtrace[0]['file'] . "<br/>";
        $exception['message'].="\t<b>[LINE]</b> " . $backtrace[0]['line'] . "<br/>";
    }
    log::write(strip_tags($exception['message']));
    if (!C("DEBUG")) {
        $e['message'] = C("ERROR_MESSAGE") .
                "\t\t <span style='color:#666; font-weight:normal;'>
                    " . L("functions_error_debug") . "
                    </span>";
        include C("LIBS_ERROR_TPL");
        exit;
    }
    $e ['message'] = $exception['message'];
    include C("LIBS_ERROR_TPL");
    debug::show("app_start", "app_end");
    exit;
}


if ( ! function_exists('set_status_header'))
{
    function set_status_header($code = 200, $text = '')
    {
        $stati = array(
                            200 => 'OK',
                            201 => 'Created',
                            202 => 'Accepted',
                            203 => 'Non-Authoritative Information',
                            204 => 'No Content',
                            205 => 'Reset Content',
                            206 => 'Partial Content',

                            300 => 'Multiple Choices',
                            301 => 'Moved Permanently',
                            302 => 'Found',
                            304 => 'Not Modified',
                            305 => 'Use Proxy',
                            307 => 'Temporary Redirect',

                            400 => 'Bad Request',
                            401 => 'Unauthorized',
                            403 => 'Forbidden',
                            404 => 'Not Found',
                            405 => 'Method Not Allowed',
                            406 => 'Not Acceptable',
                            407 => 'Proxy Authentication Required',
                            408 => 'Request Timeout',
                            409 => 'Conflict',
                            410 => 'Gone',
                            411 => 'Length Required',
                            412 => 'Precondition Failed',
                            413 => 'Request Entity Too Large',
                            414 => 'Request-URI Too Long',
                            415 => 'Unsupported Media Type',
                            416 => 'Requested Range Not Satisfiable',
                            417 => 'Expectation Failed',

                            500 => 'Internal Server Error',
                            501 => 'Not Implemented',
                            502 => 'Bad Gateway',
                            503 => 'Service Unavailable',
                            504 => 'Gateway Timeout',
                            505 => 'HTTP Version Not Supported'
                        );

        if ($code == '' OR ! is_numeric($code))
        {
            echo '状态码必须是数字';
            exit;
        }

        if (isset($stati[$code]) AND $text == '')
        {
            $text = $stati[$code];
        }

        if ($text == '')
        {
            show_error('No status text available.  Please check your status code number or supply your own message text.', 500);
            echo '没有可用的状态描述文本。';
            exit;
        }

        $server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;

        if (substr(php_sapi_name(), 0, 3) == 'cgi')
        {
            header("Status: {$code} {$text}", TRUE);
        }
        elseif ($server_protocol == 'HTTP/1.1' OR $server_protocol == 'HTTP/1.0')
        {
            header($server_protocol." {$code} {$text}", TRUE, $code);
        }
        else
        {
            header("HTTP/1.1 {$code} {$text}", TRUE, $code);
        }
    }
}


function show($var) {
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}


function p($var) {
    show($var);
}

function dump($var) {
    show($var);
}


function go($url) {
    $url = getUrl($url);
    echo "<script type='text/javascript'>location.href='$url';</script>";
    exit;
}


function getUrl($path = '') {
    if (preg_match('/http:\/\/|https:\/\//i', $path))
        return $path;
    $url = '';
    $pathArr = array_filter(explode('/', trim($path, '/')));
    switch (count($pathArr)) {
        case 0:
            $url = __APP__;
            break;
        case 1:
            $url = __CONTROL__ . '/' . $pathArr[0];
            break;
        case 2:
            $url = __APP__ . '/' . $pathArr[0] . '/' . $pathArr[1];
            break;
        case 3:
            $url = __WEB__ . '/' . $pathArr[0] . '/' . $pathArr[1] . '/' . $pathArr[2];
            break;
        default:
            $url = $path;
            break;
    }
    return $url;
}


function ip_get_client() {
    return ip::ip_get_client();
}


function ajax_request() {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        return true;
    return false;
}


function addslashes_d($data) {
    if (is_string($data)) {
        return addslashes($data);
    }
    if (is_array($data)) {
        $var = array();
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $var[$k] = addslashes_d($v);
                continue;
            } else {
                $var[$k] = addslashes($v);
            }
        }
        return $var;
    }
}


function trim_script($str) {
    if(is_array($str)){
        foreach ($str as $key => $val){
            $str[$key] = trim_script($val);
        }
    }else{
        $str = preg_replace ( '/\<([\/]?)script([^\>]*?)\>/si', '&lt;\\1script\\2&gt;', $str );
        $str = preg_replace ( '/\<([\/]?)iframe([^\>]*?)\>/si', '&lt;\\1iframe\\2&gt;', $str );
        $str = preg_replace ( '/\<([\/]?)frame([^\>]*?)\>/si', '&lt;\\1frame\\2&gt;', $str );
        $str = str_replace ( 'javascript:', 'javascript：', $str );
    }
    return $str;
}


function stripslashes_d($data) {
    if (is_string($data)) {
        return stripslashes($data);
    }
    if (is_array($data)) {
        $var = array();
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $var[$k] = stripslashes_d($v);
                continue;
            } else {
                $var[$k] = stripslashes($v);
            }
        }
        return $var;
    }
}

function cache_set($name, $data, $time = null, $path = null) {
    $cacheObj = cacheFactory::factory();
    return $cacheObj->set($name, $data, $time, $path);
}


function cache_get($name, $path = null) {
    $cacheObj = cacheFactory::factory();
    return $cacheObj->get($name, $path);
}


function cache_exists($name, $time = null, $path = null) {
    $cacheObj = cacheFactory::factory();
    return $cacheObj->is_cache($name, $time, $path);
}


function cache_del($name, $path = null) {
    $cacheObj = cacheFactory::factory();
    return $cacheObj->del($name, $path);
}


function cache_delall() {
    $cacheObj = cacheFactory::factory();
    return $cacheObj->delAll();
}


function array_to_String($array, $level = 0) {
    if (!is_array($array)) {
        return "'" . $array . "'";
    }
    $space = '';
    for ($i = 0; $i <= $level; $i++) {
        $space.="\t";
    }
    $arr = "Array\n$space(\n";
    $c = $space;
    foreach ($array as $k => $v) {
        $k = is_string($k) ? '\'' . addcslashes($k, '\'\\') . '\'' : $k;
        $v = !is_array($v) && (!preg_match("/^\-?[1-9]\d*$/", $v) || strlen($v) > 12) ?
                '\'' . addcslashes($v, '\'\\') . '\'' : $v;
        if (is_array($v)) {
            $arr.="$c$k=>" . array_to_String($v, $level + 1);
        } else {
            $arr.="$c$k=>$v";
        }
        $c = ",\n$space";
    }
    $arr.="\n$space)";
    return $arr;
}


if (!function_exists('json_decode')) {

    function json_decode($json_value, $bool = false) {
        $json = new json();
        return $json->decode($json_value, $bool);
    }

}


function mobile_area($mob) {
    $mob = substr($mob, 0, 7);
    $string = file_get_contents(PATH_LC . "/org/dat/mobile.dat");
    $string = strstr($string, $mob);
    $num = strpos($string, "\n");
    if (!$num)
        return false;
    $end = substr($string, 0, $num);
    list($a, $area) = explode(",", $end);
    $toCharset = C("charset");
    if (preg_match("/utf8|utf-8/i", $toCharset)) {
        $toCharset = "UTF-8";
    }
    return iconv("gbk", $toCharset, $area);
}


function __autoload($classname) {
    if (substr($classname, -7) == 'Control' && strlen($classname) > 7) {
        $classFile = PATH_APP . '/control/' . $classname . '.php';
    } else {
        $classFile = PATH_LC . '/libs/bin/' . $classname . '.class.php';
    }
    if (C("USR_FILES." . $classname)) {
        $classFile = C("USR_FILES." . $classname);
    }
    load_file($classFile);
}


function extension_exists($ext) {
    $ext = strtolower($ext);
    $loaded_extensions = get_loaded_extensions();
    return in_array($ext, array_change_value_case($loaded_extensions, 0));
}


if (!function_exists('image_type_to_extension')) {

    function image_type_to_extension($type, $dot = true) {
        $e = array(1 => 'gif', 'jpeg', 'png', 'swf', 'psd', 'bmp',
            'tiff', 'tiff', 'jpc', 'jp2', 'jpf', 'jb2', 'swc',
            'aiff', 'wbmp', 'xbm');
        $type = (int) $type;
        return ($dot ? '.' : '') . $e[$type];
    }

}


function rand_str($len = 6) {
    $data = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $str = '';
    while (strlen($str) < $len)
        $str.=substr($data, mt_rand(0, strlen($data) - 1), 1);
    return $str;
}final class application {

    
    static public function run() {
        self::init();
        self::formatRequest();
        self::filter();
        self::setAppGroupPath();
        self::loadAppGroupConfig();
        self::setAppPath();
        self::loadAppConfig();
        self::loadUserFile();
        self::setPathConst();
        self::createDemoControl();
        self::setTplConst();
        self::language();
        self::ajaxCloseDebug();
        self::setCharset();
        self::createDir();
        self::session_set();
        self::compileAppGroupFile();
        self::compileAppFile();
        debug::start("app_start");
        self::apprun();
        debug::show("app_start", "app_end");
        log::save();
    }

    
    static private function loadAppGroupConfig() {
        if (!defined("APP_GROUP")) {
            return;
        }
        $app_group_config = PATH_APP_GROUP . '/config/config.php';
        is_file($app_group_config) && C(include $app_group_config);
        url::parse_url();
    }

    
    static private function loadAppConfig() {
        $app_config = PATH_APP . '/config/config.php';
        is_file($app_config) && C(include $app_config);
        if (!defined("APP_GROUP")) {
            url::parse_url();
        }
    }

    
    static private function loadUserFile() {
        C(include(PATH_LC . '/libs/boot/usrFiles.php'));
    }

    
    static private function setAppGroupPath() {
        if (defined("APP_GROUP_PATH")) {
            define("PATH_APP_GROUP", rtrim(str_replace("\\", "/", APP_GROUP_PATH), '/'));
            return;
        }
        if (defined("APP_GROUP")) {
            define("PATH_APP_GROUP", PATH_ROOT . '/' . APP_GROUP);
        } else {
            define("PATH_APP_GROUP", PATH_ROOT);
        }
    }

    
    static private function setAppPath() {
        if (defined("APP_PATH")) {
            define("PATH_APP", rtrim(str_replace("\\", "/", realpath(APP_PATH)), '/'));
            return;
        }
        if (defined("APP_NAME")) {
            define("PATH_APP", PATH_APP_GROUP . '/' . APP_NAME);
            return;
        }
        $var_app = C("var_app");
        if (!isset($_GET[$var_app]) && empty($_SERVER['PATH_INFO'])) {
            define("PATH_APP", PATH_APP_GROUP . '/' . C("DEFAULT_APP"));
            return;
        }
        if (isset($_GET[$var_app])) {
            define("PATH_APP", PATH_APP_GROUP . '/' . $_GET[$var_app]);
            return;
        }
        $pathinfo = rtrim($_SERVER['PATH_INFO'], '/');
        $path = explode(C("pathinfo_dli"), $pathinfo);
        foreach ($path as $k => $v) {
            if ($v == $var_app) {
                if (!isset($path[$k + 1])) {
                    header("Content-type:text/html;charset=utf-8");
                    error("URL中" . $var_app . "后连接应用名如:" . $var_app . '/admin 形式', false);
                }
                define("PATH_APP", PATH_APP_GROUP . '/' . $path[$k + 1]);
                return;
            }
        }
        define("PATH_APP", PATH_APP_GROUP . '/' . $path[0]);
    }
    static public function setCharset() {
        $charset = strtoupper(C("CHARSET")) == 'UTF8' ? "UTF-8" : strtoupper(C("CHARSET"));
        define("CHARSET", $charset);
    }
    static public function session_set() {
        $sessionDriver = sessionFactory::factory();
        $sessionDriver->init();
        if (C("SESSION_AUTO")) {
            session_start();
        }
    }

    
    static private function init() {
        C(include PATH_LC . '/libs/boot/config.php');
        C("DEBUG_TPL", PATH_LC . '/libs/boot/debug/tpl/debug.html');
        C("LIBS_ERROR_TPL", PATH_LC . '/libs/boot/debug/tpl/lc_error.html');
        @ini_set('memory_limit', '128M');
        @ini_set("register_globals", "off");
        @ini_set('magic_quotes_runtime', 0);
        define("MAGIC_QUOTES_GPC", @get_magic_quotes_gpc() ? true : false );
        C(require (PATH_LC . '/libs/boot/config.php'));
        if (function_exists("spl_autoload_register")) {
            spl_autoload_register(array(__CLASS__, "autoload"));
        }
        set_error_handler(array("exceptionLC", "error"), E_ALL);
        set_exception_handler(array("exceptionLC", "exception"));
       
        $_SERVER['REQUEST_URI'] = rtrim($_SERVER['REQUEST_URI'], '/');
        $_SERVER['DOCUMENT_ROOT'] = str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']);
        $_SERVER['SCRIPT_FILENAME'] = str_replace("\\", "/", $_SERVER['SCRIPT_FILENAME']);
        if (!strstr($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
            $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'].$_SERVER['REQUEST_URI'];
        }
        if (isset($_SERVER['PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = trim($_SERVER['PATH_INFO'], '/');
        }
        $charset = C("CHARSET");
        $charset = preg_match("/utf8|utf-8/i", $charset) ? "utf8" : (preg_match("/gbk|gb2312/i", $charset) ? "gbk" : $charset);
        $systemLanguage = in_array($charset, array("utf8", "gbk")) ? $charset : "en";
        $systemLanguage = PATH_LC . '/data/language/' . $systemLanguage . '.php';
        L(include $systemLanguage);
    }

    static private function formatRequest() {
        if (!MAGIC_QUOTES_GPC) {
            return;
        }
        $_GET = stripslashes_d($_GET);
        $_POST = stripslashes_d($_POST);
        $_REQUEST = stripslashes_d($_REQUEST);
        $_COOKIE = stripslashes_d($_COOKIE);
    }

    static private function filter(){
        $_POST = trim_script($_POST);
    }

    
    static private function setTplConst() {
        $tplDir = rtrim(C("TPL_DIR"), '/');
        if (empty($tplDir)) {
            $tplDir = 'tpl';
            C("TPL_DIR", $tplDir);
        }
        $template_dir = '';
        $style = C('TPL_STYLE') ? '/' . C('TPL_STYLE') : '';
        if (strstr($tplDir, '/')) {
            $template_dir = $tplDir . $style;
        } else {
            $template_dir = PATH_APP . '/' . $tplDir . $style;
        }
        define("PATH_TPL", $template_dir);
        define("__TPL__", __HOST__ . '/' . trim(str_ireplace($_SERVER['DOCUMENT_ROOT'], '', PATH_TPL), '/'));
        define("__PUBLIC__", __TPL__ . '/public');
    }

    
    static private function setPathConst() {
        define("PATH_CONTROL", PATH_APP . '/control');
        define("CACHE_APP_GROUP_PATH", defined("APP_GROUP") ? PATH_TEMP . '/Applications/' . APP_GROUP . '_G/' : PATH_TEMP . '/Applications/');
        define("CACHE_APP_PATH", CACHE_APP_GROUP_PATH . APP . '_A');
        define("CACHE_CONTROL_PATH", CACHE_APP_PATH . '/' . CONTROL . '_C');
        define("CACHE_METHODL_PATH", CACHE_CONTROL_PATH . '/' . METHOD);
        define("CACHE_TABLE_PATH", PATH_TEMP . '/table');
        define("CACHE_SELECT_PATH", CACHE_METHODL_PATH . '/select');
        define("CACHE_COMPILE_PATH", CACHE_METHODL_PATH . '/compile');
        define("CACHE_TPL_PATH", CACHE_METHODL_PATH . '/tpl');
    }

    
    static private function createDir() {
        if (!is_dir(CACHE_APP_PATH)) {
            dir::create(CACHE_APP_PATH);
        }
    }

    
    static private function route() {
        url::route();
    }

    
    static private function language() {
        $charset = C("CHARSET");
        $charset = preg_match("/utf8|utf-8/i", $charset) ? "utf8" : (preg_match("/gbk|gb2312/i", $charset) ? "gbk" : $charset);
        $language = in_array($charset, array("utf8", "gbk")) ? $charset : "en";
        if ($language) {
            $systemLanguage = PATH_LC . '/data/language/' . $language . '.php';
            L(include $systemLanguage);
            $appGroupLang = PATH_APP_GROUP . '/language/' . $language . '.php';
            $appLang = PATH_APP . '/language/' . $language . '.php';

            if (is_file($appGroupLang)) {
                L(include($appGroupLang));
            }
            if (is_file($appLang)) {
                L(include($appLang));
            }
        }
    }
    static private function ajaxCloseDebug() {
        if (ajax_request() && !C("debug_ajax")) {
            C("debug", 0);
        }
    }

    
    static private function compileAppGroupFile() {
        if (!defined('APP_GROUP')) {
            return;
        }
        $compileAppFile = CACHE_APP_GROUP_PATH . 'APP_GROUP_' . APP_GROUP . '.php';

        if (file_exists($compileAppFile) && !C("DEBUG")) {
            load_File($compileAppFile);
            return;
        }
        $appLibs = PATH_APP_GROUP . '/libs';
        if (C("DEBUG")) {
            $files = glob($appLibs . '/*');
            if (!$files)
                return;
            foreach ($files as $v) {
                load_file($v);
            }
        } else {
            $compileAppCon = '';
            $files = glob($appLibs . '/*');
            if (!$files)
                return;
            $compileAppCon = php_merge($files);
            $data = "<?php  if(!defined('PATH_LC')){exit;}" . $compileAppCon . " ?>";
            file_put_contents($compileAppFile, $data);
            load_File($compileAppFile);
        }
    }

    
    static private function compileAppFile() {
        $compileAppFile = CACHE_APP_PATH . '/APP_' . APP . '.php';
        if (file_exists($compileAppFile) && !C("DEBUG")) {
            load_File($compileAppFile);
            return;
        }
        $appLibs = PATH_APP . '/libs';
        if (C("DEBUG")) {
            $appFile = glob($appLibs . '/*');
            if (!$appFile)
                return;
            foreach ($appFile as $v) {
                load_file($v);
            }
        } else {
            $appFile = glob($appLibs . '/*');
            if (!$appFile)
                return;
            $compileAppCon = '';
            $compileAppCon = php_merge($appFile);
            $data = "<?php  if(!defined('PATH_LC')){exit;}" . $compileAppCon . " ?>";
            file_put_contents($compileAppFile, $data);
            load_File($compileAppFile);
        }
    }

    
    static public function apprun() {
        if (function_exists("date_default_timezone_set")) {
            date_default_timezone_set(C("default_time_zone"));
        }
        if (!is_dir(PATH_APP)) {
            error(APP.L("application_apprun4") .' <a href="'.dirname(__lcphp__).'/setup/index.php?m=delcache&temp=/temp">'.L("application_apprun_createapp").'</a>' , FALSE);
        }
        $controlFile = rtrim(PATH_APP, '/') . '/control/' . CONTROL;
        $control = Control($controlFile);
        $method = strtolower(METHOD);
        if (!method_exists($control, $method)) {

            if ($method == 'ueditorupload') {
                include (PATH_LC . '/org/ueditor/lc_upload.php');
            } elseif ($method == 'keditorupload') {
                include (PATH_LC . '/org/kindeditor/lc_upload.php');
            } elseif ($method == 'swfupload') {
                include (PATH_LC . '/org/swfupload250/lc_upload.php');
            } elseif ($method == 'swfuploaddel') {
                include (PATH_LC . '/org/swfupload250/lc_delfile.php');
            } else {
                error(L("application_apprun1") . $controlFile . C("CONTROL_FIX") . '.php' . L("application_apprun2") . $method . L("application_apprun3"), false);
            }
        }
            call_user_func(array(&$control, $method));
       
    }

    
    static private function autoload($classname) {
        if (substr($classname, -7) == 'Control' && strlen($classname) > 7) {
            $classFile = get_control_file($classname);
            $classFile = $classFile[0];
        } else {
            $classFile = PATH_LC . '/libs/bin/' . $classname . '.class.php';
        }
        if (C("USR_FILES." . $classname)) {
            $classFile = C("USR_FILES." . $classname);
        }
        load_file($classFile);
    }

    
    static private function createDemoControl() {
        if (is_dir(PATH_TEMP . '/Applications')) {
            return;
        }
        $demoDir = array();
        if (defined("APP_GROUP")) {
            $demoDir['demo_app_group_libs_dir'] = PATH_APP_GROUP . '/libs';
            $demoDir['demo_app_group_config_dir'] = PATH_APP_GROUP . '/config';
            $demoDir['demo_app_group_language_dir'] = PATH_APP_GROUP . '/language';
        }

        $demoDir['demo_app_dir'] = PATH_APP;
        if (is_dir($demoDir['demo_app_dir'])) {
            return;
        }
        $demoDir['demo_control_dir'] = PATH_APP . '/control';
        $demoDir['demo_model_dir'] = PATH_APP . '/model';
        $demoDir['demo_libs_dir'] = PATH_APP . '/libs';
        $demoDir['demo_config_dir'] = PATH_APP . '/config';
        $demoDir['demo_tpl_dir'] = PATH_APP . '/tpl';
        $demoDir['demo_language_dir'] = PATH_APP . '/language';
        $demoDir['demo_tpl_public_dir'] = PATH_APP . '/tpl/public';
        foreach ($demoDir as $v) {
            dir_create($v);
        }
       
        $demo_control_file = $demoDir['demo_control_dir'] . '/index' . C("CONTROL_FIX") . '.php';
        $data = <<<str
<?php
class indexControl extends Control{
    function index(){
        header("Content-type:text/html;charset=utf-8");
        echo "<div style='font-size:16px;font-weight:bold;color:#333;margin-left:20px;border:solid 2px #F00;width:500px;height:30px;padding:30px 50px 20px;'>基础目录已经创建成功！</div>";
    }
}
str;
        file_put_contents($demo_control_file, $data);
        copy(PATH_LC . '/libs/boot/config.php', $demoDir['demo_config_dir'] . '/config.php');
        copy(PATH_LC . '/data/tpl/error.html', $demoDir['demo_tpl_public_dir'] . '/error.html');
        copy(PATH_LC . '/data/tpl/success.html', $demoDir['demo_tpl_public_dir'] . '/success.html');

        $languageDataUtf8 = <<<str
<?php
/**
 * 本文件为语言包测试文件，在视图页面中通过{\$base.lang.title}即可调用
 * 可以创建任意多个语言文件如gbk,en,utf8等
 * 具体使用哪一个语言包可以能过C("language","utf8")这种方式设计或者直接修改配置文件
 */
if(!defined("PATH_LC"))exit;
return array(
    "title"=>"多语言测试",
);
?>
str;
        file_put_contents($demoDir['demo_language_dir'] . '/utf8.php', $languageDataUtf8);
    }

}final class url {

    static private $is_set_app_name;
    static private $query_string;
    static private $is_pathinfo;

    

    static public function parse_url() {
        self::get_query_string();
        self::$is_set_app_name = defined("APP_NAME") ? true : false;
        $get = array();
        if (defined("APP_NAME")) {
            $get [C('VAR_APP')] = APP_NAME;
        }

        if (self::$is_pathinfo) {

            $info = explode('/', self::$query_string);

            if (!defined("APP_NAME")) {
                if ($info [0] != C("VAR_APP")) {
                    $get [C('VAR_APP')] = $info [0];
                    array_shift($info);
                } else {
                    if (!isset($get[C('VAR_APP')])) {
                        exit("<div style='padding:20px;border:solid 1px #dcdcdc;font-size:14px;'>
                            " . L("_nohaveapp") . "</div>");
                    }
                    $get [C('VAR_APP')] = $info [1];
                    array_shift($info);
                    array_shift($info);
                }
            }
            if (isset($info[0]) && $info[0] != C('VAR_CONTROL')) {
                $get [C('VAR_CONTROL')] = isset($info [0]) ? $info[0] : C("DEFAULT_CONTROL");
                array_shift($info);
            } else {
                $get [C('VAR_CONTROL')] = isset($info [1]) ? $info[1] : C("DEFAULT_CONTROL");
                array_shift($info);
                array_shift($info);
            }
            if (isset($info[0]) && $info[0] != C("VAR_METHOD")) {
                $get [C('VAR_METHOD')] = isset($info [0]) ? $info[0] : C("DEFAULT_METHOD");
                array_shift($info);
            } else {
                $get [C('VAR_METHOD')] = isset($info [1]) ? $info[1] : C("DEFAULT_METHOD");
                array_shift($info);
                array_shift($info);
            }
            $count = count($info);
            for ($i = 0; $i < $count;) {
                $get [$info [$i]] = isset($info [$i + 1]) ? $info [$i + 1] : '';
                $i+=2;
            }
        }
        $_GET = array_merge($_GET, $get);
        $get =array();
        foreach($_GET as $k=>$v){
            if($v==''){
                continue;
            }
            $get[$k]=$v;
        }
        $_GET=$get;
        self::set_url_const();
    }

    
    static private function set_url_const() {
        define("APP", (defined("APP_NAME") ? APP_NAME : (isset($_GET [C('VAR_APP')]) && !empty($_GET[C('VAR_APP')]) ? $_GET [C('VAR_APP')] : C("DEFAULT_APP"))));
        define("CONTROL", (isset($_GET [C('VAR_CONTROL')]) && !empty($_GET[C('VAR_CONTROL')]) ? $_GET [C('VAR_CONTROL')] : C("DEFAULT_CONTROL")));
        define("METHOD", (isset($_GET [C('VAR_METHOD')]) && !empty($_GET[C('VAR_METHOD')]) ? $_GET [C('VAR_METHOD')] : C("DEFAULT_METHOD")));

        $host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        $url = isset($_SERVER['REDIRECT_URL']) ? str_replace($_SERVER['REQUEST_URI'], '', $_SERVER['REDIRECT_URL']) : $_SERVER['SCRIPT_NAME'];
        define("__HOST__", "http://" . trim($host, '/'));
        $root = trim(str_ireplace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])), DIRECTORY_SEPARATOR);
        $root = empty($root) ? "" : '/' . $root;
        define("__ROOT__", __HOST__ . $root);
        define("__lcphp__", __HOST__.$root . '/lcphp');


        define("__STATIC__", __ROOT__ . '/static');
        define("__DATA__", __ROOT__ . '/data');
        define("__WEB__", __HOST__ . $url);
        define("__TPLC__", CONTROL ."/". METHOD);

        $url_type = C("URL_TYPE");
        switch ($url_type) {
            case 1:
                define("__APP__", __WEB__ . (self::$is_set_app_name ? '' : '/' . APP));
                define("__CONTROL__", __APP__ . '/' . CONTROL);
                define("__METH__", __CONTROL__ . '/' . METHOD);
                break;
            case 2:
                define("__APP__", __WEB__ . (self::$is_set_app_name ? '' : '?' . C("VAR_APP") . '=' . APP));
                define("__CONTROL__", __APP__ . (self::$is_set_app_name ? '?' : '&') . C('VAR_CONTROL') . '=' . CONTROL);
                define("__METH__", __CONTROL__ . '&' . C('VAR_METHOD') . '=' . METHOD);
                break;
        }
        define('__URL__', self::get_format_url());
        define("__ORG__", __lcphp__ . '/org');
    }

    
    static private function get_format_url() {
        $pathinfo_dli = C("PATHINFO_Dli");
        $url_type = C("URL_TYPE");
        $url = '';
        switch ($url_type) {
            case 1:
                foreach ($_GET as $k => $v) {
                    if (in_array($k, array(C("VAR_APP"), C("VAR_CONTROL"), C("VAR_METHOD"))))
                        continue;
                    $url.=$pathinfo_dli . $k . $pathinfo_dli . $v;
                }
                $url = trim($url, $pathinfo_dli);
                $url = __METH__ . $pathinfo_dli . $url;
                break;
            case 2:
                foreach ($_GET as $k => $v) {
                    $url.=$k . '=' . $v . '&';
                }
                $url = trim($url, '&');
                $url = __WEB__ . '?' . $url;
                break;
        }
        return rtrim($url, $pathinfo_dli);
    }

    
    static private function get_query_string() {

        $pathinfo_var = C("PATHINFO_VAR");






        self::$is_pathinfo = true;
        self::$query_string = isset($_GET[$pathinfo_var]) ? $_GET[$pathinfo_var] : str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']);

        self::$query_string = trim(self::$query_string, '/');
        $pathinfo_dli = C("PATHINFO_Dli");
        self::del_pathinfo_html();
        if (C("route")) {
            self::parse_route();
        }
        $url = explode("?", self::$query_string);
        if (count($url) >= 2) {
            $arr = explode($pathinfo_dli, $url[0]);
            if (defined("APP_NAME")) {
                self::$query_string = count($arr) == 1 ? $url[0] . $pathinfo_dli . C("DEFAULT_METHOD") . $pathinfo_dli . $arr[1] : self::$query_string;
            } else {
                self::$query_string = count($arr) == 2 ? $url[0] . $pathinfo_dli . C("DEFAULT_METHOD") . $pathinfo_dli . $arr[1] : self::$query_string;
            }
        }
        self::$query_string = trim(preg_replace("/\?|=|&/i", '/', self::$query_string), '/');
        self::$query_string = str_replace($pathinfo_dli, '/', self::$query_string);
    }

    
    static private function del_pathinfo_html() {
        $pathinfo_html = "." . trim(C("PATHINFO_HTML"), ".");
        self::$query_string = str_ireplace($pathinfo_html, "", self::$query_string);
    }

    
    static private function parse_route() {
        $route = C("route");
        $search = array(
            "/(:year)/i",
            "/(:month)/i",
            "/(:day)/i",
            "/(:num)/i",
            "/(:any)/i",
            "/(:\w+)/i",
            "/\//",
        );
        $replace = array(
            "\d{2,4}",
            "\d{1,2}",
            "\d{1,2}",
            "\d+",
            ".+",
            "(\w+)",
            "\/",
        );

        foreach ($route as $k => $v) {
            $v = trim($v, '/');
            if (preg_match("/^\/.*\/[isUx]*$/i", $k)) {
                $v = str_replace("#", '\\', $v);
                if (preg_match($k, self::$query_string)) {
                    self::$query_string = preg_replace($k, $v, self::$query_string);
                    break;
                }
                continue;
            }
            $preg_k = "/^\/?" . preg_replace($search, $replace, $k) . "$/i";
            if (!preg_match($preg_k, self::$query_string)) {
                continue;
            }
            preg_match("/[^:\sa-z0-9]/i", $k, $routeVar);

            if (!$routeVar) {
                self::$query_string = $v;
                break;
            }
            $role = explode($routeVar[0], $k);
            $urls = explode($routeVar[0], self::$query_string);
            self::$query_string = $v;
            $getName = '';
            foreach ($role as $m => $n) {
                if (!strstr($n, ":")) {
                    continue;
                }
                $getName = str_replace(":", "", $n);
                $_GET[$getName] = $urls[$m];
            }
            break;
        }
    }

    
    static public function url_remove_param($var, $url = null) {
        $pathinfo_dli = C("PATHINFO_Dli");
        if (!is_null($url)) {
            $url = strstr($url, "&") ? $url . '&' : $url . $pathinfo_dli;
            $url = str_replace($pathinfo_dli, "###", $url);
            $search = array(
                "/$var" . "###" . ".*?" . "###" . "/",
                "/$var=.*?&/i",
                "/\?&/",
                "/&&/"
            );
            $replace = array(
                "",
                "",
                "?",
                ""
            );
            $url = preg_replace($search, $replace, $url);
            $url = rtrim($url, "&");
            $url = rtrim($url, "###");
            $url = str_replace("###", $pathinfo_dli, $url);
            return $url;
        }
        $get = $_GET;
        unset($get[C("VAR_APP")]);
        unset($get[C("VAR_CONTROL")]);
        unset($get[C("VAR_METHOD")]);
        $url = '';
        $url_type = C("URL_TYPE");
        if(!is_array($var)){
            $var=array($var);
        }
        foreach ($get as $k => $v) {

            if(in_array($k,$var)){
                continue;
            }
            if ($url_type == 1) {
                $url.=$pathinfo_dli . $k . $pathinfo_dli . $v;
            } else {
                $url.="&" . $k . "=" . $v;
            }
        }
        $url = trim($url, $pathinfo_dli);
        $url = trim($url, "&");
        $url = empty($url) ? "" : $pathinfo_dli . $url;
        if ($url_type == 1) {
            return __METH__ . $url;
        } else {
            return __METH__ . "&" . trim($url, "&");
        }
    }

}/**
 * 
* @ClassName: debug
* @Description: todo(这里用一句话描述这个类的作用)
* @author zhouchao
* @date 2014-12-29 下午5:48:06
 */
final class debug {
    static $info = array();
    static $runtime;
    static $memory;
    static $memory_peak;
    static $sqlCount;
    static $SqlExeArr; 

    static public function start($start) {
        self::$runtime [$start] = microtime(true);
        if (function_exists("memory_get_usage")) {
            self::$memory [$start] = memory_get_usage();
        }
        if (function_exists("memory_get_peak_usage")) {
            self::$memory_peak [$start] = false;
        }
    }
    static public function runtime($start, $end = '', $decimals = 4) {
        if (!isset(self::$runtime [$start])) {
            throw new exceptionLC(L("_nohavedebugstart") . $start);
        }
        if (empty(self::$runtime [$end])) {
            self::$runtime [$end] = microtime(true);
            return number_format(self::$runtime [$end] - self::$runtime [$start], $decimals);
        }
    }
    static public function memory_perk($start, $end = '') {
        if (!isset(self::$memory_peak [$start]))
            return mt_rand(200000, 1000000);
        if (!empty($end))
            self::$memory_peak [$end] = memory_get_peak_usage();
        return max(self::$memory_peak [$start], self::$memory_peak [$end]);
    }

    
    static public function show($start, $end) {
        if (!C("DEBUG"))
            return;
        $load_file_list = load_file();
        $serverInfo = empty($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SIGNATURE'] : $_SERVER['SERVER_SOFTWARE'];
        $system = "<div class='debug_server'>";
        $system.="<strong>" . L("debug_show1") . "</strong>: " . $serverInfo . "<br/>";
        $system.="<strong>" . L("debug_show2") . "</strong>: " . $_SERVER['HTTP_USER_AGENT'] . "<br/>";
        $system.="<strong>" . L("debug_show3") . "</strong>: " . phpversion() . "<br/>";
        $system.="<strong>" . L("debug_show4") . "</strong>: " . $_SERVER['HTTP_HOST'] . "<br/>";
        $system.="<strong>" . L("debug_show5") . "</strong>: " . $_SERVER['REQUEST_METHOD'] . "<br/>";
        $system.="<strong>" . L("debug_show6") . "</strong>: " . $_SERVER['SERVER_PROTOCOL'] . "<br/>";
        if (defined("PATH_CONTROL")) {
            $system.="<strong>" . L("debug_show7") . "</strong>: " . PATH_CONTROL . '/' . CONTROL . C("CONTROL_FIX") . ".php<br/>";
        }
        $system.="<strong>" . L("debug_show8") . "</strong>: " . session_id() . "<br/>";
        $system.="</div>";
        $e ['system'] = $system;
        $compileFiles = tplCompile();
        if (!empty($compileFiles)) {
            $tplCompileFiles = '<table width=100%>
            <thead><tr>
            <td style="font-size:13px;width:80px;padding:5px;">' . L("debug_show16") . '</td>
            <td style="font-size:13px;padding:5px;">' . L("debug_show18") . '</td>
            </tr></thead>';
            foreach ($compileFiles as $k => $v) {
                $tplCompileFiles.= '<tr><td style="font-size:12px;width:80px;padding:6px;">' . $v[0] . ' </td>
                    <td style="font-size:12px;padding:6px;">' . str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $v[1]) . "</td></tr>";
            }
            $tplCompileFiles.="</table>";
        }

        
        if (self::$sqlCount > 0) {
            $e['sqlCount'] = self::$sqlCount;
            $e['sqlExeArr'] = self::$SqlExeArr;
            $sqlExeArr = '<table width=100%>
            <thead><tr>
            <td>' . L("debug_show10") . '</td>
            <td>' . L("debug_show11") . '</td>
            </tr></thead>';
            foreach ($e['sqlExeArr'] as $k => $v) {
                $sqlExeArr.= "<tr><td width='35'>[" . $k . "] </td><td>" . htmlspecialchars($v) . "</td></tr>";
            }
            $sqlExeArr.="</table>";
            $sqlExeArr.="<p>" . L("debug_show12") . $e ['sqlCount'] . L("debug_show13") .
                    "</p>";
        }

        
        $loadfile = '<table width=100%>
            <thead><tr>
            <td width="30">ID</td>
            <td>File</td>
            <td width="45">Time</td>
            <td width="60">Memory</td>
            </tr></thead>';

        $message = L("debug_show14") . ": " . self::runtime($start, $end) .
                "&nbsp;&nbsp;" . L("debug_show15") .
                number_format(self::memory_perk($start, $end) / pow(1024, 1), 0) . 'kb';
        $i = 1;
        foreach ($load_file_list as $k => $v) {
            $loadfile .= "<tr><td>[" . $i++ . "] </td><td>" . $v['path'] .
                    "</td><td>" . $v['time'] . "</td><td>" . $v['memory'] . "</td></tr>";
        }
        $loadfile.="</table>";
        $e ['loadfile'] = $loadfile . "<p>$message</p>";
        include C("DEBUG_TPL");
    }

}/**
 * 
* @ClassName: exceptionLC
* @Description: todo(异常处理类)
* @author zhouchao
* @date 2014-12-29 下午5:48:22
 */
final class exceptionLC extends Exception {

    function __construct($message, $code = 1) {
        parent::__construct($message, $code);
    }

    
    static public function exception(exceptionLC $e) {
        $e->show();
    }
    static public function getException() {
        $trace = parent::getTrace();
        $exception ['message'] = "<b>[" . L("exceptionlc_getexception1") . "] </b>" . parent::message . "<br/>";
        $exception['message'].="\t<b>[" . L("exceptionlc_getexception2") . "] </b>" . parent::getFile() . "<br/>";
        $exception['message'].="\t<b>[" . L("exceptionlc_getexception3") . "] </b>" . parent::getLine() . "<br/>";
        $info = '';
        foreach ($trace as $k => $v) {
            if (empty($v['file']))
                continue;
            $info[$k]['file'] = $v ['file'];
            $info[$k]['class'] = empty($v['class']) ? '' : $v['class'];
            $info[$k]['function'] = empty($v['function']) ? '' : $v['function'];
        }

        $exception['info'] = $info;
        return $exception;
    }

    static private function show() {
        $exception = self::getException();
        log::write(strip_tags($exception['message']));
        $exceptionTpl = PATH_LC . '/libs/boot/debug/tpl/exception.html';
        if (!C("DEBUG")) {
            $e['message'] = C("ERROR_MESSAGE") . "\t\t <span style='color:#666; font-weight:normal;'>" . L("exceptionlc_show") . "</span>";
            include $exceptionTpl;
            exit;
        }
        $e['message'] = $exception['message'];
        $loadfile = '<table width=100%>
            <thead><tr>
            <td>Index</td>
            <td>File</td>
            <td>Class</td>
            <td>Function</td>
            </tr></thead>';
        $info = array_reverse($exception['info']);
        foreach ($info as $k => $v) {
            $loadfile.="<tr><td>" . $k . "</td><td>" . $v['file'] . "</td><td>" . $v['class'] . "</td><td>" . $v['function'] . "</td></tr>";
        }
        $e['loadfile'] = $loadfile . "</table>";

        include $exceptionTpl;
    }

    static public function error($errno, $errstr, $errfile, $errline) {
        switch ($errno) {
            case E_ERROR :
            case E_USER_ERROR :
                $errormsg = "[" . L("exceptionlc_error1") . "]{$errstr}\t[" . L("exceptionlc_error2") . "]$errfile\t[" . L("exceptionlc_error3") . "]$errline";
                log::write($errormsg);
                error($errormsg);
                break;
            case E_USER_WARNING :
            case E_USER_NOTICE :
            default :
                $errormsg = "[" . L("exceptionlc_error4") . "] {$errstr}\t[" . L("exceptionlc_error5") . "]{$errfile}\t[" . L("exceptionlc_error6") . "]$errline";
                self::notice(func_get_args());
                log::set($errormsg);
        }
    }

    static private function notice($e) {
        if (!C("DEBUG")) {
            return;
        }
        if (C("SHOW_NOTICE")) {
            $time = number_format(microtime(true) - debug::$runtime ['app_start'], 5);
            $memory = function_exists("memory_get_usage") ? get_size(memory_get_usage()) : '';
            $message = $e [1];
            $file = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $e[2]);
            $line = $e [3];
            $message = "
<div style='width:800px;margin:0px 0px 20px 20px;border:solid 1px #dcdcdc; background:#fff;'>
<h1 style='color:#000;font-size:14px; border-bottom:solid 1px #dcdcdc;
line-height:1.5em;padding:5px 20px  '><span style='display:block;'>" . L("exceptionlc_notice") . ": $message</span></h1>
	<table style='border:solid 1px #dcdcdc;width:780px;color:#4F5155; font-size:13px;background-color:#F9F9F9;margin:10px; '>
                    <tr><td style='padding:6px 10px;'>Filename: " . $file . "</td><tr>
                    <tr><td style='padding:6px 10px;'>Line: $line</td><tr>
                    <tr><td style='padding:6px 10px;'>time: $time</td><tr>
                    <tr><td style='padding:6px 10px;'>Memory: $memory</td><tr>
	</table>
</div>";
            echo $message;
        }
    }

}/**
 * 
* @ClassName: log
* @Description: todo(这里用一句话描述这个类的作用)
* @author zhouchao
* @date 2014-12-29 下午5:50:12
 */
class log {

    static $log = array();
    static public function set($message, $log_type = 'notice') {
        if (!C("LOG_START"))
            return;
        if (in_array($log_type, C("LOG_TYPE"))) {
            $date = date("y-m-d h:i:s");
            self::$log [] = $message . "\t[时间]" . $date . "\r\n";
        }
    }
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

}/**
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
    function __get($var) {
        return isset($this->$var) ? $this->$var : NULL;
    }
    function __set($var, $value) {
        if (property_exists($this, $var)) {
            $this->$var = $value;
        }
    }
    function __call($method, $args) {
        if (method_exists($this, $method)) {
            $this->$method($args);
        }
    }

}/**
 * 
* @ClassName: Control
* @Description: todo(控制器类)
* @author zhouchao
* @date 2014-12-29 下午5:44:40
 */
class Control extends lcphp {

    protected $view = null;
    private $rbac;
    protected $error;
    public $obj_html = null;

    function __init() {

        $data = '----请求----'.__URL__.' time='.date("Y-m-d H:i:s").' json='.json_encode($_POST)." \r\n ";


        file_put_contents(PATH_TEMP.'/log/json_'.date("Y-m-d-H").'.log',$data,FILE_APPEND);
        if (method_exists($this, "__auto")) {
            $this->__auto();
        }

        if(empty($_SESSION)){

            if(!empty($_POST['sessionid'])){

                $_SESSION = AuthToken::decode($_POST['sessionid']);

            }
        }
    }

    public function __call($method, $args) {
        if (substr($method, -5) == "Model") {
            if (strstr($method, '_')) {
                $method = str_replace("_", "/", substr($method, 0, -5));
                return $this->kmodel($method);
            } else {
                return $this->kmodel(substr($method, 0, -5));
            }
        }
    }

    
    public function model($tableName = null, $full = null) {
        return M($tableName, $full);
    }

    
    public function kmodel($model) {
        return K($model);
    }

    
    protected function getViewObj() {
        if (is_null($this->view)) {
            $this->view = viewFactory::factory();
        }
    }

    
    public function display($tplFile = METHOD, $cacheTime = null, $contentType = "text/html", $charset = "", $show = true) {
    	$this->getViewObj();
        return $this->view->display($tplFile, $cacheTime, $contentType, $charset, $show);
    }

    public function get_html_obj() {
        $args = func_get_args();
        if (is_null($this->html)) {
            $this->obj_html = new html();
        }
    }

    
    public function is_cache($time = 3600) {
        $this->getViewObj();
        return $this->view->is_cache($time);
    }

    /**
     *  生成表态文件
     * @param type $control     执行的控制器（模块）
     * @param type $method     方法名称
     * @param type $data        数据，用于组合到$_GET中，
     *                                      其中必须存在html_file元素即html文件名如array('id'=>1,'html_file'=>'h/b/1.html')
     *                                      批量生成时请传入二维数组如array(array('id'=>1,'html_file'=>'h/1.html'),array('id'=>2,'html_file'=>'/h/2.html'))
     */
    public function html_create($control, $method, $data) {
        $this->get_html_obj();
        $this->obj_html->create($control, $method, $data);
    }

    
    public function html_del($name) {
        $this->get_html_obj();
        $this->obj_html->del($name);
    }

    
    public function assign($name, $value) {
        $this->getViewObj();
        $this->view->assign($name, $value);
    }

    
    public function error($msg = "", $url = "", $time = 2) {
        $msg = $msg ? $msg : L("control_error_msg");
        $time = is_numeric($time) ? $time : 3;
        $this->assign("msg", $msg);
        if ($url == "") {
            $url = "window.history.back(-1);";
        } else {
            $url = "window.location.href='" . getUrl($url) . "'";
        }

        $tplFile = C("ERROR_TPL") ? C("ERROR_TPL") : 'error' . C("TPL_FIX");
        $style = C('TPL_STYLE') ? '/' . C('TPL_STYLE') . '/public/' : '/';
        $tpl_dir = C("TPL_DIR");
        $tpl_dir = strstr($tpl_dir, '/') ? $tpl_dir . $style : PATH_APP . '/' . $tpl_dir . $style . 'public/';
        $tpl = $tpl_dir . $tplFile;
        $this->assign("url", $url);
        $this->assign("time", $time);
        $this->display($tpl);
        exit;
    }
    public function success($msg = "", $url = "", $time = 2) {
        $msg = $msg ? $msg : L("control_success_msg");
        $time = is_numeric($time) ? $time : 3;
        $this->assign("msg", $msg);
        if ($url == "") {
            $url = "window.history.back(-1);";
        } else {
            $url = "window.location.href='" . getUrl($url) . "'";
        }
        $tplFile = C("SUCCESS_TPL") ? C("SUCCESS_TPL") : 'success' . C("TPL_FIX");
        $style = C('TPL_STYLE') ? '/' . C('TPL_STYLE') . '/public/' : '/';
        $tpl_dir = C("TPL_DIR");
        $tpl_dir = strstr($tpl_dir, '/') ? $tpl_dir . $style : PATH_APP . '/' . $tpl_dir . $style . 'public/';
        $tpl = $tpl_dir . $tplFile;
        $this->assign("url", $url);
        $this->assign("time", $time);
        $this->display($tpl);
        exit;
    }


    
    public function go($url) {
        go($url);
    }

    
    public function xml_create($data, $root = null, $encoding = "UTF-8") {
        return xml::xml_create($data, $root = null, $encoding = "UTF-8");
    }

    
    public function xml_to_array($xml) {
        return xml::xml_to_array($xml);
    }

    
    static function session_start() {
        session::start();
    }

    
    function session_set($name, $value) {
        $args = func_get_args();
        return call_user_func_array(array("session", "set"), $args);
    }

    
    function session_get($name) {
        $args = func_get_args();
        return call_user_func_array(array("session", "get"), $args);
    }

    
    function session_get_session_name() {
        $args = func_get_args();
        return call_user_func_array(array("session", "get_session_name"), $args);
    }

    
    function session_get_session_id() {
        $args = func_get_args();
        return call_user_func_array(array("session", "get_session_id"), $args);
    }

    
    function session_del($name) {
        $args = func_get_args();
        return call_user_func_array(array("session", "del"), $args);
    }

    
    function session_delall() {
        $args = func_get_args();
        return call_user_func_array(array("session", "delall"), $args);
    }

    
    function session_destroy() {
        $args = func_get_args();
        return call_user_func_array(array("session", "destroy"), $args);
    }

    
    function session_is_set($name) {
        $args = func_get_args();
        return call_user_func_array(array("session", "is_set"), $args);
    }

    
    function cache_set($name, $data, $time = null, $path = null) {
        $cacheObj = cacheFactory::factory();
        return $cacheObj->set($name, $data, $time, $path);
    }

    
    function cache_get($name, $path = null) {
        $cacheObj = cacheFactory::factory();
        return $cacheObj->get($name, $path);
    }

    
    function cache_exists($name, $time = null, $path = null) {
        $cacheObj = cacheFactory::factory();
        return $cacheObj->is_cache($name, $time, $path);
    }

    
    function cache_del($name, $path = null) {
        $cacheObj = cacheFactory::factory();
        return $cacheObj->del($name, $path);
    }

    
    function cache_delAll() {
        $cacheObj = cacheFactory::factory();
        return $cacheObj->delAll();
    }

    function checkParam($paramName,$required=true,$default=NULL){

        if (empty($paramName)) {
            return false;
        }
        $paramValue = trim($_REQUEST[$paramName]);
        if (empty($paramValue) && $required) {
            return false;
        }
        return $paramValue ? $paramValue:$default;
    }

} ?>