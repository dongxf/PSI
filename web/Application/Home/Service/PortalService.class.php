<?php

namespace Home\Service;

/**
 * Portal Service
 *
 * @author 李静波
 */
class PortalService extends PSIBaseService {

	public function inventoryPortal() {
		$result = array();
		
		$db = M();
		$sql = "select id, name 
				from t_warehouse 
				where inited = 1
				order by code";
		$data = $db->query($sql);
		foreach ($data as $i => $v) {
			$result[$i]["warehouseName"] = $v["name"];
			$warehouseId = $v["id"];
			
			// 库存金额
			$sql = "select sum(balance_money) as balance_money 
					from t_inventory
					where warehouse_id = '%s' ";
			$d = $db->query($sql, $warehouseId);
			if ($d) {
				$result[$i]["inventoryMoney"] = $d[0]["balance_money"];
			} else {
				$result[$i]["inventoryMoney"] = 0;
			}
			//低于安全库存数量的商品种类
			$sql = "select count(*) as cnt
					from t_inventory i, t_goods_si s
					where i.goods_id = s.goods_id and i.warehouse_id = s.warehouse_id
						and s.safety_inventory > i.balance_count
						and i.warehouse_id = '%s' ";
			$d = $db->query($sql, $warehouseId);
			$result[$i]["siCount"] = $d[0]["cnt"];
		}
		
		return $result;
	}
}