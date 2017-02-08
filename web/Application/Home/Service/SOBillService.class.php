<?php

namespace Home\Service;

use Home\DAO\SOBillDAO;

/**
 * 销售订单Service
 *
 * @author 李静波
 */
class SOBillService extends PSIBaseExService {
	private $LOG_CATEGORY = "销售订单";

	/**
	 * 获得销售订单主表信息列表
	 */
	public function sobillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		$dao = new SOBillDAO();
		return $dao->sobillList($params);
	}

	/**
	 * 获得销售订单的信息
	 */
	public function soBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		$id = $params["id"];
		
		$result = array();
		
		$cs = new BizConfigService();
		$result["taxRate"] = $cs->getTaxRate();
		
		$db = M();
		
		if ($id) {
			// 编辑销售订单
			$sql = "select s.ref, s.deal_date, s.deal_address, s.customer_id,
						c.name as customer_name, s.contact, s.tel, s.fax,
						s.org_id, o.full_name, s.biz_user_id, u.name as biz_user_name,
						s.receiving_type, s.bill_memo, s.bill_status
					from t_so_bill s, t_customer c, t_user u, t_org o
					where s.id = '%s' and s.customer_Id = c.id
						and s.biz_user_id = u.id
						and s.org_id = o.id";
			$data = $db->query($sql, $id);
			if ($data) {
				$v = $data[0];
				$result["ref"] = $v["ref"];
				$result["dealDate"] = $this->toYMD($v["deal_date"]);
				$result["dealAddress"] = $v["deal_address"];
				$result["customerId"] = $v["customer_id"];
				$result["customerName"] = $v["customer_name"];
				$result["contact"] = $v["contact"];
				$result["tel"] = $v["tel"];
				$result["fax"] = $v["fax"];
				$result["orgId"] = $v["org_id"];
				$result["orgFullName"] = $v["full_name"];
				$result["bizUserId"] = $v["biz_user_id"];
				$result["bizUserName"] = $v["biz_user_name"];
				$result["receivingType"] = $v["receiving_type"];
				$result["billMemo"] = $v["bill_memo"];
				$result["billStatus"] = $v["bill_status"];
				
				// 明细表
				$sql = "select s.id, s.goods_id, g.code, g.name, g.spec, s.goods_count, s.goods_price, s.goods_money,
					s.tax_rate, s.tax, s.money_with_tax, u.name as unit_name
				from t_so_bill_detail s, t_goods g, t_goods_unit u
				where s.sobill_id = '%s' and s.goods_id = g.id and g.unit_id = u.id
				order by s.show_order";
				$items = array();
				$data = $db->query($sql, $id);
				
				foreach ( $data as $i => $v ) {
					$items[$i]["goodsId"] = $v["goods_id"];
					$items[$i]["goodsCode"] = $v["code"];
					$items[$i]["goodsName"] = $v["name"];
					$items[$i]["goodsSpec"] = $v["spec"];
					$items[$i]["goodsCount"] = $v["goods_count"];
					$items[$i]["goodsPrice"] = $v["goods_price"];
					$items[$i]["goodsMoney"] = $v["goods_money"];
					$items[$i]["taxRate"] = $v["tax_rate"];
					$items[$i]["tax"] = $v["tax"];
					$items[$i]["moneyWithTax"] = $v["money_with_tax"];
					$items[$i]["unitName"] = $v["unit_name"];
				}
				
				$result["items"] = $items;
			}
		} else {
			// 新建销售订单
			$us = new UserService();
			$result["bizUserId"] = $us->getLoginUserId();
			$result["bizUserName"] = $us->getLoginUserName();
			
			$sql = "select o.id, o.full_name
					from t_org o, t_user u
					where o.id = u.org_id and u.id = '%s' ";
			$data = $db->query($sql, $us->getLoginUserId());
			if ($data) {
				$result["orgId"] = $data[0]["id"];
				$result["orgFullName"] = $data[0]["full_name"];
			}
			
			// 默认收款方式
			$bc = new BizConfigService();
			$result["receivingType"] = $bc->getSOBillDefaultReceving();
		}
		
		return $result;
	}

	/**
	 * 新增或编辑销售订单
	 */
	public function editSOBill($json) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$db = M();
		
		$db->startTrans();
		
		$dao = new SOBillDAO($db);
		
		$id = $bill["id"];
		
		$log = null;
		
		if ($id) {
			// 编辑
			
			$bill["loginUserId"] = $this->getLoginUserId();
			
			$rc = $dao->updateSOBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			
			$log = "编辑销售订单，单号：{$ref}";
		} else {
			// 新建销售订单
			
			$bill["loginUserId"] = $this->getLoginUserId();
			$bill["dataOrg"] = $this->getLoginUserDataOrg();
			$bill["companyId"] = $this->getCompanyId();
			
			$rc = $dao->addSOBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $bill["id"];
			$ref = $bill["ref"];
			
			$log = "新建销售订单，单号：{$ref}";
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 获得销售订单的明细信息
	 */
	public function soBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new SOBillDAO();
		return $dao->soBillDetailList($params);
	}

	/**
	 * 删除销售订单
	 */
	public function deleteSOBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$db = M();
		
		$db->startTrans();
		
		$dao = new SOBillDAO($db);
		$rc = $dao->deleteSOBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$ref = $params["ref"];
		$log = "删除销售订单，单号：{$ref}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 审核销售订单
	 */
	public function commitSOBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$db = M();
		
		$db->startTrans();
		
		$sql = "select ref, bill_status from t_so_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要审核的销售订单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus > 0) {
			$db->rollback();
			return $this->bad("销售订单(单号：$ref)已经被审核，不能再次审核");
		}
		
		$sql = "update t_so_bill
					set bill_status = 1000,
						confirm_user_id = '%s',
						confirm_date = now()
					where id = '%s' ";
		$us = new UserService();
		$rc = $db->execute($sql, $us->getLoginUserId(), $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 记录业务日志
		$log = "审核销售订单，单号：{$ref}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 取消销售订单审核
	 */
	public function cancelConfirmSOBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$db = M();
		
		$db->startTrans();
		
		$sql = "select ref, bill_status from t_so_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要取消审核的销售订单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus > 1000) {
			$db->rollback();
			return $this->bad("销售订单(单号:{$ref})不能取消审核");
		}
		
		$sql = "select count(*) as cnt from t_so_ws where so_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("销售订单(单号:{$ref})已经生成了销售出库单，不能取消审核");
		}
		
		$sql = "update t_so_bill
					set bill_status = 0, confirm_user_id = null, confirm_date = null
					where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 记录业务日志
		$log = "取消审核销售订单，单号：{$ref}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}