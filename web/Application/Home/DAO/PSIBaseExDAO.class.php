<?php

namespace Home\DAO;

/**
 * 基础 DAO
 *
 * @author 李静波
 */
class PSIBaseExDAO extends PSIBaseDAO {
	protected $db;

	function __construct($db) {
		if ($db == null) {
			$db = M();
		}
		
		$this->db = $db;
	}
	
	protected function newId(){
		$idGen = new IdGenDAO($this->db);
		return $idGen->newId();
	}
}