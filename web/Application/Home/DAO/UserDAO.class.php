<?php

namespace Home\DAO;

/**
 * 用户 DAO
 *
 * @author 李静波
 */
class UserDAO extends PSIBaseDAO {
	var $db;

	function __construct($db = null) {
		if ($db == null) {
			$db = M();
		}
		
		$this->db = $db;
	}

	/**
	 * 判断某个用户是否被禁用
	 *
	 * @param string $userId
	 *        	用户id
	 * @return boolean true: 被禁用
	 */
	public function isDisabled($userId) {
		$db = $this->db;
		
		$sql = "select enabled from t_user where id = '%s' ";
		$data = $db->query($sql, $userId);
		if ($data) {
			return $data[0]["enabled"] == 0;
		} else {
			// $userId的用户不存在，也视为被禁用了
			return true;
		}
	}

	/**
	 * 判断是否可以登录
	 * 
	 * @param array $params        	
	 * @return string|NULL 可以登录返回用户id，否则返回null
	 */
	public function doLogin($params) {
		$loginName = $params["loginName"];
		$password = $params["password"];
		
		$db = $this->db;
		
		$sql = "select id from t_user where login_name = '%s' and password = '%s' and enabled = 1";
		
		$data = $db->query($sql, $loginName, md5($password));
		
		if ($data) {
			return $data[0]["id"];
		} else {
			return null;
		}
	}
}