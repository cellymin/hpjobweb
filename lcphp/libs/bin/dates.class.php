<?php

final class dates {
    private $runtime = array(); //运行时间 
    /**
     * 运行时间 
     * @param type $start       开始标记
     * @param type $end         结束标记
     * @param type $decimals   运行时间的小数位数
     * @return type 
     */

    static function runtime($start, $end = '', $decimals = 3) {
        if ($end != '') {
            self::$runtime [$end] = microtime();
            return number_format(self::$runtime [$end] - self::$runtime [$start], $decimals);
        }
        $runtime [$start] = microtime();
    }

}

?>
