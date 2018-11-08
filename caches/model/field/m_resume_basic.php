<?php if(!defined("PATH_LC")){exit("禁止访问");} return array (
  'resume_id' => 
  array (
    'mfid' => '101',
    'field_name' => 'resume_id',
    'title' => '简历ID',
    'field_null' => '1',
    'rule' => '{"required":"true","maxlength":"10"}',
    'dmid' => '6',
    'field_type' => 'input_int',
    'default_val' => '',
    'width' => '0',
    'height' => '0',
    'length' => '10',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '',
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
    'is_sys' => '1',
  ),
  'uid' => 
  array (
    'mfid' => '121',
    'field_name' => 'uid',
    'title' => '用户ID',
    'field_null' => '1',
    'rule' => '[]',
    'dmid' => '6',
    'field_type' => 'input_int',
    'default_val' => '',
    'width' => '0',
    'height' => '0',
    'length' => '10',
    'html_attr' => '',
    'sort' => '1',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => '0',
    'join_index' => '1',
    'lcgid' => '0',
    'attached' => NULL,
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => NULL,
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'name' => 
  array (
    'mfid' => '47',
    'field_name' => 'name',
    'title' => '真实姓名',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '6',
    'field_type' => 'input_varchar',
    'default_val' => '',
    'width' => NULL,
    'height' => NULL,
    'length' => '6',
    'html_attr' => '',
    'sort' => '2',
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
    'is_sys' => '1',
  ),
  'gender' => 
  array (
    'mfid' => '48',
    'field_name' => 'gender',
    'title' => '性别',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '6',
    'field_type' => 'switch',
    'default_val' => '',
    'width' => '0',
    'height' => '0',
    'length' => '1',
    'html_attr' => '',
    'sort' => '3',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => 
    array (
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
  'birthday' => 
  array (
    'mfid' => '49',
    'field_name' => 'birthday',
    'title' => '出生日期（年）',
    'field_null' => '1',
    'rule' => '{"required":"true","digits":"true"}',
    'dmid' => '6',
    'field_type' => 'input_int',
    'default_val' => '',
    'width' => NULL,
    'height' => NULL,
    'length' => '',
    'html_attr' => '',
    'sort' => '4',
    'js_event' => 'onfocus="WdatePicker({dateFmt:\'yyyy\',minDate:\'1960\',startDate:\'1980\',autoPickDate:true})"',
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
    'is_sys' => '1',
  ),
  'marital_status' => 
  array (
    'mfid' => '51',
    'field_name' => 'marital_status',
    'title' => '婚姻状况',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '6',
    'field_type' => 'switch',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '1',
    'html_attr' => '',
    'sort' => '5',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => 
    array (
      1 => '未婚',
      2 => '已婚',
      3 => '保密',
    ),
    'join_index' => '1',
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
  'origin_provice' => 
  array (
    'mfid' => '53',
    'field_name' => 'origin_provice',
    'title' => '户口所在地',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '6',
    'field_type' => 'linkage',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '7',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => '',
    'join_index' => '0',
    'lcgid' => 'city',
    'attached' => '["origin_city","origin_town"]',
    'linkage_style' => '1',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'link_provice' => 
  array (
    'mfid' => '81',
    'field_name' => 'link_provice',
    'title' => '联系地址',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '6',
    'field_type' => 'linkage',
    'default_val' => '',
    'width' => '0',
    'height' => '0',
    'length' => '10',
    'html_attr' => '',
    'sort' => '8',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '0',
    'join_index' => '0',
    'lcgid' => 'city',
    'attached' => '["link_city","link_town"]',
    'linkage_style' => '1',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'cert_type' => 
  array (
    'mfid' => '57',
    'field_name' => 'cert_type',
    'title' => '证件类型',
    'field_null' => '0',
    'rule' => '[]',
    'dmid' => '6',
    'field_type' => 'switch',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '10',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '0',
    'data' => 
    array (
      1 => '身份证',
      2 => '护照',
      3 => '军官证',
      4 => '香港身份证',
      5 => '澳门身份证',
      6 => '港澳通行证',
      7 => '台胞证',
      8 => '其他',
    ),
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => '[]',
    'linkage_style' => '0',
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
      'type' => 'option',
      'fieldtype' => 'TINYINT',
      'unsigned' => 'UNSIGNED',
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'id_number' => 
  array (
    'mfid' => '83',
    'field_name' => 'id_number',
    'title' => '证件号码',
    'field_null' => '1',
    'rule' => '{"required":"true","maxlength":"20"}',
    'dmid' => '6',
    'field_type' => 'input_varchar',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '20',
    'html_attr' => '',
    'sort' => '11',
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
  'work_exp' => 
  array (
    'mfid' => '52',
    'field_name' => 'work_exp',
    'title' => '工作经验',
    'field_null' => '0',
    'rule' => '[]',
    'dmid' => '6',
    'field_type' => 'linkage',
    'default_val' => '',
    'width' => '0',
    'height' => '0',
    'length' => '10',
    'html_attr' => '',
    'sort' => '12',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '1',
    'lcgid' => '22',
    'attached' => '[]',
    'linkage_style' => '1',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
      'type' => '',
      'fieldtype' => '',
      'unsigned' => '',
      'frontend' => '',
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'telephone' => 
  array (
    'mfid' => '58',
    'field_name' => 'telephone',
    'title' => '联系电话',
    'field_null' => '0',
    'rule' => '',
    'dmid' => '6',
    'field_type' => 'input_varchar',
    'default_val' => NULL,
    'width' => NULL,
    'height' => NULL,
    'length' => '12',
    'html_attr' => '',
    'sort' => '12',
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
    'is_sys' => '1',
  ),
  'link_email' => 
  array (
    'mfid' => '114',
    'field_name' => 'link_email',
    'title' => '联系Email',
    'field_null' => '1',
    'rule' => '{"email":"true","maxlength":"30"}',
    'dmid' => '6',
    'field_type' => 'input_varchar',
    'default_val' => '',
    'width' => NULL,
    'height' => NULL,
    'length' => '30',
    'html_attr' => '',
    'sort' => '14',
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
    'is_sys' => '1',
  ),
  'profile' => 
  array (
    'mfid' => '104',
    'field_name' => 'profile',
    'title' => '个人主页',
    'field_null' => '1',
    'rule' => '{"required":"true","url":"true","maxlength":"100"}',
    'dmid' => '6',
    'field_type' => 'input_varchar',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '100',
    'html_attr' => '',
    'sort' => '15',
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
  'self_eval' => 
  array (
    'mfid' => '106',
    'field_name' => 'self_eval',
    'title' => '自我评价',
    'field_null' => '1',
    'rule' => '{"maxlength":"200"}',
    'dmid' => '6',
    'field_type' => 'textarea',
    'default_val' => '',
    'width' => NULL,
    'height' => NULL,
    'length' => '200',
    'html_attr' => '',
    'sort' => '16',
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
    'is_sys' => '1',
  ),
  'address' => 
  array (
    'mfid' => '112',
    'field_name' => 'address',
    'title' => '详细地址',
    'field_null' => '1',
    'rule' => '{"required":"true","maxlength":"25"}',
    'dmid' => '6',
    'field_type' => 'input_varchar',
    'default_val' => '',
    'width' => NULL,
    'height' => NULL,
    'length' => '25',
    'html_attr' => '',
    'sort' => '17',
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
    'is_sys' => '1',
  ),
  'work_type' => 
  array (
    'mfid' => '107',
    'field_name' => 'work_type',
    'title' => '期望工作性质',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '6',
    'field_type' => 'switch',
    'default_val' => '1',
    'width' => '0',
    'height' => '0',
    'length' => '50',
    'html_attr' => '',
    'sort' => '17',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => 
    array (
      1 => '全职',
      2 => '兼职',
      3 => '实习',
    ),
    'join_index' => '0',
    'lcgid' => '0',
    'attached' => NULL,
    'linkage_style' => NULL,
    'editor_style' => '0',
    'show_field' => '0',
    'setting' => 
    array (
      'type' => 'checkbox',
      'fieldtype' => 'VARCHAR',
      'frontend' => '2',
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'hope_industry' => 
  array (
    'mfid' => '110',
    'field_name' => 'hope_industry',
    'title' => '期望从事行业',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '6',
    'field_type' => 'linkage',
    'default_val' => '',
    'width' => NULL,
    'height' => NULL,
    'length' => '10',
    'html_attr' => '',
    'sort' => '18',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '0',
    'join_index' => '0',
    'lcgid' => '3',
    'attached' => '[]',
    'linkage_style' => '2',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
      'checkbox' => '1',
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'hope_career' => 
  array (
    'mfid' => '109',
    'field_name' => 'hope_career',
    'title' => '期望从事职业',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '6',
    'field_type' => 'linkage',
    'default_val' => '',
    'width' => NULL,
    'height' => NULL,
    'length' => '10',
    'html_attr' => '',
    'sort' => '19',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '0',
    'join_index' => '0',
    'lcgid' => '4',
    'attached' => '["hope_career_t"]',
    'linkage_style' => '2',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
      'checkbox' => '1',
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'hope_provice' => 
  array (
    'mfid' => '108',
    'field_name' => 'hope_provice',
    'title' => '期望工作地点',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '6',
    'field_type' => 'linkage',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '10',
    'html_attr' => '',
    'sort' => '20',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '0',
    'join_index' => '0',
    'lcgid' => 'city',
    'attached' => '["hope_city","hope_town"]',
    'linkage_style' => '1',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'hope_salary' => 
  array (
    'mfid' => '111',
    'field_name' => 'hope_salary',
    'title' => '期望月薪(税前)',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '6',
    'field_type' => 'linkage',
    'default_val' => '200104000',
    'width' => '0',
    'height' => '0',
    'length' => '',
    'html_attr' => '',
    'sort' => '21',
    'js_event' => '',
    'field_tips' => '',
    'error_tips' => '',
    'state' => '1',
    'data' => '',
    'join_index' => '0',
    'lcgid' => '19',
    'attached' => '[]',
    'linkage_style' => '1',
    'editor_style' => '0',
    'show_field' => '1',
    'setting' => 
    array (
      'type' => '',
      'fieldtype' => '',
      'unsigned' => '',
      'frontend' => '',
    ),
    'js_rule' => NULL,
    'is_sys' => '1',
  ),
  'degree' => 
  array (
    'mfid' => '126',
    'field_name' => 'degree',
    'title' => '学历',
    'field_null' => '1',
    'rule' => '{"required":"true"}',
    'dmid' => '6',
    'field_type' => 'linkage',
    'default_val' => '1',
    'width' => '',
    'height' => '',
    'length' => '',
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
  'job_start' => 
  array (
    'mfid' => '127',
    'field_name' => 'job_start',
    'title' => '开始工作',
    'field_null' => '0',
    'rule' => '[]',
    'dmid' => '6',
    'field_type' => 'input_int',
    'default_val' => '',
    'width' => '',
    'height' => '',
    'length' => '',
    'html_attr' => '',
    'sort' => '23',
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
);