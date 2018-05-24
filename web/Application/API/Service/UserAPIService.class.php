<?php

namespace API\Service;

use Home\Service\PSIBaseExService;
use API\DAO\UserApiDAO;

/**
 * 用户Service
 *
 * @author 李静波
 */
class UserAPIService extends PSIBaseExService {

	public function doLogin($params) {
		$dao = new UserApiDAO($this->db());
		
		$tokenId = $dao->doLogin($params);
		if ($tokenId) {
			$result = $this->ok();
			$result["tokneId"] = $tokenId;
			
			return $result;
		} else {
			return $this->bad("用户名或密码错误");
		}
	}
}