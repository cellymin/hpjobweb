<?php

/**
 * 
* @ClassName: LC
* @Description: todo(框架运行主文件)
* @author zhouchao
* @date 2014-12-30 上午8:57:21
 */
class LC {

    private static $boot; //核心编译文件boot.php
    public static $compile; //是否编译核心编译文件
    private static $version = "12.9.6 Beta"; //LC框架版本
    private static $is_run = false;

    /**
     * 运行框架
     */
     public static function run() {
        if (self::$is_run) {
            return;
        }
        self::$is_run = true;
        self::$compile = defined("COMPILE") ? COMPILE : true;
        self::Definition(); //配置系统常量
        self::$boot = PATH_TEMP . '/boot.php'; //核心编译文件
        if (self::$compile && is_file(self::$boot)) {
            require self::$boot; //如果编译文件存在加载核心编译文件boot.php
            application::run(); //运行应用目录下的项目
            return;
        }
        self::compileCoreFile(); //编译生成核心文件boot.php
        application::run(); //运行应用目录下的项目
    }

    /**
     * 系统常量定义
     */
    static private function Definition() {
        define("PATH_LC", str_replace("\\", "/", dirname(__FILE__))); //框架目录
        define("PATH_ORG", PATH_LC . '/org'); //扩展插件路径
        $root = rtrim(str_replace(array('/', "\\"), "/", dirname($_SERVER['SCRIPT_FILENAME'])), '/');
        define("PATH_ROOT", $root); //网站物理路径
        define("PATH_TEMP", PATH_ROOT . '/temp'); //运行时的临时目录路径
        define("PATH_LOG", PATH_TEMP . '/log'); //日志文件目录路径
        define("PATH_SESSION", PATH_TEMP . '/session'); //保存SESSION文件的目录路径
    }

    /**
     * 载入编译核心文件
     */
    private static function compileCoreFile() {
        $coreFile = PATH_LC . '/libs/boot/compileFiles.php'; //需要编译的文件
        $files = require $coreFile; //载入需要编译到boot.phps中的文件列表
        foreach ($files as $v) {
            if (is_file($v))
                require $v;
        }
        self::mkdirs(); //创建核心目录
        if (!self::$compile) {
            if (is_file(self::$boot)) {
                unlink(self::$boot);
            }
            return; //是否编译核心文件
        }
        $data = php_merge($files, 1); //合并且删除空格
        $data = "<?php  if(!defined('PATH_LC')){exit;}define('TEMP_DIR_EXISTS',1);" . $data . ' ?>';
        file_put_contents(self::$boot, $data); //写入核心编译文件boot.php
    }

    /**
     * 创建运行目录
     */
    static private function mkdirs() {
        if (!is_dir(PATH_TEMP))
            @mkdir(PATH_TEMP, 0777) || die((PATH_TEMP . L("HD_mkdirs_path_temp"))); //"临时目录创建失败，请修改权限！"
        if (!is_dir(PATH_LOG))
            @mkdir(PATH_LOG, 0777) || die((PATH_LOG . L("HD_mkdirs_path_log"))); //"日志目录创建失败，请修改权限！"
        if (!is_dir(PATH_SESSION))
            @mkdir(PATH_SESSION, 0777) || die((PATH_SESSION . L("HD_mkdirs_path_session"))); //"SESSION目录目录创建失败，请修改权限！"
    }

}

