<?php

namespace Home\Service;

/**
 * 销售报表Service
 *
 * @author 李静波
 */
class SaleReportService extends PSIBaseService {

	/**
	 * 销售日报表(按商品汇总) - 查询数据
	 */
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

	private function saleDaySummaryQueryData($params) {
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

	/**
	 * 销售日报表(按商品汇总) - 查询汇总数据
	 */
	public function saleDayByGoodsSummaryQueryData($params) {
		return $this->saleDaySummaryQueryData($params);
	}

	/**
	 * 销售日报表(按客户汇总) - 查询数据
	 */
	public function saleDayByCustomerQueryData($params) {
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$dt = $params["dt"];
		
		$result = array();
		
		$db = M();
		$sql = "select c.id, c.code, c.name
				from t_customer c
				where c.id in(
					select distinct w.customer_id
					from t_ws_bill w
					where w.bizdt = '%s' and w.bill_status = 1000
					union
					select distinct s.customer_id
					from t_sr_bill s
					where s.bizdt = '%s' and s.bill_status = 1000
					)
				order by c.code
				limit %d, %d";
		$items = $db->query($sql, $dt, $dt, $start, $limit);
		foreach ( $items as $i => $v ) {
			$result[$i]["bizDT"] = $dt;
			$result[$i]["customerCode"] = $v["code"];
			$result[$i]["customerName"] = $v["name"];
			
			$customerId = $v["id"];
			$sql = "select sum(w.sale_money) as goods_money, sum(w.inventory_money) as inventory_money
					from t_ws_bill w
					where w.bizdt = '%s' and w.customer_id = '%s'
						and w.bill_status = 1000";
			$data = $db->query($sql, $dt, $customerId);
			$saleMoney = $data[0]["goods_money"];
			if (! $saleMoney) {
				$saleMoney = 0;
			}
			$saleInventoryMoney = $data[0]["inventory_money"];
			if (! $saleInventoryMoney) {
				$saleInventoryMoney = 0;
			}
			$result[$i]["saleMoney"] = $saleMoney;
			
			$sql = "select sum(s.rejection_sale_money) as rej_money,
						sum(s.inventory_money) as rej_inventory_money
					from t_sr_bill s
					where s.bizdt = '%s' and s.customer_id = '%s'
						and s.bill_status = 1000 ";
			$data = $db->query($sql, $dt, $customerId);
			$rejSaleMoney = $data[0]["rej_money"];
			if (! $rejSaleMoney) {
				$rejSaleMoney = 0;
			}
			$rejInventoryMoney = $data[0]["rej_inventory_money"];
			if (! $rejInventoryMoney) {
				$rejInventoryMoney = 0;
			}
			
			$result[$i]["rejMoney"] = $rejSaleMoney;
			
			$m = $saleMoney - $rejSaleMoney;
			$result[$i]["m"] = $m;
			$profit = $saleMoney - $rejSaleMoney - $saleInventoryMoney + $rejInventoryMoney;
			$result[$i]["profit"] = $profit;
			if ($m > 0) {
				$result[$i]["rate"] = sprintf("%0.2f", $profit / $m * 100) . "%";
			}
		}
		
		$sql = "select count(*) as cnt
				from t_customer c
				where c.id in(
					select distinct w.customer_id
					from t_ws_bill w
					where w.bizdt = '%s' and w.bill_status = 1000
					union
					select distinct s.customer_id
					from t_sr_bill s
					where s.bizdt = '%s' and s.bill_status = 1000
					)";
		$data = $db->query($sql, $dt, $dt);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 销售日报表(按客户汇总) - 查询汇总数据
	 */
	public function saleDayByCustomerSummaryQueryData($params) {
		return $this->saleDaySummaryQueryData($params);
	}
	
	/**
	 * 销售日报表(按仓库汇总) - 查询数据
	 */
	public function saleDayByWarehouseQueryData($params) {
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
	
		$dt = $params["dt"];
	
		$result = array();
	
		$db = M();
		$sql = "select w.id, w.code, w.name
				from t_warehouse w
				where w.id in(
					select distinct w.warehouse_id
					from t_ws_bill w
					where w.bizdt = '%s' and w.bill_status = 1000
					union
					select distinct s.warehouse_id
					from t_sr_bill s
					where s.bizdt = '%s' and s.bill_status = 1000
					)
				order by w.code
				limit %d, %d";
		$items = $db->query($sql, $dt, $dt, $start, $limit);
		foreach ( $items as $i => $v ) {
			$result[$i]["bizDT"] = $dt;
			$result[$i]["warehouseCode"] = $v["code"];
			$result[$i]["warehouseName"] = $v["name"];
				
			$warehouseId = $v["id"];
			$sql = "select sum(w.sale_money) as goods_money, sum(w.inventory_money) as inventory_money
					from t_ws_bill w
					where w.bizdt = '%s' and w.warehouse_id = '%s'
						and w.bill_status = 1000";
			$data = $db->query($sql, $dt, $warehouseId);
			$saleMoney = $data[0]["goods_money"];
			if (! $saleMoney) {
				$saleMoney = 0;
			}
			$saleInventoryMoney = $data[0]["inventory_money"];
			if (! $saleInventoryMoney) {
				$saleInventoryMoney = 0;
			}
			$result[$i]["saleMoney"] = $saleMoney;
				
			$sql = "select sum(s.rejection_sale_money) as rej_money,
						sum(s.inventory_money) as rej_inventory_money
					from t_sr_bill s
					where s.bizdt = '%s' and s.warehouse_id = '%s'
						and s.bill_status = 1000 ";
			$data = $db->query($sql, $dt, $warehouseId);
			$rejSaleMoney = $data[0]["rej_money"];
			if (! $rejSaleMoney) {
				$rejSaleMoney = 0;
			}
			$rejInventoryMoney = $data[0]["rej_inventory_money"];
			if (! $rejInventoryMoney) {
				$rejInventoryMoney = 0;
			}
				
			$result[$i]["rejMoney"] = $rejSaleMoney;
				
			$m = $saleMoney - $rejSaleMoney;
			$result[$i]["m"] = $m;
			$profit = $saleMoney - $rejSaleMoney - $saleInventoryMoney + $rejInventoryMoney;
			$result[$i]["profit"] = $profit;
			if ($m > 0) {
				$result[$i]["rate"] = sprintf("%0.2f", $profit / $m * 100) . "%";
			}
		}
	
		$sql = "select count(*) as cnt
				from t_warehouse c
				where c.id in(
					select distinct w.warehouse_id
					from t_ws_bill w
					where w.bizdt = '%s' and w.bill_status = 1000
					union
					select distinct s.warehouse_id
					from t_sr_bill s
					where s.bizdt = '%s' and s.bill_status = 1000
					)";
		$data = $db->query($sql, $dt, $dt);
		$cnt = $data[0]["cnt"];
	
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}
	
	/**
	 * 销售日报表(按仓库汇总) - 查询汇总数据
	 */
	public function saleDayByWarehouseSummaryQueryData($params) {
		return $this->saleDaySummaryQueryData($params);
	}
}