<?php
header("Content-type:text/html;charset=utf-8");
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Credentials:true');
define('APP_GROUP', 'web');
define('QRSPEC_VERSION_MAX', 40);
define('QRSPEC_WIDTH_MAX',   177);
define('QRCAP_WIDTH',        0);
define('QRCAP_WORDS',        1);
define('QRCAP_REMINDER',     2);
define('QRCAP_EC',           3);
//禁用错误报告
error_reporting(0);
//报告运行时错误
error_reporting(E_ERROR | E_WARNING | E_PARSE);
//报告所有错误
error_reporting(E_ALL);

function cache_shutdown_error() {

    $_error = error_get_last();

    if ($_error && in_array($_error['type'], array(1, 4, 16, 64, 256, 4096, E_ALL))) {

        echo '<font color=red>你的代码出错了：</font></br>';
        echo '致命错误:' . $_error['message'] . '</br>';
        echo '文件:' . $_error['file'] . '</br>';
        echo '在第' . $_error['line'] . '行</br>';
    }
}

register_shutdown_function("cache_shutdown_error");
define('COMPILE', true);
include './lcphp/lcphp.php';

LC::run();
?>