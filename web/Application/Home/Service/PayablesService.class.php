<?php

namespace Home\Service;

/**
 * 应付账款Service
 *
 * @author 李静波
 */
class PayablesService extends PSIBaseService {

	public function payCategoryList($params) {
		$id = $params["id"];
		if ($id == "supplier") {
			return M()->query("select id,  code, name from t_supplier_category order by code");
		} else {
			return M()->query("select id,  code, name from t_customer_category order by code");
		}
	}

	public function payList($params) {
		$caType = $params["caType"];
		$categoryId = $params["categoryId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];

		$db = M();
		if ($caType == "supplier") {
			$sql = "select p.id, p.pay_money, p.act_money, p.balance_money, s.id as ca_id, s.code, s.name "
					. " from t_payables p, t_supplier s"
					. " where p.ca_id = s.id and p.ca_type = 'supplier' and s.category_id = '%s' "
					. " order by s.code "
					. " limit " . $start . ", " . $limit;
			$data = $db->query($sql, $categoryId);
			$result = [];
			foreach ($data as $i => $v) {
				$result[$i]["id"] = $v["id"];
				$result[$i]["caId"] = $v["ca_id"];
				$result[$i]["code"] = $v["code"];
				$result[$i]["name"] = $v["name"];
				$result[$i]["payMoney"] = $v["pay_money"];
				$result[$i]["actMoney"] = $v["act_money"];
				$result[$i]["balanceMoney"] = $v["balance_money"];
			}

			$sql = "select count(*) as cnt from t_payables p, t_supplier s"
					. " where p.ca_id = s.id and p.ca_type = 'supplier' and s.category_id = '%s' ";
			$data = $db->query($sql, $categoryId);
			$cnt = $data[0]["cnt"];

			return array("dataList" => $result, "totalCount" => $cnt);
		} else {
			$sql = "select p.id, p.pay_money, p.act_money, p.balance_money, s.id as ca_id, s.code, s.name "
					. " from t_payables p, t_customer s"
					. " where p.ca_id = s.id and p.ca_type = 'customer' and s.category_id = '%s' "
					. " order by s.code "
					. " limit " . $start . ", " . $limit;
			$data = $db->query($sql, $categoryId);
			$result = [];
			foreach ($data as $i => $v) {
				$result[$i]["id"] = $v["id"];
				$result[$i]["caId"] = $v["ca_id"];
				$result[$i]["code"] = $v["code"];
				$result[$i]["name"] = $v["name"];
				$result[$i]["payMoney"] = $v["pay_money"];
				$result[$i]["actMoney"] = $v["act_money"];
				$result[$i]["balanceMoney"] = $v["balance_money"];
			}

			$sql = "select count(*) as cnt from t_payables p, t_customer s"
					. " where p.ca_id = s.id and p.ca_type = 'customer' and s.category_id = '%s' ";
			$data = $db->query($sql, $categoryId);
			$cnt = $data[0]["cnt"];

			return array("dataList" => $result, "totalCount" => $cnt);
		}
	}

	public function payDetailList($params) {
		$caType = $params["caType"];
		$caId = $params["caId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];

		$db = M();

		$sql = "select id, ref_type, ref_number, pay_money, act_money, balance_money, date_created"
				. " from t_payables_detail "
				. " where ca_type = '%s' and ca_id = '%s' "
				. " limit " . $start . ", " . $limit;
		$data = $db->query($sql, $caType, $caId);
		$result = [];
		foreach ($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["refType"] = $v["ref_type"];
			$result[$i]["refNumber"] = $v["ref_number"];
			$result[$i]["bizDT"] = date("Y-m-d", strtotime($v["date_created"]));
			$result[$i]["payMoney"] = $v["pay_money"];
			$result[$i]["actMoney"] = $v["act_money"];
			$result[$i]["balanceMoney"] = $v["balance_money"];
		}

		$sql = "select count(*) as cnt from t_payables_detail "
				. " where ca_type = '%s' and ca_id = '%s' ";
		$data = $db->query($sql, $caType, $caId);
		$cnt = $data[0]["cnt"];

		return array("dataList" => $result, "totalCount" => $cnt);
	}

	public function payRecordList($params) {
		$refType = $params["refType"];
		$refNumber = $params["refNumber"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];

		$db = M();

		$sql = "select u.name as biz_user_name, bu.name as input_user_name, p.id, "
				. " p.act_money, p.biz_date, p.date_created, p.remark"
				. " from t_payment p, t_user u, t_user bu"
				. " where p.ref_type = '%s' and p.ref_number = '%s' "
				. "          and  p.pay_user_id = u.id and p.input_user_id = bu.id "
				. " limit " . $start . ", " . $limit;
		$data = $db->query($sql, $refType, $refNumber);
		$result = [];
		foreach ($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["actMoney"] = $v["act_money"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["biz_date"]));
			$result[$i]["dateCreated"] = date("Y-m-d", strtotime($v["date_created"]));
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["remark"] = $v["remark"];
		}

		$sql = "select count(*) as cnt from t_payment "
				. " where ref_type = '%s' and ref_number = '%s' ";
		$data = $db->query($sql, $refType, $refNumber);
		$cnt = $data[0]["cnt"];

		return array("dataList" => $result, "totalCount" => 0);
	}

	public function addPayment($params) {
		$refType = $params["refType"];
		$refNumber = $params["refNumber"];
		$bizDT = $params["bizDT"];
		$actMoney = $params["actMoney"];
		$bizUserId = $params["bizUserId"];
		$remark = $params["remark"];
		if (!$remark) {
			$remark = "";
		}

		$db = M();
		$billId = "";
		if ($refType == "采购入库") {
			$sql = "select id from t_pw_bill where ref = '%s' ";
			$data = $db->query($sql, $refNumber);
			if (!$data) {
				return $this->bad("单号为 {$refNumber} 的采购入库不存在，无法付款");
			}
			$billId = $data[0]["id"];
		}

		$db->startTrans();
		try {
			$sql = "insert into t_payment (id, act_money, biz_date, date_created, input_user_id,"
					. "pay_user_id,  bill_id,  ref_type, ref_number, remark) "
					. " values ('%s', %f, '%s', now(), '%s', '%s', '%s', '%s', '%s', '%s')";
			$db->execute($sql, (new IdGenService())->newId(), $actMoney, $bizDT, (new UserService())->getLoginUserId(), $bizUserId, $billId, $refType, $refNumber, $remark);

			$log = "为 {$refType} - 单号：{$refNumber} 付款：{$actMoney}元";
			(new BizlogService())->insertBizlog($log, "应付账款管理");

			// 应付明细账			
			$sql = "select pay_money, act_money, ca_type, ca_id "
					. " from t_payables_detail "
					. " where ref_type = '%s' and ref_number = '%s' ";
			$data = $db->query($sql, $refType, $refNumber);
			$caType = $data[0]["ca_type"];
			$caId = $data[0]["ca_id"];
			$payMoney = $data[0]["pay_money"];
			$actMoneyNew = $data[0]["act_money"];
			$actMoneyNew += $actMoney;
			$balanceMoney = $payMoney - $actMoneyNew;
			$sql = "update t_payables_detail "
					. " set act_money = %f, balance_money = %f "
					. " where ref_type = '%s' and ref_number = '%s' ";
			$db->execute($sql, $actMoneyNew, $balanceMoney, $refType, $refNumber);

			// 应付总账
			$sql = "select pay_money, act_money "
					. " from t_payables "
					. " where ca_type = '%s' and ca_id = '%s' ";
			$data = $db->query($sql, $caType, $caId);
			$payMoneyTotal = $data[0]["pay_money"];
			$actMoneyTotal = $data[0]["act_money"];
			$actMoneyTotal += $actMoney;
			$balanceMoneyTotal = $payMoneyTotal - $actMoneyTotal;
			$sql = "update t_payables"
					. " set act_money = %f, balance_money = %f "
					. " where ca_type = '%s' and ca_id = '%s' ";
			$db->execute($sql, $actMoneyTotal, $balanceMoneyTotal, $caType, $caId);

			$db->commit();
		} catch (Exception $ex) {
			$db->rollback();
			return $this->bad("数据库错误，请联系管理员");
		}

		return $this->ok();
	}

	public function refreshPayInfo($params) {
		$id = $params["id"];
		$data = M()->query("select act_money, balance_money from t_payables "
				. " where id = '%s' ", $id);
		return array("actMoney" => $data[0]["act_money"],
			"balanceMoney" => $data[0]["balance_money"]);
	}

	public function refreshPayDetailInfo($params) {
		$id = $params["id"];
		$data = M()->query("select act_money, balance_money from t_payables_detail "
				. " where id = '%s' ", $id);
		return array("actMoney" => $data[0]["act_money"],
			"balanceMoney" => $data[0]["balance_money"]);
	}
}
