<?php

class weixin {

    public $code;

    /**
     * @Title: createOauthUrlForCode
     * @Description: todo(生成可以获得code的url)
     * @author zhouchao
     * @param $redirectUrl
     * @return  string  返回类型
     */
    public function createOauthUrlForCode($redirectUrl,$scope='snsapi_base'){
        $urlObj["appid"] = weiXinConfig::APPID;
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = $scope;
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->formatBizQueryParaMap($urlObj, false);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }


    /**
     * @Title: getOpenid
     * @Description: todo(过curl向微信提交code，以获取openid)
     * @author zhouchao
     * @return  mixed  返回类型
     */
    public function getOpenid(){

        $url = $this->createOauthUrlForOpenid();
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res,true);

        //{
        //   "access_token":"ACCESS_TOKEN",
        //   "expires_in":7200,
        //   "refresh_token":"REFRESH_TOKEN",
        //   "openid":"OPENID",
        //   "scope":"SCOPE"
        //}
        return $data;

    }

    /**
     * @Title: getUserInfo
     * @Description: todo(通过access_token和openid拉取用户信息)
     * @author zhouchao
     * @param $access_token
     * @param $openid
     * @return  mixed  返回类型
     */
    public function getUserInfo($access_token,$openid){

        $url = $this->createOauthUrlFornSapiUserInfo($access_token,$openid);


        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以json形式返回
        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res,true);

//        {
//            "openid":" OPENID",
//            " nickname": NICKNAME,
//          "sex":"1",
//          "province":"PROVINCE"
//          "city":"CITY",
//          "country":"COUNTRY",
//          "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",
//	        "privilege":[
//              "PRIVILEGE1"
//	            "PRIVILEGE2"
//          ],
//         "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
//      }
        return $data;
    }


    /**
     * @Title: formatBizQueryParaMap
     * @Description: todo(格式化参数)
     * @author zhouchao
     * @param $paraMap
     * @param $urlencode
     * @return  string  返回类型
     */
    private function formatBizQueryParaMap($paraMap, $urlencode){

        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';

        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     * @Title: createOauthUrlForOpenid
     * @Description: todo(生成可以获得openid的url)
     * @author zhouchao
     * @return  string  返回类型
     */
    private function createOauthUrlForOpenid(){
        $urlObj["appid"] = weiXinConfig::APPID;;
        $urlObj["secret"] = weiXinConfig::APPSECRET;
        $urlObj["code"] = $this->code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->formatBizQueryParaMap($urlObj, false);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }

    /**
     * @Title: createOauthUrlFornSapiUserInfo
     * @Description: todo(生成可以获得用户信息的url)
     * @author zhouchao
     * @param $access_token
     * @param $openid
     * @return  string  返回类型
     */
    private function createOauthUrlFornSapiUserInfo($access_token,$openid){
        $urlObj["access_token"] = $access_token;
        $urlObj["openid"] = $openid;
        $urlObj["lang"] = 'zh_CN';
        $bizString = $this->formatBizQueryParaMap($urlObj, false);
        return "https://api.weixin.qq.com/sns/userinfo?".$bizString;
    }



}