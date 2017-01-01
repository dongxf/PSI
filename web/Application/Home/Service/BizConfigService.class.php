<?php

namespace Home\Service;

use Home\Common\FIdConst;
use Home\DAO\BizConfigDAO;

/**
 * 业务设置Service
 *
 * @author 李静波
 */
class BizConfigService extends PSIBaseService {
	private $LOG_CATEGORY = "业务设置";

	/**
	 * 返回所有的配置项
	 */
	public function allConfigs($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new BizConfigDAO();
		
		return $dao->allConfigs($params);
	}

	private function getDefaultConfig() {
		return array(
				array(
						"id" => "9000-01",
						"name" => "公司名称",
						"value" => "",
						"note" => "",
						"showOrder" => 100
				),
				array(
						"id" => "9000-02",
						"name" => "公司地址",
						"value" => "",
						"note" => "",
						"showOrder" => 101
				),
				array(
						"id" => "9000-03",
						"name" => "公司电话",
						"value" => "",
						"note" => "",
						"showOrder" => 102
				),
				array(
						"id" => "9000-04",
						"name" => "公司传真",
						"value" => "",
						"note" => "",
						"showOrder" => 103
				),
				array(
						"id" => "9000-05",
						"name" => "公司邮编",
						"value" => "",
						"note" => "",
						"showOrder" => 104
				),
				array(
						"id" => "2001-01",
						"name" => "采购入库默认仓库",
						"value" => "",
						"note" => "",
						"showOrder" => 200
				),
				array(
						"id" => "2001-02",
						"name" => "采购订单默认付款方式",
						"value" => "0",
						"note" => "",
						"showOrder" => 201
				),
				array(
						"id" => "2001-03",
						"name" => "采购入库单默认付款方式",
						"value" => "0",
						"note" => "",
						"showOrder" => 202
				),
				array(
						"id" => "2002-02",
						"name" => "销售出库默认仓库",
						"value" => "",
						"note" => "",
						"showOrder" => 300
				),
				array(
						"id" => "2002-01",
						"name" => "销售出库单允许编辑销售单价",
						"value" => "0",
						"note" => "当允许编辑的时候，还需要给用户赋予权限[销售出库单允许编辑销售单价]",
						"showOrder" => 301
				),
				array(
						"id" => "2002-03",
						"name" => "销售出库单默认收款方式",
						"value" => "0",
						"note" => "",
						"showOrder" => 302
				),
				array(
						"id" => "2002-04",
						"name" => "销售订单默认收款方式",
						"value" => "0",
						"note" => "",
						"showOrder" => 303
				),
				array(
						"id" => "1003-02",
						"name" => "存货计价方法",
						"value" => "0",
						"note" => "",
						"showOrder" => 401
				),
				array(
						"id" => "9001-01",
						"name" => "增值税税率",
						"value" => "17",
						"note" => "",
						"showOrder" => 501
				),
				array(
						"id" => "9002-01",
						"name" => "产品名称",
						"value" => "PSI",
						"note" => "",
						"showOrder" => 0
				),
				array(
						"id" => "9003-01",
						"name" => "采购订单单号前缀",
						"value" => "PO",
						"note" => "",
						"showOrder" => 601
				),
				array(
						"id" => "9003-02",
						"name" => "采购入库单单号前缀",
						"value" => "PW",
						"note" => "",
						"showOrder" => 602
				),
				array(
						"id" => "9003-03",
						"name" => "采购退货出库单单号前缀",
						"value" => "PR",
						"note" => "",
						"showOrder" => 603
				),
				array(
						"id" => "9003-04",
						"name" => "销售出库单单号前缀",
						"value" => "WS",
						"note" => "",
						"showOrder" => 604
				),
				array(
						"id" => "9003-05",
						"name" => "销售退货入库单单号前缀",
						"value" => "SR",
						"note" => "",
						"showOrder" => 605
				),
				array(
						"id" => "9003-06",
						"name" => "调拨单单号前缀",
						"value" => "IT",
						"note" => "",
						"showOrder" => 606
				),
				array(
						"id" => "9003-07",
						"name" => "盘点单单号前缀",
						"value" => "IC",
						"note" => "",
						"showOrder" => 607
				),
				array(
						"id" => "9003-08",
						"name" => "销售订单单号前缀",
						"value" => "SO",
						"note" => "",
						"showOrder" => 608
				)
		);
	}

	/**
	 * 返回所有的配置项，附带着附加数据集
	 */
	public function allConfigsWithExtData($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new BizConfigDAO();
		
		return $dao->allConfigsWithExtData($params);
	}

