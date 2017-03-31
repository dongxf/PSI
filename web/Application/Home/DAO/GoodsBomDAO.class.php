<?php

namespace Home\DAO;

/**
 * 商品构成DAO
 *
 * @author 李静波
 */
class GoodsBomDAO extends PSIBaseExDAO {

	/**
	 * 获得某个商品的商品构成
	 */
	public function goodsBOMList($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$result = array();
		
		$sql = "select b.id, b.sub_goods_count, g.code, g.name, g.spec, u.name as unit_name
				from t_goods_bom b, t_goods g, t_goods_unit u
				where b.goods_id = '%s' and b.sub_goods_id = g.id and g.unit_id = u.id
				order by g.code";
		$data = $db->query($sql, $id);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["goodsCount"] = $v["sub_goods_count"];
		}
		
		return $result;
	}

	private function checkSubGoods($id, $subGoodsId) {
		if ($id == $subGoodsId) {
			return $this->bad("子商品不能是自身");
		}
		
		$db = $this->db;
		// 检查子商品是否形成了循环引用
		// 目前只检查一级
		// TODO 用递归算法检查
		
		$sql = "select id, sub_goods_id
				from t_goods_bom
				where goods_id = '%s' ";
		$data = $db->query($sql, $subGoodsId);
		foreach ( $data as $v ) {
			$sgi = $v["sub_goods_id"];
			if ($id == $sgi) {
				return $this->bad("子商品形成了循环引用");
			}
		}
		
		return null;
	}

	/**
	 * 新增商品构成
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function addGoodsBOM(& $params) {
		// id: 商品id
		$id = $params["id"];
		
		$subGoodsId = $params["subGoodsId"];
		$subGoodsCount = intval($params["subGoodsCount"]);
		
		$db = $this->db;
		
		$goodsDAO = new GoodsDAO($db);
		$goods = $goodsDAO->getGoodsById($id);
		if (! $goods) {
			return $this->bad("商品不存在");
		}
		
		$subGoods = $goodsDAO->getGoodsById($subGoodsId);
		if (! $subGoods) {
			return $this->bad("子商品不存在");
		}
		
		if ($subGoodsCount <= 0) {
			return $this->bad("子商品数量需要大于0");
		}
		
		$rc = $this->checkSubGoods($id, $subGoodsId);
		if ($rc) {
			return $rc;
		}
		
		return null;
	}

	/**
	 * 编辑商品构成
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function updateGoodsBOM($params) {
		return null;
	}
}