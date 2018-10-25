<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link type="text/css" rel="stylesheet" href="http://localhost//hpjobweb/public/css/jqueryUI.bootstrap/jquery-ui-1.8.16.custom.css"/>
        <link type="text/css" rel="stylesheet" href="http://localhost//hpjobweb/public/css/bootstrap/bootstrap.min.css"/>
        <link type="text/css" rel="stylesheet" href="http://localhost/hpjobweb/web/backend/templates/css/public.css"/>
        <script type="text/javascript" src="http://localhost//hpjobweb/public/js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="http://localhost//hpjobweb/public/js/jqueryValidate/jquery.validate.min.js"></script>
        <script type="text/javascript" src="http://localhost//hpjobweb/public/js/jqueryValidate/jquery.metadata.js"></script>
        <script type="text/javascript" src="http://localhost//hpjobweb/public/js/jquery-ui-1.8.21.custom.min.js"></script>
        <script type="text/javascript" src="http://localhost//hpjobweb/public/js/My97DatePicker/WdatePicker.js"></script>
        <script type="text/javascript" src="http://localhost//hpjobweb/caches/js/linkage_data.js"></script>
        <script type="text/javascript" src="http://localhost//hpjobweb/public/js/linkage/linkage_style_1.js"></script>
    </head>
    <body>
        <div id="jquery-colour-picker" class="hide"></div>
        <div id="tabs">
            <ul>
                <li><a href="#tabs-2">门店列表</a></li>
                <li><a href="#tabs-1">添加门店</a></li>
               
            </ul>
            <div id="tabs-1">
                <div id="add-form">
                    <form action="http://localhost/hpjobweb/index.php/backend/branch/branchList" method="post" validate="true" enctype="multipart/form-data">
                        <table>
                            <tr>
                                <th>门店名称：</th>
                                <td><input type="text" name="name" validate="{required:true}" /></td>
                            </tr>

                            <tr>
                                <th>门店地址：</th>
                                <td><input type="text" name="address" validate="{required:true}" /></td>
                            </tr>


                            <tr>
                                <th>联系人：</th>
                                <td><input type="text" name="contacter" validate="{required:true}" /></td>
                            </tr> 
                           
                           
                            <tr>
                                <th>联系人手机号：</th>
                                <td><input type="text" name="phonenumber" validate="{required:true}" /></td>
                            </tr>
                        </table>
           
                        <script type="text/javascript">
                            $('#cate').change(function(){
                                $('[class^=cate_c_]').hide();
                                $('[class^=cate_c_] input').attr('disabled',true);
                                $('.'+$(this).find(':checked').attr('cate')).show();
                                $('.'+$(this).find(':checked').attr('cate')+' input').attr('disabled',false);
                            });
                        </script>
                        <table>
                            <tr>
                                <th></th>
                                <td><button type="submit" class="btn btn-primary"><i class="icon-plus icon-white"></i> 添加</button></td>
                            </tr>
                        </table>
                    </form>
                </div>
                <style type="text/css">
                    table th{
                        width: 95px;
                        text-align:right;
                    }
                    .cate-title{
                        width: 200px;
                        height: 40px;
                    }
                </style>
            </div>



            <div id="tabs-2">
                <table class="table">
                    <tr>
                        <th>门店ID</th>
                        <th>门店名称</th>
                        <th>门店地址</th>
                        <th>联系人</th>
                        <th>联系人手机号</th>
                        <th>添加时间</th>
                        <th>操作</th>
                    </tr>
                    <?php if(is_array($ads)):?><?php  foreach($ads as $value){ ?>
                    <tr>
                        <td><?php echo $value['id'];?></td>
                        <td><?php echo $value['name'];?></td>
                        <td><?php echo $value['address'];?></td>
                        <td><?php echo $value['contacter'];?></td>
                        <td><?php echo $value['phonenumber'];?></td>
                        <td><?php echo $value['addtime'];?></td>
                        <td><a  href="http://localhost/hpjobweb/index.php/backend/branch/editAds/id/<?php echo $value['id'];?>"><i class="icon-edit"></i>编辑</a>&nbsp;&nbsp;
                        <a href="http://localhost/hpjobweb/index.php/backend/branch/del/id/<?php echo $value['id'];?>"><i class="icon-trash"></i>删除</a>&nbsp;&nbsp;
                        <a href="../../../<?php echo $value['phpqrcode'];?>" target="_blank"><i class="icon-picture"></i>查看二维码</a>
                        </td> 
                    </tr>
                    <?php }?><?php endif;?>
                    
                </table>
                <style type="text/css">
                    .input-mini{
                        width: 30px;
                        text-align:center;
                    }
                </style>
                <script type="text/javascript">
                    $('.del-ads').click(function(){
                        if(confirm('确认删除？')){
                            var _obj=$(this).parents('tr');
                            $.post(
                            'http://localhost/hpjobweb/index.php/backend/branch/delAds',
                            {id:$(this).attr('href')},
                            function(data) {
                                if(data){
                                    _obj.fadeOut(350);
                                }
                            },'html'
                        );
                        }
                        return false;
                    });
                </script>
            </div>

 
        </div>
        <script type="text/javascript">
            $('#tabs').tabs({ selected: <?php if(!empty($_GET['action'])){?><?php echo $_GET['action'];?><?php  }else{ ?>0<?php }?>});
        </script>
    </body>
</html>