	/**
	 * 保存配置项
	 */
	public function edit($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		
		$db->startTrans();
		
		$params["isDemo"] = $this->isDemo();
		
		$dao = new BizConfigDAO($db);
		$rc = $dao->edit($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$db->commit();
		
		return $this->ok();
	}

	private function getPOBillPaymentName($id) {
		switch ($id) {
			case "0" :
				return "记应付账款";
			case "1" :
				return "现金付款";
			case "2" :
				return "预付款";
		}
		
		return "";
	}

	private function getPWBillPaymentName($id) {
		switch ($id) {
			case "0" :
				return "记应付账款";
			case "1" :
				return "现金付款";
			case "2" :
				return "预付款";
		}
		
		return "";
	}

	private function getWSBillRecevingName($id) {
		switch ($id) {
			case "0" :
				return "记应收账款";
			case "1" :
				return "现金收款";
			case "2" :
				return "用预收款支付";
		}
		
		return "";
	}

	private function getSOBillRecevingName($id) {
		switch ($id) {
			case "0" :
				return "记应收账款";
			case "1" :
				return "现金收款";
		}
		
		return "";
	}

	private function getWarehouseName($id) {
		$data = M()->query("select name from t_warehouse where id = '%s' ", $id);
		if ($data) {
			return $data[0]["name"];
		} else {
			return "[没有设置]";
		}
	}

	/**
	 * 获得增值税税率
	 */
	public function getTaxRate() {
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$sql = "select value from t_config 
				where id = '9001-01' and company_id = '%s' ";
		$data = $db->query($sql, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			return intval($result);
		} else {
			return 17;
		}
	}

	/**
	 * 获得本产品名称，默认值是：PSI
	 */
	public function getProductionName() {
		$defaultName = "PSI";
		
		$db = M();
		if (! $this->columnExists($db, "t_config", "company_id")) {
			// 兼容旧代码
			return $defaultName;
		}
		
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$sql = "select value from t_config 
				where id = '9002-01' and company_id = '%s' ";
		$data = $db->query($sql, $companyId);
		if ($data) {
			return $data[0]["value"];
		} else {
			// 登录页面的时候，并不知道company_id的值
			$sql = "select value from t_config
				where id = '9002-01' ";
			$data = $db->query($sql);
			if ($data) {
				return $data[0]["value"];
			}
			
			return $defaultName;
		}
	}

	/**
	 * 获得存货计价方法
	 * 0： 移动平均法
	 * 1：先进先出法
	 */
	public function getInventoryMethod() {
		// 2015-11-19 为发布稳定版本，临时取消先进先出法
		$result = 0;
		
		// $db = M();
		// $sql = "select value from t_config where id = '1003-02' ";
		// $data = $db->query($sql);
		// if (! $data) {
		// return $result;
		// }
		
		// $result = intval($data[0]["value"]);
		
		return $result;
	}

	/**
	 * 获得采购订单单号前缀
	 */
	public function getPOBillRefPre() {
		$result = "PO";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-01";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "PO";
			}
		}
		
		return $result;
	}

	/**
	 * 获得采购入库单单号前缀
	 */
	public function getPWBillRefPre() {
		$result = "PW";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-02";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "PW";
			}
		}
		
		return $result;
	}

	/**
	 * 获得采购退货出库单单号前缀
	 */
	public function getPRBillRefPre() {
		$result = "PR";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-03";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "PR";
			}
		}
		
		return $result;
	}

	/**
	 * 获得销售出库单单号前缀
	 */
	public function getWSBillRefPre() {
		$result = "WS";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-04";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "WS";
			}
		}
		
		return $result;
	}

	/**
	 * 获得销售退货入库单单号前缀
	 */
	public function getSRBillRefPre() {
		$result = "SR";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-05";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "SR";
			}
		}
		
		return $result;
	}

	/**
	 * 获得调拨单单号前缀
	 */
	public function getITBillRefPre() {
		$result = "IT";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-06";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "IT";
			}
		}
		
		return $result;
	}

	/**
	 * 获得盘点单单号前缀
	 */
	public function getICBillRefPre() {
		$result = "IC";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-07";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "IC";
			}
		}
		
		return $result;
	}

	/**
	 * 获得当前用户可以设置的公司
	 */
	public function getCompany() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$db = M();
		$result = array();
		
		$us = new UserService();
		
		$companyId = $us->getCompanyId();
		
		$sql = "select id, name
				from t_org
				where (parent_id is null) ";
		$queryParams = array();
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::BIZ_CONFIG, "t_org");
		
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by org_code ";
		
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["name"] = $v["name"];
		}
		
		return $result;
	}

	/**
	 * 获得销售订单单号前缀
	 */
	public function getSOBillRefPre() {
		$result = "PO";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-08";
		$sql = "select value from t_config
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "SO";
			}
		}
		
		return $result;
	}

	/**
	 * 获得采购订单默认付款方式
	 */
	public function getPOBillDefaultPayment() {
		$result = "0";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "2001-02";
		$sql = "select value from t_config
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "0";
			}
		}
		
		return $result;
	}

	/**
	 * 获得采购入库单默认付款方式
	 */
	public function getPWBillDefaultPayment() {
		$result = "0";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "2001-03";
		$sql = "select value from t_config
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "0";
			}
		}
		
		return $result;
	}

	/**
	 * 获得销售出库单默认收款方式
	 */
	public function getWSBillDefaultReceving() {
		$result = "0";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "2002-03";
		$sql = "select value from t_config
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "0";
			}
		}
		
		return $result;
	}

	/**
	 * 获得销售订单默认收款方式
	 */
	public function getSOBillDefaultReceving() {
		$result = "0";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "2002-04";
		$sql = "select value from t_config
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "0";
			}
		}
		
		return $result;
	}
}