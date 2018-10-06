<?php

namespace Home\DAO;

/**
 * 表单视图 DAO
 *
 * @author 李静波
 */
class FormViewDAO extends PSIBaseExDAO {

	/**
	 * 视图列表 - 开发助手
	 */
	public function fvListForDev() {
		$db = $this->db;
		$sql = "select id, prop_value from t_fv_md
				where parent_id is null and prop_name = 'view_name' 
				order by show_order";
		$data = $db->query($sql);
		$result = [];
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"name" => $v["prop_value"]
			];
		}
		
		$sql = "select count(*) as cnt from t_fv_md
				where parent_id is null and prop_name = 'view_name' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		return [
				"dataList" => $result,
				"totalCount" => $cnt
		];
	}
}