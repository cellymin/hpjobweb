<?php

/*
 * Copyright  : [后盾人才招聘系统] (C)2011-2012 后盾网 ，Inc. 
 * Link       : http://www.houdunwang.com
 * Encoding   : UTF-8
 * Author     : 张博
 * Email      : zhangbo1248@gmail.com
 * Created    : 2012-7-2
 * Describe   : 
 */
if (!defined("PATH_LC"))
    exit;
return array(
    'TPL_STYLE' => 'default', //如果有多风格模版时，这里添上目录名  那么路径结果就会变成 TPL_PATH/TPL_STYLE形式
    'TPL_DIR' =>PATH_ROOT.'/templates',
    'VERIFY_RECRUIT'=>true,//审核招聘信息
    'DATA_CACHE_DIR'=>PATH_ROOT.'/caches/',
    'DEBUG'=>1
);
