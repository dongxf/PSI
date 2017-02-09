<?php

namespace Home\DAO;

/**
 * 基础 DAO
 *
 * @author 李静波
 */
class PSIBaseExDAO extends PSIBaseDAO {
	/**
	 *
	 * @var \Think\Model $db
	 */
	protected $db;

	function __construct($db) {
		if ($db == null) {
			$db = M();
		}
		
		$this->db = $db;
	}

	protected function newId() {
		$idGen = new IdGenDAO($this->db);
		return $idGen->newId();
	}

	protected function loginUserIdNotExists($loginUserId) {
		$db = $this->db;
		
		$sql = "select count(*) as cnt from t_user where id = '%s' ";
		$data = $db->query($sql, $loginUserId);
		$cnt = $data[0]["cnt"];
		
		return $cnt != 1;
	}

	protected function dataOrgNotExists($dataOrg) {
		$db = $this->db;
		
		$sql = "select count(*) as cnt from t_user where data_org = '%s' ";
		$data = $db->query($sql, $dataOrg);
		$cnt = $data[0]["cnt"];
		
		return $cnt != 1;
	}

	protected function companyIdNotExists($companyId) {
		$db = $this->db;
		
		$sql = "select count(*) as cnt from t_org where id = '%s' ";
		$data = $db->query($sql, $companyId);
		$cnt = $data[0]["cnt"];
		
		return $cnt != 1;
	}

	/**
	 * 判断日期是否是正确的Y-m-d格式
	 *
	 * @param string $date        	
	 * @return boolean true: 是正确的格式
	 */
	protected function dateIsValid($date) {
		$dt = strtotime($date);
		if (! $dt) {
			return false;
		}
		
		return date("Y-m-d", $dt) == $date;
	}

	protected function emptyResult() {
		return array();
	}

	protected function badParam($param) {
		return $this->bad("参数" . $param . "不正确");
	}
}