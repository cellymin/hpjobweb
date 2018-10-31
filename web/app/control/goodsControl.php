<?php
class goodsControl extends Control {
    private $auth;
    private $resume;

    function __construct()
    {
        parent::__construct();
        $this->resume = K('resume');
        $this->auth = new auth;
    }

    /**
     * @Title:
     * @Description: todo(抢购列表)
     * @author zhouchao
     * @return  void  返回类型
     */
    public function moneyGoods(){

        $goods = M('goods')->where('type=0')->order('start_time desc')->findall();

        $this->assign('goods',$goods);

        $this->display('app/money_goods');


    }

    /**
     * @Title: moneyGoodsInfo
     * @Description: todo(抢购详细)
     * @author zhouchao
     * @return  void  返回类型
     */
    public function moneyGoodsInfo(){

        $gid = intval($_GET['id']);

        $goods = M('goods')->where(array('gid'=>$gid))->find();

        $this->assign('goods',$goods);

        $this->display('app/money_goods_info');

    }

    /**
     * @Title:
     * @Description: todo(兑换列表)
     * @author zhouchao
     * @return  void  返回类型
     */
    public function integralGoods(){

        $goods = M('goods')->where('type=1')->order('amount desc,start_time desc')->findall();

        $this->assign('goods',$goods);

        $this->display('app/integral_goods');

    }

    /**
     * @Title: moneyGoodsInfo
     * @Description: todo(兑换详细)
     * @author zhouchao
     * @return  void  返回类型
     */
    public function integralGoodsInfo(){

        $gid = intval($_GET['id']);

        $goods = M('goods')->order('amount desc')->where(array('gid'=>$gid))->find();

        $this->assign('goods',$goods);

        $this->display('app/integral_goods_info');

    }


