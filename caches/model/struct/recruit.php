<?php return array (
  'recruit_name' => 
  array (
    'title' => '职位名称',
    'field_tips' => '',
    'add_html' => '<input type="text" name="recruit_name"   validate={"required":true,"messages":"请输入职位名称"}  value="" />',
    'edit_html' => '<input type="text" name="recruit_name"   validate={"required":true,"messages":"请输入职位名称"}  value="<?php echo $recruit_name;?>" />',
  ),
  'return_money' => 
  array (
    'title' => '入职返现金额',
    'field_tips' => '福利中有入职返现再填此项',
    'add_html' => '<input type="text" name="return_money"   value="0" />',
    'edit_html' => '<input type="text" name="return_money"   value="<?php echo $return_money;?>" />',
  ),
  'uid' => 
  array (
    'title' => '企业用户ID',
    'field_tips' => '请填写正确的公司id!',
    'add_html' => '<input type="text" name="uid"   value="" />',
    'edit_html' => '<input type="text" name="uid"   value="<?php echo $uid;?>" />',
  ),
  'welfare' => 
  array (
    'title' => '福利',
    'field_tips' => '',
    'add_html' => '<input type="text" id="welfare" title="" value=""  validate={"required":true}  /><script>$(function(){$("#welfare").linkage_style_2({
                data:linkage_23,
                field:\'welfare\',
                html_attr:\' validate={"required":true} \',checkbox:true
                })});</script>',
    'edit_html' => '<input type="text" id="welfare" title="" value=""  validate={"required":true}  /><script>$(function(){$("#welfare").linkage_style_2({
                data:linkage_23,
                field:\'welfare\',
                html_attr:\' validate={"required":true} \',defaults:\'<?php echo $welfare;?>\',checkbox:true
                })});</script>',
  ),
  'high_salary' => 
  array (
    'title' => '今日高薪',
    'field_tips' => '0代表否1代表是',
    'add_html' => '<input type="text" name="high_salary"   validate={"required":true}  value="0" />',
    'edit_html' => '<input type="text" name="high_salary"   validate={"required":true}  value="<?php echo $high_salary;?>" />',
  ),
  'istop' => 
  array (
    'title' => '置顶',
    'field_tips' => '0代表否1代表是',
    'add_html' => '<input type="text" name="istop"   validate={"required":true}  value="0" />',
    'edit_html' => '<input type="text" name="istop"   validate={"required":true}  value="<?php echo $istop;?>" />',
  ),
  'work_time' => 
  array (
    'title' => '工作时限',
    'field_tips' => '',
    'add_html' => '<input type="text" name="work_time"   value="" />',
    'edit_html' => '<input type="text" name="work_time"   value="<?php echo $work_time;?>" />',
  ),
  'check' => 
  array (
    'title' => '审核',
    'field_tips' => '',
    'add_html' => '<label><input type="radio" name="check"  value="0" validate={"required":true}   />未通过</label><label><input type="radio" name="check"  value="1" validate={"required":true}  checked="checked" />通过</label>',
    'edit_html' => '<label><input type="radio" name="check"  value="0" validate={"required":true}  <?php if(in_array("0",explode("#",$check))):?>checked<?php endif;?> />未通过</label><label><input type="radio" name="check"  value="1" validate={"required":true}  <?php if(in_array("1",explode("#",$check))):?>checked<?php endif;?> />通过</label>',
  ),
  'origin' => 
  array (
    'title' => '来源',
    'field_tips' => '
',
    'add_html' => '<select name="origin"   validate={"required":true} ><option value="">请选择</option><option value="0" selected="selected">全部</option><option value="1" >开心直招</option><option value="2" >企业直招</option><option value="3" >代招</option></select>',
    'edit_html' => '<select name="origin"   validate={"required":true} ><option value="">请选择</option><option value="0" <?php if($origin=="0"):?>selected<?php endif;?>>全部</option><option value="1" <?php if($origin=="1"):?>selected<?php endif;?>>开心直招</option><option value="2" <?php if($origin=="2"):?>selected<?php endif;?>>企业直招</option><option value="3" <?php if($origin=="3"):?>selected<?php endif;?>>代招</option></select>',
  ),
  'company_tel' => 
  array (
    'title' => '公司客服',
    'field_tips' => '',
    'add_html' => '<input type="text" name="company_tel"   validate={"required":true,"number":true}  value="" />',
    'edit_html' => '<input type="text" name="company_tel"   validate={"required":true,"number":true}  value="<?php echo $company_tel;?>" />',
  ),
  'jobs_industry' => 
  array (
    'title' => '职位行业',
    'field_tips' => '',
    'add_html' => '<input type="text" id="jobs_industry" title="" value=""  validate={"required":true}  /><script>$(function(){$("#jobs_industry").linkage_style_2({
                data:linkage_3,
                field:\'jobs_industry\',
                html_attr:\' validate={"required":true} \'
                })});</script>',
    'edit_html' => '<input type="text" id="jobs_industry" title="" value=""  validate={"required":true}  /><script>$(function(){$("#jobs_industry").linkage_style_2({
                data:linkage_3,
                field:\'jobs_industry\',
                html_attr:\' validate={"required":true} \',defaults:\'<?php echo $jobs_industry;?>\'
                })});</script>',
  ),
  'class' => 
  array (
    'title' => '职位分类',
    'field_tips' => '',
    'add_html' => '<input type="text" id="class" title="" value=""  validate={"required":true}  /><script>$(function(){$("#class").linkage_style_2({
                data:linkage_4,
                field:\'class#class_two\',
                html_attr:\' validate={"required":true} \'
                })});</script>',
    'edit_html' => '<input type="text" id="class" title="" value=""  validate={"required":true}  /><script>$(function(){$("#class").linkage_style_2({
                data:linkage_4,
                field:\'class#class_two\',
                html_attr:\' validate={"required":true} \',defaults:\'<?php echo $class;?>#<?php echo $class_two;?>\'
                })});</script>',
  ),
  'job_desc' => 
  array (
    'title' => '职位描述',
    'field_tips' => '',
    'add_html' => '<textarea name = "job_desc"   style=" width:400px; height:80px;"  ></textarea>',
    'edit_html' => '<textarea name = "job_desc"   style=" width:400px; height:80px;"  ><?php echo $job_desc;?></textarea>',
  ),
  'jobs_property' => 
  array (
    'title' => '职位性质',
    'field_tips' => '',
    'add_html' => '<label><input type="radio" name="jobs_property"  value="0" validate={"required":true}   />实习</label><label><input type="radio" name="jobs_property"  value="1" validate={"required":true}   />兼职</label><label><input type="radio" name="jobs_property"  value="2" validate={"required":true}  checked="checked" />全职</label>',
    'edit_html' => '<label><input type="radio" name="jobs_property"  value="0" validate={"required":true}  <?php if(in_array("0",explode("#",$jobs_property))):?>checked<?php endif;?> />实习</label><label><input type="radio" name="jobs_property"  value="1" validate={"required":true}  <?php if(in_array("1",explode("#",$jobs_property))):?>checked<?php endif;?> />兼职</label><label><input type="radio" name="jobs_property"  value="2" validate={"required":true}  <?php if(in_array("2",explode("#",$jobs_property))):?>checked<?php endif;?> />全职</label>',
  ),
  'graduates' => 
  array (
    'title' => '应届生应聘',
    'field_tips' => '',
    'add_html' => '<label><input type="radio" name="graduates"  value="1" checked="checked" />允许</label><label><input type="radio" name="graduates"  value="0"  />不允许</label>',
    'edit_html' => '<label><input type="radio" name="graduates"  value="1" <?php if(in_array("1",explode("#",$graduates))):?>checked<?php endif;?> />允许</label><label><input type="radio" name="graduates"  value="0" <?php if(in_array("0",explode("#",$graduates))):?>checked<?php endif;?> />不允许</label>',
  ),
  'salary' => 
  array (
    'title' => '职位月薪',
    'field_tips' => '',
    'add_html' => '<select name="salary"   validate={"required":true} ><option value="">请选择</option><option value="0" selected="selected">不限</option><option value="1" >面议</option><option value="2000" >2000元/月以下</option><option value="200103000" >2001-3000元/月</option><option value="300105000" >3001-5000元/月</option><option value="500108000" >5001-8000元/月</option><option value="800110000" >8001-10000元/月</option><option value="1000115000" >10001-15000元/月</option><option value="1500025000" >15001-25000元/月</option><option value="2500000000" >25001元/月以上</option></select>',
    'edit_html' => '<select name="salary"   validate={"required":true} ><option value="">请选择</option><option value="0" <?php if($salary=="0"):?>selected<?php endif;?>>不限</option><option value="1" <?php if($salary=="1"):?>selected<?php endif;?>>面议</option><option value="2000" <?php if($salary=="2000"):?>selected<?php endif;?>>2000元/月以下</option><option value="200103000" <?php if($salary=="200103000"):?>selected<?php endif;?>>2001-3000元/月</option><option value="300105000" <?php if($salary=="300105000"):?>selected<?php endif;?>>3001-5000元/月</option><option value="500108000" <?php if($salary=="500108000"):?>selected<?php endif;?>>5001-8000元/月</option><option value="800110000" <?php if($salary=="800110000"):?>selected<?php endif;?>>8001-10000元/月</option><option value="1000115000" <?php if($salary=="1000115000"):?>selected<?php endif;?>>10001-15000元/月</option><option value="1500025000" <?php if($salary=="1500025000"):?>selected<?php endif;?>>15001-25000元/月</option><option value="2500000000" <?php if($salary=="2500000000"):?>selected<?php endif;?>>25001元/月以上</option></select>',
  ),
  'work_exp' => 
  array (
    'title' => '工作经验',
    'field_tips' => '',
    'add_html' => '<select name="work_exp"   validate={"required":true} ><option value="">请选择</option><option value="0" selected="selected">不限</option><option value="1" >无经验</option><option value="2" >1年以下</option><option value="3" >1-3年</option><option value="4" >3-5年</option><option value="5" >5-10年</option><option value="6" >10年以上</option></select>',
    'edit_html' => '<select name="work_exp"   validate={"required":true} ><option value="">请选择</option><option value="0" <?php if($work_exp=="0"):?>selected<?php endif;?>>不限</option><option value="1" <?php if($work_exp=="1"):?>selected<?php endif;?>>无经验</option><option value="2" <?php if($work_exp=="2"):?>selected<?php endif;?>>1年以下</option><option value="3" <?php if($work_exp=="3"):?>selected<?php endif;?>>1-3年</option><option value="4" <?php if($work_exp=="4"):?>selected<?php endif;?>>3-5年</option><option value="5" <?php if($work_exp=="5"):?>selected<?php endif;?>>5-10年</option><option value="6" <?php if($work_exp=="6"):?>selected<?php endif;?>>10年以上</option></select>',
  ),
  'start_time' => 
  array (
    'title' => '开始时间',
    'field_tips' => '',
    'add_html' => '<input type="text" name="start_time" onfocus="WdatePicker({minDate:\'%yyyy-%MM-%dd %HH:%mm:%ss\',alwaysUseStartDate:true,dateFmt:\'yyyy-MM-dd HH:mm:ss\',autoPickDate:true,vel:\'start_time\'})" id="start_time" validate={"required":true}  value="" />',
    'edit_html' => '<input type="text" name="start_time" onfocus="WdatePicker({minDate:\'%yyyy-%MM-%dd %HH:%mm:%ss\',alwaysUseStartDate:true,dateFmt:\'yyyy-MM-dd HH:mm:ss\',autoPickDate:true,vel:\'start_time\'})" id="start_time" validate={"required":true}  value="<?php echo $start_time;?>" />',
  ),
  'expiration_time' => 
  array (
    'title' => '到期时间',
    'field_tips' => '没有此需要，尽量选大的时间',
    'add_html' => '<input type="text" name="expiration_time" onfocus="WdatePicker({minDate:\'%yyyy-%MM-%dd %HH:%mm:%ss\',alwaysUseStartDate:true,dateFmt:\'yyyy-MM-dd HH:mm:ss\',autoPickDate:true,vel:\'expiration_time\'})" id="expiration_time" validate={"required":true}  value="" />',
    'edit_html' => '<input type="text" name="expiration_time" onfocus="WdatePicker({minDate:\'%yyyy-%MM-%dd %HH:%mm:%ss\',alwaysUseStartDate:true,dateFmt:\'yyyy-MM-dd HH:mm:ss\',autoPickDate:true,vel:\'expiration_time\'})" id="expiration_time" validate={"required":true}  value="<?php echo $expiration_time;?>" />',
  ),
  'recruit_num' => 
  array (
    'title' => '招聘人数',
    'field_tips' => '填若干或数字',
    'add_html' => '<input type="text" name="recruit_num"   validate={"required":true}  value="0" />',
    'edit_html' => '<input type="text" name="recruit_num"   validate={"required":true}  value="<?php echo $recruit_num;?>" />',
  ),
  'address' => 
  array (
    'title' => '工作地点',
    'field_tips' => '',
    'add_html' => '<select style="margin-right:3px;" id="address" name="address" class="input-medium" validate={"required":true} ></select><script>$(function(){$("#address").linkage_style_1({
                data:city,
                field:\'address#city#town\',
                html_attr:\'class="input-medium" validate={"required":true} \'
                })});</script>',
    'edit_html' => '<select style="margin-right:3px;" id="address" name="address" class="input-medium" validate={"required":true} ></select><script>$(function(){$("#address").linkage_style_1({
                data:city,
                field:\'address#city#town\',
                html_attr:\'class="input-medium" validate={"required":true} \',defaults:\'<?php echo $address;?>#<?php echo $city;?>#<?php echo $town;?>\'
                })});</script>',
  ),
  'degree' => 
  array (
    'title' => '学历要求',
    'field_tips' => '',
    'add_html' => '<select style="margin-right:3px;" id="degree" name="degree"  validate={"required":true} ></select><script>$(function(){$("#degree").linkage_style_1({
                data:linkage_18,
                field:\'degree\',
                html_attr:\' validate={"required":true} \'
                })});</script>',
    'edit_html' => '<select style="margin-right:3px;" id="degree" name="degree"  validate={"required":true} ></select><script>$(function(){$("#degree").linkage_style_1({
                data:linkage_18,
                field:\'degree\',
                html_attr:\' validate={"required":true} \',defaults:\'<?php echo $degree;?>\'
                })});</script>',
  ),
  'contact' => 
  array (
    'title' => '联系人',
    'field_tips' => '',
    'add_html' => '<input type="text" name="contact"   validate={"required":true}  value="" />',
    'edit_html' => '<input type="text" name="contact"   validate={"required":true}  value="<?php echo $contact;?>" />',
  ),
  'phone' => 
  array (
    'title' => '联系电话',
    'field_tips' => '',
    'add_html' => '<input type="text" name="phone"   validate={"required":true}  value="" />',
    'edit_html' => '<input type="text" name="phone"   validate={"required":true}  value="<?php echo $phone;?>" />',
  ),
  'rece_mail' => 
  array (
    'title' => '接收邮箱',
    'field_tips' => '',
    'add_html' => '<input type="text" name="rece_mail"   validate={"email":true}  value="" />',
    'edit_html' => '<input type="text" name="rece_mail"   validate={"email":true}  value="<?php echo $rece_mail;?>" />',
  ),
);