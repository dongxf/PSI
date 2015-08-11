<?php

namespace Home\Service;

/**
 * 销售报表Service
 *
 * @author 李静波
 */
class SaleReportService extends PSIBaseService {

	public function saleDayByGoodsQueryData($params) {
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$dt = $params["dt"];
		
		$result = array();
		
		$db = M();
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name
				from t_goods g, t_goods_unit u
				where g.unit_id = u.id and g.id in(
					select distinct d.goods_id
					from t_ws_bill w, t_ws_bill_detail d
					where w.id = d.wsbill_id and w.bizdt = '%s' and w.bill_status = 1000
					union
					select distinct d.goods_id
					from t_sr_bill s, t_sr_bill_detail d
					where s.id = d.srbill_id and s.bizdt = '%s' and s.bill_status = 1000
					)
				order by g.code
				limit %d, %d";
		$items = $db->query($sql, $dt, $dt, $start, $limit);
		foreach ( $items as $i => $v ) {
			$result[$i]["bizDT"] = $dt;
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			
			$goodsId = $v["id"];
			$sql = "select sum(d.goods_money) as goods_money, sum(d.inventory_money) as inventory_money,
						sum(d.goods_count) as goods_count
					from t_ws_bill w, t_ws_bill_detail d
					where w.id = d.wsbill_id and w.bizdt = '%s' and d.goods_id = '%s' 
						and w.bill_status = 1000";
			$data = $db->query($sql, $dt, $goodsId);
			$saleCount = $data[0]["goods_count"];
			if (! $saleCount) {
				$saleCount = 0;
			}
			$saleMoney = $data[0]["goods_money"];
			if (! $saleMoney) {
				$saleMoney = 0;
			}
			$saleInventoryMoney = $data[0]["inventory_money"];
			if (! $saleInventoryMoney) {
				$saleInventoryMoney = 0;
			}
			$result[$i]["saleMoney"] = $saleMoney;
			$result[$i]["saleCount"] = $saleCount;
			
			$sql = "select sum(d.rejection_goods_count) as rej_count, 
						sum(d.rejection_sale_money) as rej_money,
						sum(d.inventory_money) as rej_inventory_money
					from t_sr_bill s, t_sr_bill_detail d
					where s.id = d.srbill_id and s.bizdt = '%s' and d.goods_id = '%s' 
						and s.bill_status = 1000 ";
			$data = $db->query($sql, $dt, $goodsId);
			$rejCount = $data[0]["rej_count"];
			if (! $rejCount) {
				$rejCount = 0;
			}
			$rejSaleMoney = $data[0]["rej_money"];
			if (! $rejSaleMoney) {
				$rejSaleMoney = 0;
			}
			$rejInventoryMoney = $data[0]["rej_inventory_money"];
			if (! $rejInventoryMoney) {
				$rejInventoryMoney = 0;
			}
			
			$result[$i]["rejCount"] = $rejCount;
			$result[$i]["rejMoney"] = $rejSaleMoney;
			
			$c = $saleCount - $rejCount;
			$m = $saleMoney - $rejSaleMoney;
			$result[$i]["c"] = $c;
			$result[$i]["m"] = $m;
			$profit = $saleMoney - $rejSaleMoney - $saleInventoryMoney + $rejInventoryMoney;
			$result[$i]["profit"] = $profit;
			if ($m > 0) {
				$result[$i]["rate"] = sprintf("%0.2f", $profit / $m * 100) . "%";
			}
		}
		
		$sql = "select count(*) as cnt
				from t_goods g, t_goods_unit u
				where g.unit_id = u.id and g.id in(
					select distinct d.goods_id
					from t_ws_bill w, t_ws_bill_detail d
					where w.id = d.wsbill_id and w.bizdt = '%s' and w.bill_status = 1000
					union
					select distinct d.goods_id
					from t_sr_bill s, t_sr_bill_detail d
					where s.id = d.srbill_id and s.bizdt = '%s' and s.bill_status = 1000
					)
				";
		$data = $db->query($sql, $dt, $dt);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	public function saleDayByGoodsSummaryQueryData($params) {
		$dt = $params["dt"];
		
		$result = array();
		$result[0]["bizDT"] = $dt;
		
		$db = M();
		$sql = "select sum(d.goods_money) as goods_money, sum(d.inventory_money) as inventory_money
					from t_ws_bill w, t_ws_bill_detail d
					where w.id = d.wsbill_id and w.bizdt = '%s' 
						and w.bill_status = 1000";
		$data = $db->query($sql, $dt);
		$saleMoney = $data[0]["goods_money"];
		if (! $saleMoney) {
			$saleMoney = 0;
		}
		$saleInventoryMoney = $data[0]["inventory_money"];
		if (! $saleInventoryMoney) {
			$saleInventoryMoney = 0;
		}
		$result[0]["saleMoney"] = $saleMoney;
		
		$sql = "select  sum(d.rejection_sale_money) as rej_money,
						sum(d.inventory_money) as rej_inventory_money
					from t_sr_bill s, t_sr_bill_detail d
					where s.id = d.srbill_id and s.bizdt = '%s' 
						and s.bill_status = 1000 ";
		$data = $db->query($sql, $dt);
		$rejSaleMoney = $data[0]["rej_money"];
		if (! $rejSaleMoney) {
			$rejSaleMoney = 0;
		}
		$rejInventoryMoney = $data[0]["rej_inventory_money"];
		if (! $rejInventoryMoney) {
			$rejInventoryMoney = 0;
		}
		
		$result[0]["rejMoney"] = $rejSaleMoney;
		
		$m = $saleMoney - $rejSaleMoney;
		$result[0]["m"] = $m;
		$profit = $saleMoney - $rejSaleMoney - $saleInventoryMoney + $rejInventoryMoney;
		$result[0]["profit"] = $profit;
		if ($m > 0) {
			$result[0]["rate"] = sprintf("%0.2f", $profit / $m * 100) . "%";
		}
		
		return $result;
	}
}