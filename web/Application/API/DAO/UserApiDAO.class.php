<?php

namespace API\DAO;

use Home\DAO\PSIBaseExDAO;

/**
 * 用户 API DAO
 *
 * @author 李静波
 */
class UserApiDAO extends PSIBaseExDAO {

	public function doLogin($params) {
		$loginName = $params["loginName"];
		$password = $params["password"];
		
		$db = $this->db;
		
		$sql = "select id from t_user where login_name = '%s' and password = '%s' and enabled = 1";
		
		$data = $db->query($sql, $loginName, md5($password));
		
		$result = [];
		
		if ($data) {
			return $data[0]["id"];
		} else {
			return null;
		}
	}
}