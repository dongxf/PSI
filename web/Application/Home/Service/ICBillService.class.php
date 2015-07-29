<?php

namespace Home\Service;

use Think\Model\AdvModel;

/**
 * 库存盘点Service
 *
 * @author 李静波
 */
class ICBillService extends PSIBaseService {

	/**
	 * 生成新的盘点单单号
	 *
	 * @return string
	 */
	private function genNewBillRef() {
		$pre = "IC";
		$mid = date("Ymd");
		
		$sql = "select ref from t_ic_bill where ref like '%s' order by ref desc limit 1";
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

	public function icBillInfo($params) {
		$id = $params["id"];
		
		$result = array();
		
		if ($id) {
			// 编辑
		} else {
			// 新建
			$us = new UserService();
			$result["bizUserId"] = $us->getLoginUserId();
			$result["bizUserName"] = $us->getLoginUserName();
		}
		
		return $result;
	}

	public function editICBill($params) {
		$json = $params["jsonStr"];
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$db = M();
		
		$id = $bill["id"];
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		$sql = "select name from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			return $this->bad("盘点仓库不存在，无法保存");
		}
		
		$bizUserId = $bill["bizUserId"];
		$sql = "select name from t_user where id = '%s' ";
		$data = $db->query($sql, $bizUserId);
		if (! $data) {
			return $this->bad("业务人员不存在，无法保存");
		}
		
		$items = $bill["items"];
		
		$idGen = new IdGenService();
		$us = new UserService();
		
		if ($id) {
			// 编辑单据
			return $this->todo();
		} else {
			// 新建单据
			$db->startTrans();
			try {
				$id = $idGen->newId();
				$ref = $this->genNewBillRef();
				
				// 主表
				$sql = "insert into t_ic_bill(id, bill_status, bizdt, biz_user_id, date_created, 
							input_user_id, ref, warehouse_id)
						values ('%s', 0, '%s', '%s', now(), '%s', '%s', '%s')";
				$rc = $db->execute($sql, $id, $bizDT, $bizUserId, $us->getLoginUserId(), $ref, 
						$warehouseId);
				if (! $rc) {
					$db->rollback();
					return $this->sqlError();
				}
				
				// 明细表
				$sql = "insert into t_ic_bill_detail(id, date_created, goods_id, goods_count, goods_money,
							show_order, icbill_id)
						values ('%s', now(), '%s', %d, %f, %d, '%s')";
				foreach ( $items as $i => $v ) {
					$goodsId = $v["goodsId"];
					if (! $goodsId) {
						continue;
					}
					$goodsCount = $v["goodsCount"];
					$goodsMoney = $v["goodsMoney"];
					
					$rc = $db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsMoney, 
							$i, $id);
					if (! $rc) {
						$db->rollback();
						return $this->sqlError();
					}
				}
				
				$bs = new BizlogService();
				$log = "新建盘点单，单号：$ref";
				$bs->insertBizlog($log, "库存盘点");
				
				$db->commit();
				
				return $this->ok($id);
			} catch ( Exception $e ) {
				$db->rollback();
				return $this->sqlError();
			}
		}
	}
}