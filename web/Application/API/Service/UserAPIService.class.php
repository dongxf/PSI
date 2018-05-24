<?php

namespace API\Service;

use API\DAO\UserApiDAO;

/**
 * 用户Service
 *
 * @author 李静波
 */
class UserAPIService extends PSIApiBaseService {

	public function doLogin($params) {
		$dao = new UserApiDAO($this->db());
		
		$userId = $dao->doLogin($params);
		if ($userId) {
			$result = $this->ok();
			
			$tokenId = session_id();
			session($tokenId, $userId);
			
			$result["tokneId"] = $tokenId;
			
			return $result;
		} else {
			return $this->bad("用户名或密码错误");
		}
	}
}