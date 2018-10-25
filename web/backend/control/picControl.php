<?php

/*
 * Describe   : 分享页面，引导页面图片管理
 */
class picControl extends myControl {

    public $ads_type = array(
    1 => '文字', 2 => '图片'
);
    private $ads_cate;
    private $ads;

    function __construct() {
        parent::__construct();
        $this->ads_cate = M('ads_cate');
        $this->ads = M('ads');
    }

  public function picList(){

        $this->display();
  }
  public function  addPic(){
        $this->display();
  }
}

