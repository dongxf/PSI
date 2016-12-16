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

	/**
	 * 判断当前用户是否有某个功能的权限
	 *
	 * @param string $userId
	 *        	用户id
	 * @param string $fid
	 *        	功能id
	 * @return boolean true:有该功能的权限
	 */
	public function hasPermission($userId, $fid) {
		$db = $this->db;
		$sql = "select count(*) as cnt
				from  t_role_user ru, t_role_permission rp, t_permission p
				where ru.user_id = '%s' and ru.role_id = rp.role_id
				      and rp.permission_id = p.id and p.fid = '%s' ";
		$data = $db->query($sql, $userId, $fid);
		
		return $data[0]["cnt"] > 0;
	}

	/**
	 * 根据用户id查询用户名称
	 *
	 * @param string $userId
	 *        	用户id
	 *        	
	 * @return string 用户姓名
	 */
	public function getLoginUserName($userId) {
		$db = $this->db;
		
		$sql = "select name from t_user where id = '%s' ";
		
		$data = $db->query($sql, $userId);
		
		if ($data) {
			return $data[0]["name"];
		} else {
			return "";
		}
	}

	/**
	 * 获得带组织机构的用户全名
	 *
	 * @param string $userId
	 *        	用户id
	 * @return string
	 */
	public function getLoignUserNameWithOrgFullName($userId) {
		$db = $this->db;
		
		$userName = $this->getLoginUserName($userId);
		if ($userName == "") {
			return $userName;
		}
		
		$sql = "select o.full_name
				from t_org o, t_user u
				where o.id = u.org_id and u.id = '%s' ";
		$data = $db->query($sql, $userId);
		$orgFullName = "";
		if ($data) {
			$orgFullName = $data[0]["full_name"];
		}
		
		return addslashes($orgFullName . "\\" . $userName);
	}

	/**
	 * 获得用户的登录名
	 *
	 * @param string $userId        	
	 * @return string
	 */
	public function getLoginName($userId) {
		$db = $this->db;
		
		$sql = "select login_name from t_user where id = '%s' ";
		
		$data = $db->query($sql, $userId);
		
		if ($data) {
			return $data[0]["login_name"];
		} else {
			return "";
		}
	}
}