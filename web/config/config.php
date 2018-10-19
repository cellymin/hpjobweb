<?php

if (!defined("PATH_LC")){exit('拒绝访问');}
$db_config=include PATH_ROOT.'/config/database.php';
$app_config=include PATH_ROOT.'/config/app.php';
return array_merge($db_config,$app_config);
?>