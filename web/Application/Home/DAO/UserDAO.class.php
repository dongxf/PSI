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

	/**
	 * 获得某个组织机构的人员
	 */
	public function users($params) {
		$db = $this->db;
		
		$orgId = $params["orgId"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$sql = "select id, login_name,  name, enabled, org_code, gender, birthday, id_card_number, tel,
				    tel02, address, data_org
				from t_user
				where org_id = '%s'
				order by org_code
				limit %d , %d ";
		
		$data = $db->query($sql, $orgId, $start, $limit);
		
		$result = array();
		
		foreach ( $data as $key => $value ) {
			$result[$key]["id"] = $value["id"];
			$result[$key]["loginName"] = $value["login_name"];
			$result[$key]["name"] = $value["name"];
			$result[$key]["enabled"] = $value["enabled"];
			$result[$key]["orgCode"] = $value["org_code"];
			$result[$key]["gender"] = $value["gender"];
			$result[$key]["birthday"] = $value["birthday"];
			$result[$key]["idCardNumber"] = $value["id_card_number"];
			$result[$key]["tel"] = $value["tel"];
			$result[$key]["tel02"] = $value["tel02"];
			$result[$key]["address"] = $value["address"];
			$result[$key]["dataOrg"] = $value["data_org"];
		}
		
		$sql = "select count(*) as cnt
				from t_user
				where org_id = '%s' ";
		
		$data = $db->query($sql, $orgId);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 做类似这种增长 '01010001' => '01010002', 用户的数据域+1
	 */
	private function incDataOrgForUser($dataOrg) {
		$pre = substr($dataOrg, 0, strlen($dataOrg) - 4);
		$seed = intval(substr($dataOrg, - 4)) + 1;
		
		return $pre . str_pad($seed, 4, "0", STR_PAD_LEFT);
	}

	/**
	 * 新增用户
	 */
	public function addUser($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$loginName = $params["loginName"];
		$name = $params["name"];
		$orgCode = $params["orgCode"];
		$orgId = $params["orgId"];
		$enabled = $params["enabled"];
		$gender = $params["gender"];
		$birthday = $params["birthday"];
		$idCardNumber = $params["idCardNumber"];
		$tel = $params["tel"];
		$tel02 = $params["tel02"];
		$address = $params["address"];
		
		$py = $params["py"];
		
		// 检查登录名是否被使用
		$sql = "select count(*) as cnt from t_user where login_name = '%s' ";
		$data = $db->query($sql, $loginName);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("登录名 [$loginName] 已经存在");
		}
		
		// 检查组织机构是否存在
		$sql = "select count(*) as cnt from t_org where id = '%s' ";
		$data = $db->query($sql, $orgId);
		$cnt = $data[0]["cnt"];
		if ($cnt != 1) {
			return $this->bad("组织机构不存在");
		}
		
		// 检查编码是否存在
		$sql = "select count(*) as cnt from t_user where org_code = '%s' ";
		$data = $db->query($sql, $orgCode);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码[$orgCode]已经被其他用户使用");
		}
		
		// 新增用户的默认密码
		$password = md5("123456");
		
		// 生成数据域
		$dataOrg = "";
		$sql = "select data_org
					from t_user
					where org_id = '%s'
					order by data_org desc limit 1";
		$data = $db->query($sql, $orgId);
		if ($data) {
			$dataOrg = $this->incDataOrgForUser($data[0]["data_org"]);
		} else {
			$sql = "select data_org from t_org where id = '%s' ";
			$data = $db->query($sql, $orgId);
			if ($data) {
				$dataOrg = $data[0]["data_org"] . "0001";
			} else {
				return $this->bad("组织机构不存在");
			}
		}
		
		$sql = "insert into t_user (id, login_name, name, org_code, org_id, enabled, password, py,
					gender, birthday, id_card_number, tel, tel02, address, data_org)
					values ('%s', '%s', '%s', '%s', '%s', %d, '%s', '%s',
					'%s', '%s', '%s', '%s', '%s', '%s', '%s') ";
		$rc = $db->execute($sql, $id, $loginName, $name, $orgCode, $orgId, $enabled, $password, $py, 
				$gender, $birthday, $idCardNumber, $tel, $tel02, $address, $dataOrg);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 做类似这种增长 '0101' => '0102'，组织机构的数据域+1
	 */
	private function incDataOrg($dataOrg) {
		$pre = substr($dataOrg, 0, strlen($dataOrg) - 2);
		$seed = intval(substr($dataOrg, - 2)) + 1;
		
		return $pre . str_pad($seed, 2, "0", STR_PAD_LEFT);
	}

	/**
	 * 修改用户
	 */
	public function updateUser($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$loginName = $params["loginName"];
		$name = $params["name"];
		$orgCode = $params["orgCode"];
		$orgId = $params["orgId"];
		$enabled = $params["enabled"];
		$gender = $params["gender"];
		$birthday = $params["birthday"];
		$idCardNumber = $params["idCardNumber"];
		$tel = $params["tel"];
		$tel02 = $params["tel02"];
		$address = $params["address"];
		
		$py = $params["py"];
		
		// 检查登录名是否被使用
		$sql = "select count(*) as cnt from t_user where login_name = '%s' and id <> '%s' ";
		$data = $db->query($sql, $loginName, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("登录名 [$loginName] 已经存在");
		}
		
		// 检查组织机构是否存在
		$sql = "select count(*) as cnt from t_org where id = '%s' ";
		$data = $db->query($sql, $orgId);
		$cnt = $data[0]["cnt"];
		if ($cnt != 1) {
			return $this->bad("组织机构不存在");
		}
		
		// 检查编码是否存在
		$sql = "select count(*) as cnt from t_user
					where org_code = '%s' and id <> '%s' ";
		$data = $db->query($sql, $orgCode, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码[$orgCode]已经被其他用户使用");
		}
		
		$sql = "select org_id, data_org from t_user where id = '%s'";
		$data = $db->query($sql, $id);
		$oldOrgId = $data[0]["org_id"];
		$dataOrg = $data[0]["data_org"];
		if ($oldOrgId != $orgId) {
			// 修改了用户的组织机构， 这个时候要调整数据域
			$sql = "select data_org from t_user
						where org_id = '%s'
						order by data_org desc limit 1";
			$data = $db->query($sql, $orgId);
			if ($data) {
				$dataOrg = $this->incDataOrg($data[0]["data_org"]);
			} else {
				$sql = "select data_org from t_org where id = '%s' ";
				$data = $db->query($sql, $orgId);
				$dataOrg = $data[0]["data_org"] . "0001";
			}
			$sql = "update t_user
					set login_name = '%s', name = '%s', org_code = '%s',
					    org_id = '%s', enabled = %d, py = '%s',
					    gender = '%s', birthday = '%s', id_card_number = '%s',
					    tel = '%s', tel02 = '%s', address = '%s', data_org = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $loginName, $name, $orgCode, $orgId, $enabled, $py, $gender, 
					$birthday, $idCardNumber, $tel, $tel02, $address, $dataOrg, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		} else {
			$sql = "update t_user
					set login_name = '%s', name = '%s', org_code = '%s',
					    org_id = '%s', enabled = %d, py = '%s',
					    gender = '%s', birthday = '%s', id_card_number = '%s',
					    tel = '%s', tel02 = '%s', address = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $loginName, $name, $orgCode, $orgId, $enabled, $py, $gender, 
					$birthday, $idCardNumber, $tel, $tel02, $address, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 操作成功
		return null;
	}
}