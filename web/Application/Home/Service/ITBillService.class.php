<?php

namespace Home\Service;

use Think\Model\AdvModel;

/**
 * 库间调拨Service
 *
 * @author 李静波
 */
class ITBillService extends PSIBaseService {

	/**
	 * 生成新的调拨单单号
	 *
	 * @return string
	 */
	private function genNewBillRef() {
		$pre = "IT";
		$mid = date("Ymd");
		
		$sql = "select ref from t_it_bill where ref like '%s' order by ref desc limit 1";
		$data = M()->query($sql, $pre . $mid . "%");
		$sufLength = 3;
		$suf = str_pad("1", $sufLength, "0", STR_PAD_LEFT);
		if ($data) {
			$ref = $data[0]["ref"];
			$nextNumber = intval(substr($ref, 10)) + 1;
			$suf = str_pad($nextNumber, $sufLength, "0", STR_PAD_LEFT);
		}
		
		return $pre . $mid . $suf;
	}

	/**
	 * 调拨单主表列表信息
	 */
	public function itbillList($params) {
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		
		$sql = "select t.id, t.ref, t.bizdt, t.bill_status,
					fw.name as from_warehouse_name,
					tw.name as to_warehouse_name,
					u.name as biz_user_name,
					u1.name as input_user_name
				from t_it_bill t, t_warehouse fw, t_warehouse tw,
				   t_user u, t_user u1
				where t.from_warehouse_id = fw.id 
				  and t.to_warehouse_id = tw.id
				  and t.biz_user_id = u.id
				  and t.input_user_id = u1.id
				order by t.ref
				limit $start , $limit
				";
		$data = $db->query($sql);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["bizdt"]));
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待调拨" : "已调拨";
			$result[$i]["fromWarehouseName"] = $v["from_warehouse_name"];
			$result[$i]["toWarehouseName"] = $v["to_warehouse_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
		}
		
		$sql = "select count(*) as cnt
				from t_it_bill t, t_warehouse fw, t_warehouse tw,
				   t_user u, t_user u1
				where t.from_warehouse_id = fw.id 
				  and t.to_warehouse_id = tw.id
				  and t.biz_user_id = u.id
				  and t.input_user_id = u1.id
				";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	public function editITBill($params) {
		$json = $params["jsonStr"];
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$id = $bill["id"];
		$bizDT = $bill["bizDT"];
		$fromWarehouseId = $bill["fromWarehouseId"];
		$toWarehouseId = $bill["toWarehouseId"];
		$bizUserId = $bill["bizUserId"];
		
		$items = $bill["items"];
		
		$idGen = new IdGenService();
		$us = new UserService();
		
		$db = M();
		
		if ($id) {
			// 编辑
			$sql = "select ref from t_it_bill where id = '%s' ";
			$data = $db->query($sql, $id);
			if (! $data) {
				return $this->bad("要编辑的调拨单不存在");
			}
			
			$db->startTrans();
			try {
				$sql = "update t_it_bill
						set bizdt = '%s', biz_user_id = '%s', date_created = now(),
						    input_user_id = '%s', from_warehouse_id = '%s', to_warehouse_id = '%s'
						where id = '%s' ";
				$id = $idGen->newId();
				$ref = $this->genNewBillRef();
				
				$db->execute($sql, $bizDT, $bizUserId, $us->getLoginUserId(), $fromWarehouseId, 
						$toWarehouseId, $id);
				
				$sql = "delete from t_it_bill_detail where itbill_id = '%s' ";
				$db->execute($sql, $id);
				
				$sql = "insert into t_it_bill_detail(id, date_created, goods_id, goods_count, show_order, itbill_id)
						values ('%s', now(), '%s', %d, %d, '%s')";
				foreach ( $items as $i => $v ) {
					$goodsId = $v["goodsId"];
					$goodsCount = $v["goodsCount"];
					
					$db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $i, $id);
				}
				
				$bs = new BizlogService();
				$log = "编辑调拨单，单号：$ref";
				$bs->insertBizlog($log, "库间调拨");
				
				$db->commit();
			} catch ( Exception $ex ) {
				$db->rollback();
				return $this->bad("数据库错误，请联系系统管理员");
			}
		} else {
			// 新增
			$db->startTrans();
			try {
				$sql = "insert into t_it_bill(id, bill_status, bizdt, biz_user_id,
						date_created, input_user_id, ref, from_warehouse_id, to_warehouse_id)
						values ('%s', 0, '%s', '%s', now(), '%s', '%s', '%s', '%s')";
				$id = $idGen->newId();
				$ref = $this->genNewBillRef();
				
				$db->execute($sql, $id, $bizDT, $bizUserId, $us->getLoginUserId(), $ref, 
						$fromWarehouseId, $toWarehouseId);
				
				$sql = "insert into t_it_bill_detail(id, date_created, goods_id, goods_count, show_order, itbill_id)
						values ('%s', now(), '%s', %d, %d, '%s')";
				foreach ( $items as $i => $v ) {
					$goodsId = $v["goodsId"];
					$goodsCount = $v["goodsCount"];
					
					$db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $i, $id);
				}
				
				$bs = new BizlogService();
				$log = "新建调拨单，单号：$ref";
				$bs->insertBizlog($log, "库间调拨");
				
				$db->commit();
			} catch ( Exception $ex ) {
				$db->rollback();
				return $this->bad("数据库错误，请联系系统管理员");
			}
		}
		
		return $this->ok($id);
	}

	public function itBillInfo($parmas) {
		return array();
	}
}