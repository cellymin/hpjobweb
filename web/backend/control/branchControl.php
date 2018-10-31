<?php

/*
 * Describe   : 广告管理
 */
class branchControl extends myControl { 
   
    function branchList() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            include_once('/var/www/html/hpjobweb/web/backend/libs/phpqrcode.php');
            $object = new QRcode();  
            //var_dump($_POST);die;
            $name = $_POST['name'];
            $address = $_POST['address'];
            $contacter = $_POST['contacter'];
            $phonenumber = $_POST['phonenumber'];
            
            $db = M('branch');
            $arr = array(
                'name'=>$name,
                'address' => $address,
                'contacter' => $contacter,
                'phonenumber' => $phonenumber,
                'addtime' => date('Y-m-d H:i:s')
            );
            $goods = $db->add($arr);

            //生成二维码
            $url='http://www.hap-job.com/index.php/app/auth/share/from/35920?from=singlemessage&isappinstalled=1&branch_id='.$goods;
            $level=3;  
            $size=4;  
            $path = "./uploads/qrcode/";//创建路径
            $fileName = $path.$goods.'.png';  
            $errorCorrectionLevel =intval($level);//容错级别  
            $matrixPointSize = intval($size);//生成图片大小  
            $object->png($url, $fileName, $errorCorrectionLevel, $matrixPointSize, 2);   


            $list = $db->order("id desc")->limit(1)->find();
            $data['phpqrcode'] = $fileName;
            $result = $db->where(array('id'=>$list['id']))->update($data);
            if($result){
                $this->success('添加成功');
            }
        }


        $db = M('branch');
        $count = $db->count();
        $page = new page($count,20);
        $ads = $db->findall($page->limit());
        $this->assign('ads',$ads);
        $this->assign('pages',$page->show());
        $this->assign('ads', $ads);
        // $this->assign('ads_type', $this->ads_type);
        $this->display('ads');
    }




    function editAds() {  
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $address = $_POST['address'];
            $contacter = $_POST['contacter'];
            $phonenumber = $_POST['phonenumber'];
            
            $db = M('branch');
            $arr = array(
                'name'=>$name,
                'address' => $address,
                'contacter' => $contacter,
                'phonenumber' => $phonenumber
            );
            $ads = $db->where('id=' . $_POST['id'])->update($arr);
            if($ads){
                $this->success('修改成功');
            }
        }
        $id = $_GET['id'];
        $goods = M('branch')->where(array('id'=>$id))->find();
        $this->assign('goods',$goods);
        $this->display();
    }



    function del(){
        $id = $_GET['id'];
        $db = M('branch');
        $result = $db->where('id=' . $id)->del();
        if($result){
            $this->success('删除成功');
        }
    }
    



}

