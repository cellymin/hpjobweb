<?php

class AuthToken{
    /**
     * @Title: encode
     * @Description:todo(将数据加密)
     * @Author: Zhouchao
     * @param $array (加密数据)
     * @param $expire(过期时间)
     * @param string $key(加密秘钥)
     * @return string 返回类型
     */
    public static function encode($array,$expire,$key= 'Common2015'){
        return self::auth_code(json_encode($array,JSON_UNESCAPED_UNICODE),'ENCODE',$key,$expire);
    }

    /**
     * @Title: decode
     * @Description:todo(将字符串解密)
     * @Author: Zhouchao
     * @param $string(字符串)
     * @param string $key(秘钥)
     * @param bool $reArr(是否返回数组)
     * @return mixed 返回类型
     */
    public static function decode($string,$key= 'Common2015',$reArr = true){
        return json_decode(self::auth_code($string,'DECODE',$key),$reArr);
    }

    /**
     * @Title: auth_code
     * @Description:todo(加密算法)
     * @Author: Zhouchao
     * @param $string
     * @param string $operation
     * @param $key
     * @param int $expiry
     * @return string 返回类型
     */
    private static function auth_code($string, $operation = 'DECODE', $key , $expiry = 0) {

        $ckey_length = 4;

        $key = md5( $key ); //将密码md5散列

        $keya = md5(substr($key, 0, 16));//散列后的值前16位再次散列

        $keyb = md5(substr($key, 16, 16));//散列后的值后16位再次散列

        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
        //解密时将返回信息的前4位赋给keyc 如果是加密将当前的时间减去4秒
        $cryptkey = $keya.md5($keya.$keyc);
        //keya与keyc拼接的字符串散列后与keya连接
        $key_length = strlen($cryptkey);
        //加密字符串长度
        $string = $operation == 'DECODE' ? base64_decode(str_replace('=','+',substr($string, $ckey_length))) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        //解密是为字符串前4位，并且将=替换成+ 加密时 前10位过期时间.md5(原本字符串.keyb前16位.原本字符串)
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        //生成255位随机数
        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if($operation == 'DECODE') {
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            }else{
                return '';
            }
        }else{
            $auth =  $keyc.str_replace('+','=',str_replace('=', '', base64_encode($result)));
            return $auth;
        }
    }
}