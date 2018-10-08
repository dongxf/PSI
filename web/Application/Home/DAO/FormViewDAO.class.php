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

	private function getPropId($parentId, $propName) {
		$db = $this->db;
		$sql = "select id from t_fv_md
				where parent_id = '%s' and prop_name = '%s'
				limit 1";
		$data = $db->query($sql, $parentId, $propName);
		if ($data) {
			return $data[0]["id"];
		} else {
			return null;
		}
	}

	private function getPropValue($parentId, $propName) {
		$db = $this->db;
		$sql = "select prop_value from t_fv_md 
				where parent_id = '%s' and prop_name = '%s' 
				limit 1";
		$data = $db->query($sql, $parentId, $propName);
		if ($data) {
			return $data[0]["prop_value"];
		} else {
			return null;
		}
	}

	private function getPropValueArray($parentId, $propName) {
		$db = $this->db;
		$sql = "select prop_value from t_fv_md
				where parent_id = '%s' and prop_name = '%s' 
				order by show_order";
		return $db->query($sql, $parentId, $propName);
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
		
		// 使用帮助Id
		$sql = "select prop_value from t_fv_md 
				where parent_id = '%s' and prop_name = 'help_id' ";
		$data = $db->query($sql, $viewId);
		if ($data) {
			$result["helpId"] = $data[0]["prop_value"];
		}
		
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
				
				// 按钮的icon
				$icon = null;
				$sql = "select prop_value from t_fv_md
						where parent_id = '%s' and prop_name = 'button_icon'";
				$d = $db->query($sql, $buttonId);
				if ($d) {
					$icon = $d[0]["prop_value"];
				}
				
				$toolBarItem = [
						"text" => $buttonText,
						"handler" => $handler,
						"iconCls" => $icon
				
				];
				
				// 子按钮/子菜单
				$sql = "select prop_value from t_fv_md
						where parent_id = '%s' and prop_name = 'sub_button_id' ";
				$d = $db->query($sql, $buttonId);
				if ($d) {
					$subButtonId = $d[0]["prop_value"];
					
					$sql = "select id, prop_value from t_fv_md
							where parent_id = '%s' and prop_name = 'button_text' 
							order by show_order";
					$subButtons = $db->query($sql, $subButtonId);
					$subButtonList = [];
					foreach ( $subButtons as $btn ) {
						$btnText = $btn["prop_value"];
						$btnId = $btn["id"];
						
						// 查询该button的Handler
						$sql = "select prop_value from t_fv_md
								where parent_id = '%s' and prop_name = 'button_handler' ";
						$d = $db->query($sql, $btnId);
						$btnHandler = null;
						if ($d) {
							$btnHandler = $d[0]["prop_value"];
						}
						
						// 按钮的icon
						$sql = "select prop_value from t_fv_md
								where parent_id = '%s' and prop_name = 'button_icon' ";
						$d = $db->query($sql, $btnId);
						$btnIcon = null;
						if ($d) {
							$btnIcon = $d[0]["prop_value"];
						}
						
						$subButtonList[] = [
								"text" => $btnText,
								"handler" => $btnHandler,
								"iconCls" => $btnIcon
						];
					}
					
					$toolBarItem["subButtons"] = $subButtonList;
				}
				
				$toolBar[] = $toolBarItem;
			}
			
			$result["toolBar"] = $toolBar;
		}
		
		// 查询栏
		$queryPanelId = $this->getPropValue($viewId, "query_panel_id");
		if ($queryPanelId) {
			$queryCmp = [];
			
			$queryPanelColCount = $this->getPropValue($viewId, "query_panel_col_count");
			if (! $queryPanelColCount) {
				$queryPanelColCount = 4;
			}
			
			$data = $this->getPropValueArray($queryPanelId, "query_cmp_label");
			foreach ( $data as $v ) {
				$queryCmpLabel = $v["prop_value"];
				
				$queryCmpId = $this->getPropId($queryPanelId, "query_cmp_label");
				
				if (! $queryCmpId) {
					continue;
				}
				
				$xtype = $this->getPropValue($queryCmpId, "query_cmp_xtype");
				if (! $xtype) {
					$xtype = "textfield";
				}
				
				$queryCmp[] = [
						"label" => $queryCmpLabel,
						"xtype" => $xtype
				];
			}
			
			$result["queryCmpColCount"] = $queryPanelColCount;
			$result["queryCmp"] = $queryCmp;
		}
		
		return $result;
	}
}