    /**
     * @Title: snapUp
     * @Description: todo(限时抢购)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function snapUp(){

        $uid = $_POST['uid'];
        $result = M('user')->where('uid=' . $uid)->find();
        $client_id = $result['client_id'];
        $username = $result['username'];
        $gid = $_POST['gid'];

        if(!empty($result['authid_qq']) && $result['is_bind']==2){//未绑定手机号码
            $info = array(
                'status'=>20,
                'msg'=>'请先绑定手机号码',
                'data'=>array()
            );
            echo json_encode($info);
            die;
        }

        if(M('order_info')->where(array('uid'=>$uid,'gid'=>$gid))->find()){
            Json_error('您已抢购过了哟');
        }
        $user = M('user')->where('uid=' . $uid)->find();
        $mobile = $user['username'];
        $goods = M('goods')->where('gid=' . $gid . ' AND amount > 0 AND type=0')->find();

        if($goods['status']==0){

            Json_error('抢购未开始');
        }
        if(empty($goods)){
            Json_error('库存不足');
        }
        $message = $goods['title'];
        $price = $goods['price'];
        if((M('goods')->dec('amount','gid='.$gid,1))&&
            M('order_info')->add(array('uid'=>$uid,'gid'=>$gid,'username'=>$username,'type'=>0,'title'=>$message,'price'=>$price,'created'=>time()))){

            addNewMessages($uid,$content='您已成功抢到价值'.$price.'元的'.$message.'，请您于3个工作日后到开心工作线下体验店进行购买，购买时请出示本条系统消息及有效证件，有效期为两周，地址：无锡市新区新光路307-5(红旗花园站旁)，如有疑问请联系客服4006920099！',$title='限时抢购',1,101,$gid,$is_mess=0,$mobile);
            $hidden = array(
                'type'=>1,
                'data_type'=>101,
                'title'=>'限时抢购',
                'content'=>'您已成功抢到价值'.$price.'元的'.$message.'，请您于3个工作日后到开心工作线下体验店进行购买，购买时请出示本条系统消息及有效证件，有效期为两周，地址：无锡市新区新光路307-5(红旗花园站旁)，如有疑问请联系客服4006920099！'
            );

            if(queryExistMissionLog($uid,'3,12')){
                addMissionLog($uid,3,$gid);
            }else{
                addMissionLog($uid,12,$gid);
            }

            push(array($client_id),array('hidden'=>$hidden,'title'=>'title','content'=>'content'));
            Json_success('您已成功抢到价值'.$price.'元的'.$message.'，请您于3个工作日后到开心工作线下体验店进行购买，购买时请出示本条系统消息及有效证件，有效期为两周，地址：无锡市新区新光路307-5(红旗花园站旁)，如有疑问请联系客服4006920099！');
        }else{
            Json_error('网络繁忙');
        }
    }

    /**
     * @Title: integralExchange
     * @Description: todo(积分兑换)
     * @author nipeiquan
     * @return  void  返回类型
     */
    public function integralExchange(){
        $uid = $_POST['uid'];
        $result = M('user')->where('uid=' . $uid)->find();
        $username = $result['username'];
        $gid = $_POST['gid'];
        $user = M('user')->where('uid=' . $uid)->find();

        if(!empty($user['authid_qq']) && $user['is_bind']==2){//未绑定手机号码
            $info = array(
                'status'=>20,
                'msg'=>'请先绑定手机号码',
                'data'=>array()
            );
            echo json_encode($info);
            die;
        }
        $client_id = $user['client_id'];
        $mobile = $user['username'];
        if(empty($uid)){
            Json_error('请先登录');
        }else{
            $user = M('user_point')->where('uid=' . $uid)->find();
            $point = $user['point'];
            $goods = M('goods')->where('gid=' . $gid . ' AND amount > 0 AND type=1')->find();
            if(empty($goods)){
                Json_error('库存不足');
            }
            $price = $goods['price'];
            $message = $goods['title'];
            if($point < $price){
                Json_error('积分不足，无法兑换');
            }else{
                $point = $point-$price;
                if(M('user_point')->where('uid=' . $uid)->update(array('point'=>$point))&&
                    M('goods')->dec('amount','gid='.$gid,1)&&
                    M('order_info')->add(array('uid'=>$uid,'gid'=>$gid,'username'=>$username,'type'=>1,'title'=>$message,'price'=>$price,'created'=>time())))
                {
                    addNewMessages($uid,$content='您已成功使用'.$price.'积分兑换'.$message.'，请您于3个工作日后到开心工作线下体验店领取，领取时请出示本条系统消息及有效证件，有效期为两周，地址：无锡市新区新光路307-5(红旗花园站旁)，如有疑问请联系客服4006920099！',$title='积分兑换',1,101,$gid,$is_mess=0,$mobile);
                    $data = array(
                        'uid' => $uid,
                        'content' => '兑换商品',
                        'point' => '-' . $price,
                        'created' => time(),
                        'ip' => ip_get_client(),//操作ip
                        'username' => $username,
                        'time' => time(),
                        'type' => 0
                    );
                    M('opt_log')->insert($data);
                    $hidden = array(
                        'type'=>1,
                        'data_type'=>101,
                        'title'=>'积分兑换',
                        'content'=>'您已成功使用'.$price.'积分兑换'.$message.'，请您于3个工作日后到开心工作线下体验店领取，领取时请出示本条系统消息及有效证件，有效期为两周，地址：无锡市新区新光路307-5(红旗花园站旁)，如有疑问请联系客服4006920099！'
                    );

                    push(array($client_id),array('hidden'=>$hidden));
                    Json_success('您已成功使用'.$price.'积分兑换'.$message.'，请您于3个工作日后到开心工作线下体验店领取，领取时请出示本条系统消息及有效证件，有效期为两周，地址：无锡市新区新光路307-5(红旗花园站旁)，如有疑问请联系客服4006920099！');
                }else{
                    Json_error('兑换失败');
                }
            }
        }
    }

}