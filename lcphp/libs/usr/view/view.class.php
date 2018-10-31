<?php

/**
 * 
* @ClassName: view
* @Description: todo(视图基类)
* @author zhouchao
* @date 2014-12-30 上午8:54:19
 */
abstract class view extends lcphp {

    /**
     * 获得模版文件
     */
    protected function getTemplateFile($tplFile) {
        $tplFix = C("TPL_FIX") ? '.' . trim(C("TPL_FIX"), '.') : '';
        $tplFile = str_replace(C("PATHINFO_HTML"), '', $tplFile);
        $tplFile = empty($tplFile) ? METHOD . $tplFix : $tplFile . $tplFix; //模板文件
        if (strstr(C("TPL_DIR"), '/') && !strstr($tplFile, '/')) {//配置文件tpl_dir包含路径 并且文件中不包含路径
            $tplFile = PATH_TPL . '/' . $tplFile;
        } else {//模板文件中包含/
            $fileArr = explode("/", $tplFile);
            $file = array_pop($fileArr); //模版文件
            switch (count($fileArr)) {
                case 0:
                    $tplFile = PATH_TPL . '/' . CONTROL . '/' . $tplFile;
                    break;
                case 1:
                    $tplFile = PATH_TPL . '/' . $fileArr[0] . '/' . $file;
                    break;
                case 2:
                    $tplFile = str_replace(APP, $fileArr[0], PATH_TPL) . '/' . $fileArr[1] . '/' . $file;
                    break;
            }
        }
        if (!is_file($tplFile)) {
            error(L("view_getTemplateFile_error3") . $tplFile, false); //模版文件不存在
        }
        return $tplFile;
    }

}

?>