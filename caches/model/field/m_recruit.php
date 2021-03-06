<?php if(!defined("PATH_LC")){exit("禁止访问");} return array (
  'recruit_name' => 
  array (
    'mfid' => '14',
    'field_name' => 'recruit_name',
    'title' => '职位名称',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'input_varchar',
    'default_val' => '',
    'width' => '0',
    'height' => '0',
    'length' => '20',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '请输入职位名称',
    'state' => '1',
    'data' => '',
    'join_index' => '1',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'return_money' => 
  array (
    'mfid' => '135',
    'field_name' => 'return_money',
    'title' => '入职返现金额',
    'field_null' => '0',
    'rule' => '[]',
    'dmid' => '5',
    'field_type' => 'input_varchar',
    'default_val' => '0',
    'width' => '',
    'height' => '',
    'length' => '30',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '福利中有入职返现再填此项',
    'error_tips' => '',
    'state' => '1',
    'data' => '0',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => NULL,
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'uid' => 
  array (
    'mfid' => '63',
    'field_name' => 'uid',
    'title' => '企业用户ID',
    'field_null' => '0',
    'rule' => '[]',
    'dmid' => '5',
    'field_type' => 'input_int',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '请填写正确的公司id!',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '1',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'star' => 
  array (
    'mfid' => '130',
    'field_name' => 'star',
    'title' => '公司星级',
    'field_null' => '1',
    'rule' => '{"required":"true","number":"true"}',
    'dmid' => '5',
    'field_type' => 'input_int',
    'default_val' => '4',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '1代表1星,2代表2星,3代表3星,4代表4星,5代表5星',
    'error_tips' => '',
    'state' => '0',
    'data' => '0',
    'join_index' => '1',
    'lcgid' => '0',
    'attached' => NULL,
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'welfare' => 
  array (
    'mfid' => '131',
    'field_name' => 'welfare',
    'title' => '福利',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'linkage',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '255',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '0',
    'join_index' => '1',
    'lcgid' => '23',
    'attached' => '[]',
    'linkage_style' => '2',
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
      'checkbox' => '1',
    ),
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'looks' => 
  array (
    'mfid' => '132',
    'field_name' => 'looks',
    'title' => '职位浏览次数',
    'field_null' => '1',
    'rule' => '{"required":"true","number":"true"}',
    'dmid' => '5',
    'field_type' => 'input_int',
    'default_val' => '1',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => '0',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => NULL,
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'high_salary' => 
  array (
    'mfid' => '136',
    'field_name' => 'high_salary',
    'title' => '今日高薪',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'input_varchar',
    'default_val' => '0',
    'width' => '',
    'height' => '',
    'length' => '1',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '0代表否1代表是',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => NULL,
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
      'type' => '',
      'fieldtype' => '',
      'unsigned' => '',
    ),
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'istop' => 
  array (
    'mfid' => '137',
    'field_name' => 'istop',
    'title' => '置顶',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'input_varchar',
    'default_val' => '0',
    'width' => '',
    'height' => '',
    'length' => '1',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '0代表否1代表是',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => NULL,
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
      'type' => '',
      'fieldtype' => '',
      'unsigned' => '',
    ),
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'work_time' => 
  array (
    'mfid' => '138',
    'field_name' => 'work_time',
    'title' => '工作时限',
    'field_null' => '1',
    'rule' => '[]',
    'dmid' => '5',
    'field_type' => 'input_varchar',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '100',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '0',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => NULL,
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => NULL,
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'check' => 
  array (
    'mfid' => '139',
    'field_name' => 'check',
    'title' => '审核',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'switch',
    'default_val' => '1',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => 
    array (
      0 => '未通过',
      1 => '通过',
    ),
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => NULL,
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
      'type' => 'radio',
      'fieldtype' => 'TINYINT',
      'unsigned' => '',
    ),
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'origin' => 
  array (
    'mfid' => '140',
    'field_name' => 'origin',
    'title' => '来源',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'switch',
    'default_val' => '0',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '
',
    'error_tips' => '',
    'state' => '1',
    'data' => 
    array (
      0 => '全部',
      1 => '开心直招',
      2 => '企业直招',
      3 => '代招',
    ),
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => NULL,
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
      'type' => 'option',
      'fieldtype' => 'VARCHAR',
    ),
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'company_tel' => 
  array (
    'mfid' => '141',
    'field_name' => 'company_tel',
    'title' => '公司客服',
    'field_null' => '1',
    'rule' => '{"required":"true","number":"true"}',
    'dmid' => '5',
    'field_type' => 'input_int',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '20',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '0',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => NULL,
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'jobs_industry' => 
  array (
    'mfid' => '22',
    'field_name' => 'jobs_industry',
    'title' => '职位行业',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'linkage',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '2',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '1',
    'lcgid' => '3',
    'attached' => '[]',
    'linkage_style' => '2',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'class' => 
  array (
    'mfid' => '24',
    'field_name' => 'class',
    'title' => '职位分类',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'linkage',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '3',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '1',
    'lcgid' => '4',
    'attached' => '["class_two"]',
    'linkage_style' => '2',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'effective_time' => 
  array (
    'mfid' => '65',
    'field_name' => 'effective_time',
    'title' => '有效时间',
    'field_null' => '1',
    'rule' => '{"required":"true","digits":"true","min":"30","regexp":"\\/\\\\d+\\/"}',
    'dmid' => '5',
    'field_type' => 'input_int',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => 'id="effective_time"',
    'sort' => '4',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '请输入数字',
    'state' => '0',
    'data' => '',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'seo_desc' => 
  array (
    'mfid' => '82',
    'field_name' => 'seo_desc',
    'title' => '职位摘要',
    'field_null' => '0',
    'rule' => '{"maxlength":"80"}',
    'dmid' => '5',
    'field_type' => 'textarea',
    'default_val' => '',
    'width' => '400',
    'height' => '80',
    'length' => '80',
    'html_attr' => '',
    'sort' => '5',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => '0',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => NULL,
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'job_desc' => 
  array (
    'mfid' => '27',
    'field_name' => 'job_desc',
    'title' => '职位描述',
    'field_null' => '0',
    'rule' => '[]',
    'dmid' => '5',
    'field_type' => 'textarea',
    'default_val' => '',
    'width' => '400',
    'height' => '80',
    'length' => '2000',
    'html_attr' => '',
    'sort' => '6',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'sex' => 
  array (
    'mfid' => '16',
    'field_name' => 'sex',
    'title' => '性别要求',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'switch',
    'default_val' => '0',
    'width' => '',
    'height' => '',
    'length' => '1',
    'html_attr' => '',
    'sort' => '7',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => 
    array (
      0 => '不限',
      1 => '男',
      2 => '女',
    ),
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
      'type' => 'radio',
      'fieldtype' => 'TINYINT',
      'unsigned' => 'UNSIGNED',
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'jobs_property' => 
  array (
    'mfid' => '18',
    'field_name' => 'jobs_property',
    'title' => '职位性质',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'switch',
    'default_val' => '2',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '9',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => 
    array (
      0 => '实习',
      1 => '兼职',
      2 => '全职',
    ),
    'join_index' => '1',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => '0',
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
      'type' => 'radio',
      'fieldtype' => 'TINYINT',
      'unsigned' => 'UNSIGNED',
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'graduates' => 
  array (
    'mfid' => '20',
    'field_name' => 'graduates',
    'title' => '应届生应聘',
    'field_null' => '0',
    'rule' => '[]',
    'dmid' => '5',
    'field_type' => 'switch',
    'default_val' => '1',
    'width' => NULL,
    'height' => NULL,
    'length' => '1',
    'html_attr' => '',
    'sort' => '10',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => 
    array (
      1 => '允许',
      0 => '不允许',
    ),
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
      'type' => 'radio',
    ),
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'salary' => 
  array (
    'mfid' => '33',
    'field_name' => 'salary',
    'title' => '职位月薪',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'switch',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '11',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => 
    array (
      0 => '不限',
      1 => '面议',
      2000 => '2000元/月以下',
      200103000 => '2001-3000元/月',
      300105000 => '3001-5000元/月',
      500108000 => '5001-8000元/月',
      800110000 => '8001-10000元/月',
      1000115000 => '10001-15000元/月',
      1500025000 => '15001-25000元/月',
      2500000000 => '25001元/月以上',
    ),
    'join_index' => '1',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => '1',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
      'type' => 'option',
      'fieldtype' => 'INT',
      'unsigned' => 'UNSIGNED',
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'work_exp' => 
  array (
    'mfid' => '30',
    'field_name' => 'work_exp',
    'title' => '工作经验',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'switch',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '12',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => 
    array (
      0 => '不限',
      1 => '无经验',
      2 => '1年以下',
      3 => '1-3年',
      4 => '3-5年',
      5 => '5-10年',
      6 => '10年以上',
    ),
    'join_index' => '1',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => '1',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
      'type' => 'option',
      'fieldtype' => 'TINYINT',
      'unsigned' => 'UNSIGNED',
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'issue_type' => 
  array (
    'mfid' => '34',
    'field_name' => 'issue_type',
    'title' => '发布日期',
    'field_null' => '0',
    'rule' => '[]',
    'dmid' => '5',
    'field_type' => 'switch',
    'default_val' => '1',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '13',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => 
    array (
      1 => '立即发布',
      2 => '定时发布',
    ),
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
      'type' => 'radio',
      'fieldtype' => 'TINYINT',
      'unsigned' => 'UNSIGNED',
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'start_time' => 
  array (
    'mfid' => '62',
    'field_name' => 'start_time',
    'title' => '开始时间',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'input_int',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => 'id="start_time"',
    'sort' => '14',
    'js_event' => 'onfocus="WdatePicker({minDate:\'%yyyy-%MM-%dd %HH:%mm:%ss\',alwaysUseStartDate:true,dateFmt:\'yyyy-MM-dd HH:mm:ss\',autoPickDate:true,vel:\'start_time\'})"',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'expiration_time' => 
  array (
    'mfid' => '66',
    'field_name' => 'expiration_time',
    'title' => '到期时间',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'input_int',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => 'id="expiration_time"',
    'sort' => '15',
    'js_event' => 'onfocus="WdatePicker({minDate:\'%yyyy-%MM-%dd %HH:%mm:%ss\',alwaysUseStartDate:true,dateFmt:\'yyyy-MM-dd HH:mm:ss\',autoPickDate:true,vel:\'expiration_time\'})"',
    'field_tips' => '没有此需要，尽量选大的时间',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '1',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'recruit_num' => 
  array (
    'mfid' => '32',
    'field_name' => 'recruit_num',
    'title' => '招聘人数',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'input_varchar',
    'default_val' => '0',
    'width' => '',
    'height' => '',
    'length' => '4',
    'html_attr' => '',
    'sort' => '16',
    'js_event' => '',
    'field_tips' => '填若干或数字',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '1',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'address' => 
  array (
    'mfid' => '29',
    'field_name' => 'address',
    'title' => '工作地点',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'linkage',
    'default_val' => '',
    'width' => '0',
    'height' => '0',
    'length' => '10',
    'html_attr' => 'class="input-medium"',
    'sort' => '17',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '1',
    'lcgid' => 'city',
    'attached' => '["city","town"]',
    'linkage_style' => '1',
    'editor_style' => '1',
    'show_field' => '1',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'company_name' => 
  array (
    'mfid' => '35',
    'field_name' => 'company_name',
    'title' => '公司名称',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'input_varchar',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '30',
    'html_attr' => '',
    'sort' => '18',
    'js_event' => '',
    'field_tips' => '为防止公司重名，请填写正确的公司id！',
    'error_tips' => '',
    'state' => '0',
    'data' => '',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'company_property' => 
  array (
    'mfid' => '37',
    'field_name' => 'company_property',
    'title' => '公司性质',
    'field_null' => '0',
    'rule' => '{"min":"1"}',
    'dmid' => '5',
    'field_type' => 'switch',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '19',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => 
    array (
      1 => '国企',
      2 => '外商独资',
      3 => '代表处',
      4 => '合资',
      5 => '民营',
      6 => '股份制企业',
      7 => '上市公司',
      8 => '国家机关',
      9 => '事业单位',
      10 => '其它',
    ),
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => '1',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
      'type' => 'option',
      'fieldtype' => 'TINYINT',
      'unsigned' => 'UNSIGNED',
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'company_industry' => 
  array (
    'mfid' => '36',
    'field_name' => 'company_industry',
    'title' => '行业',
    'field_null' => '0',
    'rule' => '[]',
    'dmid' => '5',
    'field_type' => 'linkage',
    'default_val' => '',
    'width' => NULL,
    'height' => NULL,
    'length' => '10',
    'html_attr' => '',
    'sort' => '20',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => '',
    'join_index' => '0',
    'lcgid' => '3',
    'attached' => '[]',
    'linkage_style' => '2',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => NULL,
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'company_scope' => 
  array (
    'mfid' => '38',
    'field_name' => 'company_scope',
    'title' => '公司规模',
    'field_null' => '1',
    'rule' => '{"required":"true","minlength":"1"}',
    'dmid' => '5',
    'field_type' => 'switch',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '21',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => 
    array (
      1 => '20人以下',
      2 => '20-99人',
      3 => '100-499人',
      4 => '500-999人',
      5 => '1000-9999人',
      6 => '10000人以上',
    ),
    'join_index' => '1',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => '1',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
      'type' => 'option',
      'fieldtype' => 'TINYINT',
      'unsigned' => 'UNSIGNED',
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'degree' => 
  array (
    'mfid' => '31',
    'field_name' => 'degree',
    'title' => '学历要求',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'linkage',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '22',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '1',
    'lcgid' => '18',
    'attached' => '[]',
    'linkage_style' => '1',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'company_index' => 
  array (
    'mfid' => '39',
    'field_name' => 'company_index',
    'title' => '公司主页',
    'field_null' => '0',
    'rule' => '{"url":"true"}',
    'dmid' => '5',
    'field_type' => 'input_varchar',
    'default_val' => 'http://',
    'width' => '',
    'height' => '',
    'length' => '25',
    'html_attr' => '',
    'sort' => '22',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => '',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'company_desc' => 
  array (
    'mfid' => '40',
    'field_name' => 'company_desc',
    'title' => '公司介绍',
    'field_null' => '0',
    'rule' => '[]',
    'dmid' => '5',
    'field_type' => 'textarea',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '400',
    'html_attr' => '',
    'sort' => '23',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => '我文网文',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'contact' => 
  array (
    'mfid' => '41',
    'field_name' => 'contact',
    'title' => '联系人',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'input_varchar',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '24',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'phone' => 
  array (
    'mfid' => '42',
    'field_name' => 'phone',
    'title' => '联系电话',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '5',
    'field_type' => 'input_varchar',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '40',
    'html_attr' => '',
    'sort' => '25',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'rece_mail' => 
  array (
    'mfid' => '43',
    'field_name' => 'rece_mail',
    'title' => '接收邮箱',
    'field_null' => '0',
    'rule' => '{"email":"true"}',
    'dmid' => '5',
    'field_type' => 'input_varchar',
    'default_val' => '',
    'width' => NULL,
    'height' => NULL,
    'length' => '10',
    'html_attr' => '',
    'sort' => '26',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '',
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => NULL,
    'js_rule' => NULL,
    'is_sys' => '0',
  ),
  'verify' => 
  array (
    'mfid' => '118',
    'field_name' => 'verify',
    'title' => '企业认证',
    'field_null' => '1',
    'rule' => '{"required":"true","maxlength":"10"}',
    'dmid' => '5',
    'field_type' => 'input_int',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '28',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => '0',
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => NULL,
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
);