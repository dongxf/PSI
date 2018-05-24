<?php

namespace API\Service;

use Home\Service\PSIBaseExService;

/**
 * 用户Service
 *
 * @author 李静波
 */
class PSIApiBaseService extends PSIBaseExService {

	protected function tokenIsInvalid(string $tokenId): bool {
		$userId = session($tokenId);
		if (! $userId) {
			return false;
		}
		
		$db = $this->db();
		$sql = "select count(*) as cnt 
				form t_user
				where id = '%s' and enabled = 1 ";
		$data = $db->query($sql, $userId);
		$cnt = $data[0]["cnt"];
		
		return $cnt == 1;
	}
}