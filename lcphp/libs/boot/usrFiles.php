<?php

/**
 *
 * @ClassName: usrFiles
 * @Description: todo(usr系统类包)
 * @author zhouchao
 * @date 2014-12-29 下午5:48:22
 */
if (!defined("PATH_LC"))
    exit;
return array(
    "USR_FILES" => array(
        "baseTag" => PATH_LC . '/libs/usr/view/base/baseTag.class.php', //核心标签
        "baseView" => PATH_LC . '/libs/usr/view/base/baseView.class.php', //后盾视图类
        "smartyView" => PATH_LC . '/libs/usr/view/smarty/SmartyView.class.php', //smarty类
        "Model" => PATH_LC . '/libs/usr/model/Model.class.php', //模型类
        "relationModel" => PATH_LC . '/libs/usr/model/driver/relationModel.class.php', //关联模型
        "viewModel" => PATH_LC . '/libs/usr/model/driver/viewModel.class.php', //视图模型
        "dbFactory" => PATH_LC . '/libs/usr/db/dbFactory.class.php', //数据库工厂，指派驱动
        "dbInterface" => PATH_LC . '/libs/usr/db/dbInterface.class.php', //数据库接口
        "cacheFactory" => PATH_LC . '/libs/usr/cache/cacheFactory.class.php', //缓存工厂类
        "cacheInterface" => PATH_LC . '/libs/usr/cache/cacheInterface.class.php', //缓存工厂类
        "sessionFactory" => PATH_LC . '/libs/usr/session/sessionFactory.class.php', //数据库工厂，指派驱动
        "viewFactory" => PATH_LC . '/libs/usr/view/viewFactory.class.php', //视图工厂
    )
);
?>
