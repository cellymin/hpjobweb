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

include __DIR__.'/lcphp/lcphp.php';
$_SERVER = [
    "USER"=>"nginx",
    "HOME"=>"/var/cache/nginx",
    "FCGI_ROLE"=>"RESPONDER",
    "SCRIPT_FILENAME"=>"/var/www/html/hpjobweb/index.php",
    "QUERY_STRING"=>"",
    "REQUEST_METHOD"=>"GET",
    "CONTENT_TYPE"=>"",
    "CONTENT_LENGTH"=>"",
    "SCRIPT_NAME"=>"/index.php",
    "REQUEST_URI"=>"/index.php/app/index/run",
    "DOCUMENT_URI"=>"/index.php",
    "DOCUMENT_ROOT"=>"/var/www/html/hpjobweb",
    "SERVER_PROTOCOL"=>"HTTP/1.1",
    "REQUEST_SCHEME"=>"http",
    "GATEWAY_INTERFACE"=>"CGI/1.1",
    "SERVER_SOFTWARE"=>"nginx/1.10.3",
    "REMOTE_ADDR"=>"101.81.143.7",
    "REMOTE_PORT"=>"13719",
    "SERVER_ADDR"=>"120.55.165.117",
    "SERVER_PORT"=>"80",
    "SERVER_NAME"=>"",
    "REDIRECT_STATUS"=>"200",
    "HTTP_HOST"=>"120.55.165.117",
    "HTTP_USER_AGENT"=>"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:52.0) Gecko/20100101 Firefox/52.0",
    "HTTP_ACCEPT"=>"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    "HTTP_ACCEPT_LANGUAGE"=>"zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3",
    "HTTP_ACCEPT_ENCODING"=>"gzip, deflate",
    "HTTP_COOKIE"=>"sessionid=rdfo2o5kv2m9qufm6pmu4n2na7; hdsid=c15sujcolvkbnhqjk00r6g5ol5",
    "HTTP_CONNECTION"=>"keep-alive",
    "HTTP_UPGRADE_INSECURE_REQUESTS"=>"1",
    "HTTP_CACHE_CONTROL"=>"max-age=0",
    "PHP_SELF"=>"/index.php",
    "REQUEST_TIME_FLOAT"=>1492417365.1608,
    "REQUEST_TIME"=>1492417365
];
try{
    LC::run();
}catch(Exception $e){

    var_dump($e);

}

?>