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
		
		$sql = "select b.id, b.sub_goods_count,g.id as goods_id,
					g.code, g.name, g.spec, u.name as unit_name
				from t_goods_bom b, t_goods g, t_goods_unit u
				where b.goods_id = '%s' and b.sub_goods_id = g.id and g.unit_id = u.id
				order by g.code";
		$data = $db->query($sql, $id);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["goodsId"] = $v["goods_id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["goodsCount"] = $v["sub_goods_count"];
		}
		
		return $result;
	}

	/**
	 * 检查子商品是否形成了循环引用
	 *
	 * @param string $id
	 *        	商品id
	 * @param string $subGoodsId
	 *        	子商品id
	 * @return array|NULL
	 */
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
		
		$sql = "select count(*) as cnt 
				from t_goods_bom
				where goods_id = '%s' and sub_goods_id = '%s' ";
		$data = $db->query($sql, $id, $subGoodsId);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("子商品已经存在，不能再新增");
		}
		
		$sql = "insert into t_goods_bom(id, goods_id, sub_goods_id, sub_goods_count, parent_id)
				values ('%s', '%s', '%s', %d, null)";
		$rc = $db->execute($sql, $this->newId(), $id, $subGoodsId, $subGoodsCount);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
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
		
		$sql = "update t_goods_bom
				set sub_goods_count = %d
				where goods_id = '%s' and sub_goods_id = '%s' ";
		
		$rc = $db->execute($sql, $subGoodsCount, $id, $subGoodsId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	public function getSubGoodsInfo($params) {
		$goodsId = $params["goodsId"];
		$subGoodsId = $params["subGoodsId"];
		
		$db = $this->db;
		
		$goodsDAO = new GoodsDAO($db);
		$goods = $goodsDAO->getGoodsById($goodsId);
		if (! $goods) {
			return $this->badParam("goodsId");
		}
		$subGoods = $goodsDAO->getGoodsById($subGoodsId);
		if (! $subGoods) {
			return $this->badParam("subGoodsId: $subGoodsId ");
		}
		
		$sql = "select sub_goods_count
				from t_goods_bom
				where goods_id = '%s' and sub_goods_id = '%s' ";
		$data = $db->query($sql, $goodsId, $subGoodsId);
		$subGoodsCount = 0;
		if ($data) {
			$subGoodsCount = $data[0]["sub_goods_count"];
		}
		
		$sql = "select u.name
				from t_goods g, t_goods_unit u
				where g.unit_id = u.id and g.id = '%s' ";
		$data = $db->query($sql, $subGoodsId);
		$unitName = "";
		if ($data) {
			$unitName = $data[0]["name"];
		}
		
		return array(
				"success" => true,
				"count" => $subGoodsCount,
				"name" => $subGoods["name"],
				"spec" => $subGoods["spec"],
				"code" => $subGoods["code"],
				"unitName" => $unitName
		);
	}

	/**
	 * 删除商品构成中的子商品
	 *
	 * @param array $params        	
	 * @return null|array
	 */
	public function deleteGoodsBOM(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$sql = "select goods_id, sub_goods_id
				from t_goods_bom
				where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要删除的子商品不存在");
		}
		$goodsId = $data[0]["goods_id"];
		$subGoodsId = $data[0]["sub_goods_id"];
		$goodsDAO = new GoodsDAO($db);
		$goods = $goodsDAO->getGoodsById($goodsId);
		if (! $goods) {
			return $this->badParam("goodsId");
		}
		$subGoods = $goodsDAO->getGoodsById($subGoodsId);
		if (! $subGoods) {
			return $this->badParam("subGoodsId");
		}
		
		$sql = "delete from t_goods_bom where id = '%s' ";
		
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["goodsCode"] = $goods["code"];
		$params["goodsName"] = $goods["name"];
		$params["goodsSpec"] = $goods["spec"];
		$params["subGoodsCode"] = $subGoods["code"];
		$params["subGoodsName"] = $subGoods["name"];
		$params["subGoodsSpec"] = $subGoods["spec"];
		
		// 操作成功
		return null;
	}
}