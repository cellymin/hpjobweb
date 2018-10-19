<?php if(!defined("PATH_LC"))exit;?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>无标题文档</title>
<link href="http://www.hap-job.com/templates/default/app/css/style.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
</head>

<body style="background:#f8f8f8;">
<!--<div id="top_top">-->
    <!--<a onclick="javascript :history.go(-1);"><img src="http://www.hap-job.com/templates/default/app/images/back.png" height="21" width="12"></a>-->
    <!--我的话题-->
<!--</div>-->
<?php if(is_array($goods)):?><?php  foreach($goods as $good){ ?>
<a href="http://www.hap-job.com/index.php/app/goods/integralGoodsInfo/uid/<?php echo $_GET['uid'];?>/id/<?php echo $good['gid'];?>">
   <div class="jifeng">
        <div class="jifeng_tp">
            <img src="<?php echo $good['img'];?>">
        </div>
        <div class="jifeng_wz"><?php echo $good['title'];?></div>
            <p style="padding: 0 2%;">剩余数量<?php echo $good['amount'];?></p>
            <p style="padding: 0 2%; color: #f00">积分：<?php echo intval($good['price']);?></p>
        <div class="clear"></div>
        <div class="time2">
            <div type="submit" <?php if($good['amount']>0){?> class="jifeng_anniu" <?php  }else{ ?> class="feise_anniu" <?php }?>> <?php if($good['amount']>0){?> 立即兑换 <?php  }else{ ?>抢光了 <?php }?></div>
        </div>
        </div>
    </div>
    </div>
</a>
<?php }?><?php endif;?>
</body>
</html>
