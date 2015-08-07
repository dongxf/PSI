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
		foreach ( $data as $i => $v ) {
			$result[$i]["warehouseName"] = $v["name"];
			$warehouseId = $v["id"];
			
			// 库存金额
			$sql = "select sum(balance_money) as balance_money 
					from t_inventory
					where warehouse_id = '%s' ";
			$d = $db->query($sql, $warehouseId);
			if ($d) {
				$m = $d[0]["balance_money"];
				$result[$i]["inventoryMoney"] = $m ? $m : 0;
			} else {
				$result[$i]["inventoryMoney"] = 0;
			}
			// 低于安全库存数量的商品种类
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

	public function salePortal() {
		$result = array();
		
		$db = M();
		
		// 当月
		$sql = "select year(now()) as y, month(now()) as m";
		$data = $db->query($sql);
		$year = $data[0]["y"];
		$month = $data[0]["m"];
		
		for($i = 0; $i < 6; $i ++) {
			if ($month < 10) {
				$result[$i]["month"] = "$year-0$month";
			} else {
				$result[$i]["month"] = "$year-$month";
			}
			
			$sql = "select sum(w.sale_money) as sale_money, sum(w.profit) as profit
					from t_ws_bill w
					where w.bill_status = 1000
						and year(w.bizdt) = %d
						and month(w.bizdt) = %d";
			$data = $db->query($sql, $year, $month);
			$saleMoney = $data[0]["sale_money"];
			if (! $saleMoney) {
				$saleMoney = 0;
			}
			$profit = $data[0]["profit"];
			if (! $profit) {
				$profit = 0;
			}
			
			// 扣除退货
			$sql = "select sum(s.rejection_sale_money) as rej_sale_money,
						sum(s.profit) as rej_profit
					from t_sr_bill s
					where s.bill_status = 1000
						and year(s.bizdt) = %d
						and month(s.bizdt) = %d";
			$data = $db->query($sql, $year, $month);
			$rejSaleMoney = $data[0]["rej_sale_money"];
			if (! $rejSaleMoney) {
				$rejSaleMoney = 0;
			}
			$rejProfit = $data[0]["rej_profit"];
			if (! $rejProfit) {
				$rejProfit = 0;
			}
			
			$saleMoney -= $rejSaleMoney;
			$profit += $rejProfit; // 这里是+号，因为$rejProfit是负数
			
			$result[$i]["saleMoney"] = $saleMoney;
			$result[$i]["profit"] = $profit;
			
			if ($saleMoney != 0) {
				$result[$i]["rate"] = sprintf("%0.2f", $profit / $saleMoney * 100) . "%";
			} else {
				$result[$i]["rate"] = "";
			}
			
			// 获得上个月
			if ($month == 1) {
				$month = 12;
				$year -= 1;
			} else {
				$month -= 1;
			}
		}
		
		return $result;
	}
}