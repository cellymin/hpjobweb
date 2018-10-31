<?php

/*
 * Describe   :后台配置模型
 */

class webConfigModel extends Model {

    private $validate_rule;

    function __construct() {
        $this->validate_rule = M('validate_rule');
    }

    /**
     * 添加验证规则
     * @param type $data 
     */
    function addRule($data) {
        return $this->validate_rule->insert($data);
    }

    /**
     * 取得所有的验证规则 
     */
    function getRule() {
        return $this->validate_rule->findall();
    }

    function editRule($condition, $data) {
        return $this->validate_rule->where($condition)->update($data)>=0;
    }

    function delRule($condition) {
        return $this->validate_rule->delete($condition);
    }

}

