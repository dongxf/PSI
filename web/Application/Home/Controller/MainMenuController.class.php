<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\FIdService;
use Home\Service\BizlogService;
use Home\Service\UserService;

use Home\Common\FIdConst;

/**
 * 主菜单Controller
 * @author 李静波
 *
 */
class MainMenuController extends Controller {

	public function navigateTo() {
		$this->assign("uri", __ROOT__ . "/");

		$fid = I("get.fid");

		$fidService = new FIdService();
		$fidService->insertRecentFid($fid);
		$fidName = $fidService->getFIdName($fid);
		if ($fidName) {
			$bizLogService = new BizlogService();
			$bizLogService->insertBizlog("进入模块：" . $fidName);
		}
		if (!$fid) {
			redirect(__ROOT__ . "/Home");
		}

		switch ($fid) {
			case FIdConst::RELOGIN:
				// 重新登录
				$us = new UserService();
				$us->clearLoginUserInSession();
				redirect(__ROOT__ . "/Home");
				break;
			case FIdConst::CHANGE_MY_PASSWORD:
				// 修改我的密码
				redirect(__ROOT__ . "/Home/User/changeMyPassword");
				break;
			case FIdConst::USR_MANAGEMENT:
				// 用户管理
				redirect(__ROOT__ . "/Home/User");
				break;
			case FIdConst::PERMISSION_MANAGEMENT:
				// 权限管理
				redirect(__ROOT__ . "/Home/Permission");
				break;
			case FIdConst::BIZ_LOG:
				// 业务日志
				redirect(__ROOT__ . "/Home/Bizlog");
				break;
			case FIdConst::WAREHOUSE:
				// 基础数据 - 仓库
				redirect(__ROOT__ . "/Home/Warehouse");
				break;
			case FIdConst::SUPPLIER:
				// 基础数据 - 供应商档案
				redirect(__ROOT__ . "/Home/Supplier");
				break;
			case FIdConst::GOODS:
				// 基础数据 - 商品
				redirect(__ROOT__ . "/Home/Goods");
				break;
			case FIdConst::GOODS_UNIT:
				// 基础数据 - 商品计量单位
				redirect(__ROOT__ . "/Home/Goods/unitIndex");
				break;
			case FIdConst::CUSTOMER:
				// 客户关系 - 客户资料
				redirect(__ROOT__ . "/Home/Customer");
				break;
			case FIdConst::INVENTORY_INIT:
				// 库存建账
				redirect(__ROOT__ . "/Home/Inventory/initIndex");
				break;
			case FIdConst::PURCHASE_WAREHOUSE:
				// 采购入库
				redirect(__ROOT__ . "/Home/Purchase/pwbillIndex");
				break;
			case FIdConst::INVENTORY_QUERY:
				// 库存账查询
				redirect(__ROOT__ . "/Home/Inventory/inventoryQuery");
				break;
			case FIdConst::PAYABLES:
				// 应付账款管理
				redirect(__ROOT__ . "/Home/Funds/payIndex");
				break;
			case FIdConst::RECEIVING:
				// 应收账款管理
				redirect(__ROOT__ . "/Home/Funds/rvIndex");
				break;
			case FIdConst::WAREHOUSING_SALE:
				// 销售出库
				redirect(__ROOT__ . "/Home/Sale/wsIndex");
				break;
			case FIdConst::SALE_REJECTION:
				// 销售退货入库
				redirect(__ROOT__ . "/Home/Sale/srIndex");
				break;
			case FIdConst::BIZ_CONFIG:
				// 业务设置
				redirect(__ROOT__ . "/Home/BizConfig");
				break;
			case FIdConst::INVENTORY_TRANSFER:
				// 库间调拨
				redirect(__ROOT__ . "/Home/InvTransfer");
				break;
			case FIdConst::INVENTORY_CHECK:
				// 库存盘点
				redirect(__ROOT__ . "/Home/InvCheck");
				break;
			default:
				redirect(__ROOT__ . "/Home");
		}
	}

	/**
	 * 返回生成主菜单的JSON数据
	 * 目前只能处理到生成三级菜单的情况
	 */
	public function mainMenuItems() {
		if (IS_POST) {
			$us = new UserService();

			$sql = "select id, caption, fid from t_menu_item 
					where parent_id is null order by show_order";
			$db = M();
			$m1 = $db->query($sql);
			$result = array();

			$index1 = 0;
			foreach ($m1 as $menuItem1) {

				$children1 = array();

				$sql = "select id, caption, fid from t_menu_item "
						. " where parent_id = '%s' order by show_order ";
				$m2 = $db->query($sql, $menuItem1["id"]);

				// 第二级菜单
				$index2 = 0;
				foreach ($m2 as $menuItem2) {
					$children2 = array();
					$sql = "select id, caption, fid from t_menu_item "
							. " where parent_id = '%s' order by show_order ";
					$m3 = $db->query($sql, $menuItem2["id"]);

					// 第三级菜单
					$index3 = 0;
					foreach ($m3 as $menuItem3) {
						if ($us->hasPermission($menuItem3["fid"])) {
							$children2[$index3]["id"] = $menuItem3["id"];
							$children2[$index3]["caption"] = $menuItem3["caption"];
							$children2[$index3]["fid"] = $menuItem3["fid"];
							$children2[$index3]["children"] = array();
							$index3++;
						}
					}

					if ($us->hasPermission($menuItem2["fid"])) {
						$children1[$index2]["id"] = $menuItem2["id"];
						$children1[$index2]["caption"] = $menuItem2["caption"];
						$children1[$index2]["fid"] = $menuItem2["fid"];
						$children1[$index2]["children"] = $children2;
						$index2++;
					}
				}

				if (count($children1) > 0) {
					$result[$index1] = $menuItem1;
					$result[$index1]["children"] = $children1;
					$index1++;
				}
			}

			$this->ajaxReturn($result);
		}
	}

	/**
	 * 常用功能
	 */
	public function recentFid() {
		if (IS_POST) {
			$fidService = new FIdService();
			$data = $fidService->recentFid();

			$this->ajaxReturn($data);
		}
	}
}
