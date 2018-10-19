<?php if(!defined("PATH_LC"))exit;?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link type="text/css" rel="stylesheet" href="http://www.hap-job.com/public/css/bootstrap/bootstrap.min.css"/>
        <script type="text/javascript" src="http://www.hap-job.com/public/js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="http://www.hap-job.com/public/js/jqueryValidate/jquery.validate.min.js"></script>
        <script type="text/javascript" src="http://www.hap-job.com/public/js/jqueryValidate/jquery.metadata.js"></script>
        <script type="text/javascript" src="jquery.colorPicker"></script>
        <script type="text/javascript" src="http://www.hap-job.com/public/js/My97DatePicker/WdatePicker.js"></script>
        <script type="text/javascript" src="http://www.hap-job.com/caches/js/linkage_data.js"></script>
        <script type="text/javascript" src="http://www.hap-job.com/caches/js/linkage_style_1.js"></script>
    </head>
    <body>
        <div id="add-form">
            <form action="http://www.hap-job.com/index.php/backend/branch/editAds" method="post" validate="true" enctype="multipart/form-data">
                <table>
                    <tr>
                        <th>门店名称：</th>
                        <td><input type="text" name="name" validate="{required:true}" value="<?php echo $goods['name'];?>" /></td>
                    </tr>

                    <tr>
                        <th>门店地址：</th>
                        <td><input type="text" name="address" validate="{required:true}" value="<?php echo $goods['address'];?>" />
                        <input type="hidden" name="id" validate="{required:true}" value="<?php echo $goods['id'];?>" />
                        </td>
                    </tr>


                    <tr>
                        <th>联系人：</th>
                        <td><input type="text" name="contacter" validate="{required:true}" value="<?php echo $goods['contacter'];?>" /></td>
                    </tr> 
                   
                   
                    <tr>
                        <th>联系人手机号：</th>
                        <td><input type="text" name="phonenumber" validate="{required:true}" value="<?php echo $goods['phonenumber'];?>" /></td>
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
                        <td><button type="submit" class="btn btn-primary"><i class="icon-plus icon-white"></i> 修改</button></td>
                    </tr>
                </table>
            </form>
        </div>
        <script type="text/javascript">
            function show_area(aname){
                $('[class^=cate_c_]').hide();
                $('[class^=cate_c_] input').attr('disabled',true);
                $('[class^=cate_c_] textarea').attr('disabled',true);
                $('.'+aname).show();
                $('.'+aname+' input').attr('disabled',false);
                $('.'+aname+' textarea').attr('disabled',false);
            }
            show_area($('#cate').find(':checked').attr('cate'));
            $('#cate').change(function(){
                show_area($(this).find(':checked').attr('cate'));
            });
            
        </script>
        <style type="text/css">
            #jquery-colour-picker{
                width: 220px;
            }
            table th{
                width: 95px;
                text-align:right;
            }
            .cate-title{
                width: 200px;
                height: 40px;
            }
            table label{
                width: 70px;
                float: left;
                text-indent:0.3em;
            }
            table label input{
                display:block;
                padding-right:20px;
                float: left;
            }
        </style>
        <script type="text/javascript">
            $(function(){
                <?php if($ads['type']=='2' && substr($ads['path'],0,4)!='http'){?>
               uploadSuccess({"id":"SWFUpload_0_0","index":"0","size":"5870","name":"zhang.jpg"},'{"state":"SUCCESS","fid":"6bdf9bfeeb5eaaa762c44699578175c7","thumb":{"file":"http://www.hap-job.com/<?php echo $ads['path'];?>","w":"200","h":"200"},"file":[{"path":"<?php echo $ads['path'];?>","url":"http://www.hap-job.com/<?php echo $ads['path'];?>"}]}');
               <?php }?>
            });
        </script>
    </body>
</html>
