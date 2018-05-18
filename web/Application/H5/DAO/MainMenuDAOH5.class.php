<?php

namespace H5\DAO;

use Home\Common\FIdConst;

/**
 * 主菜单 DAO
 *
 * @author 李静波
 */
class MainMenuDAOH5 extends PSIBaseExDAO {

	private function fidToURL($fid) {
		switch ($fid) {
			case FIdConst::ABOUT :
				return "/about/";
			default :
				return "#";
		}
	}

	private function fidToClick($fid) {
		if ($fid == FIdConst::SALE_ORDER) {
			return "todo";
		}
		
		if ($fid == FIdConst::RELOGIN) {
			return "doLogout";
		}
		
		return "doNothing";
	}

	public function mainMenuItems($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		
		$userDAO = new UserDAOH5($db);
		
		$result = [];
		
		// 第一级菜单
		$sql = "select id, caption 
				from t_menu_item_h5
				where parent_id is null
				order by show_order";
		$data = $db->query($sql);
		foreach ( $data as $v ) {
			$menu1 = [
					"caption" => $v["caption"]
			];
			
			$id = $v["id"];
			
			// 第二级菜单
			
			$menu2 = [];
			$sql = "select caption, fid
				from t_menu_item_h5
				where parent_id = '%s'
				order by show_order";
			$data2 = $db->query($sql, $id);
			foreach ( $data2 as $v2 ) {
				$fid = $v2["fid"];
				
				if ($userDAO->hasPermission($loginUserId, $fid)) {
					$menu2[] = [
							"caption" => $v2["caption"],
							"url" => $this->fidToURL($fid),
							"click" => $this->fidToClick($fid)
					];
				}
			}
			
			// 如果没有一个二级菜单项，那么一级菜单也不显示
			if (count($menu2) > 0) {
				menu1["items"] = $menu2;
				
				$result[] = $menu1;
			}
		}
	}
}