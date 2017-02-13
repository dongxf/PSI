<?php

namespace Home\DAO;

/**
 * ID DAO
 *
 * @author 李静波
 */
class IdGenDAO extends PSIBaseExDAO {

	/**
	 * 创建一个新的UUID
	 */
	public function newId() {
		$db = $this->db;
		
		$data = $db->query("select UUID() as uuid");
		
		return strtoupper($data[0]["uuid"]);
	}
}