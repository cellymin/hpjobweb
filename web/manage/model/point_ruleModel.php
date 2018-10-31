<?php

/*
 * Describe   : 积分规则模型
 */

class point_ruleModel extends Model {
    public $point;
    function __construct(){
    	$this->point=M('point_rule');
    }
    function ruleList(){
    	return $this->point->findall();
    }

    /*
     * 获取某个操作的积分规则
     * $cond 操作名称
     */
    function getPointRule($cond,$field='opt_name,operator'){
    	return $this->point->field($field)->where("opt_name='$cond'")->find();
    }

    function addPointRule($data){
    	return $this->point->insert($data);
    }

    function editPointRule($cond,$data){
    	return $this->point->where('id='.$cond)->update($data)>=0;
    }
}