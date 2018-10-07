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

	/**
	 * 获得表单视图的标题
	 *
	 * @param string $viewId        	
	 * @return string
	 */
	public function getTitle(string $viewId) {
		$db = $this->db;
		
		$sql = "select prop_value from t_fv_md
				where parent_id = '%s' and prop_name = 'title' ";
		$data = $db->query($sql, $viewId);
		if ($data) {
			return $data[0]["prop_value"];
		} else {
			return "";
		}
	}

	/**
	 * 获得某个表单视图的全部元数据
	 */
	public function getFormViewMetaData(string $viewId) {
		$db = $this->db;
		
		// 检查表单视图是否存在
		$sql = "select prop_name from t_fv_md where id = '%s' ";
		$data = $db->query($sql, $viewId);
		if (! $data) {
			return $this->emptyResult();
		}
		
		$result = [];
		
		// 工具栏按钮
		$sql = "select prop_value from t_fv_md
				where parent_id = '%s' and prop_name = 'tool_bar_id' ";
		$data = $db->query($sql, $viewId);
		if ($data) {
			$toolBarId = $data[0]["prop_value"];
			
			$sql = "select id, prop_value from t_fv_md
					where parent_id = '%s' and prop_name = 'button_text'
					order by show_order";
			$data = $db->query($sql, $toolBarId);
			$toolBar = [];
			foreach ( $data as $v ) {
				$buttonText = $v["prop_value"];
				$buttonId = $v["id"];
				
				// 获得按钮单击handler
				$handler = null;
				$sql = "select prop_value from t_fv_md
						where parent_id = '%s' and prop_name = 'button_handler'";
				$d = $db->query($sql, $buttonId);
				if ($d) {
					$handler = $d[0]["prop_value"];
				}
				
				$toolBar[] = [
						"text" => $buttonText,
						"handler" => $handler
				];
			}
			
			$result["toolBar"] = $toolBar;
		}
		
		return $result;
	}
}