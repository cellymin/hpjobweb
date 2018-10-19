<?php
 /*
  * Description:内容中心
  * Email zhangbo1248@gmail.com
  * Copyright (C) 2012 zhangbo
  */

class dynamicControl extends control
{

	public function __construct()
	{
		$path = $_SERVER['REQUEST_URI'];//得到图片路径

		$outfile = str_replace('/uploads/dynamic/','',$path);//得到图片名称

		$pattern = '/-(\d+)/';

		$original_path = preg_replace($pattern,'',$path);//得到原始图片

		preg_match_all($pattern,$path, $matches);//得到尺寸

		$matches = $matches[1];

		$wh = array(  //尺寸限制
			array(320,480),
			array(120,120),
			array(220,60),
		);

		if(in_array($matches,$wh)){

			$img = new image ();

			$outfile_path = $img->thumb(PATH_ROOT.$original_path, $outfile, '', $matches[0], $matches[1], 1);

			header('Content-Type:image/png');

			echo file_get_contents($outfile_path);


		}else{
			header('HTTP/1.1 404 Not Found');
		}

		die;
	}

	public function index(){
		echo 1;
	}

	public function img(){

		var_dump($_GET);

		var_dump($_SERVER);
	}
}