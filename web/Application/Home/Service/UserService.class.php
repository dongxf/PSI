<?php

namespace Home\Service;

use Home\Common\DemoConst;
use Home\Common\FIdConst;

/**
 * 用户Service
 *
 * @author 李静波
 */
class UserService extends PSIBaseService {

	public function getDemoLoginInfo() {
		if ($this->isDemo()) {
			return "您当前处于演示环境，默认的登录名和密码均为 admin <br/>更多帮助请点击 [帮助] 按钮来查看 "
					. "<br /><div style='color:red'>请勿在演示环境中保存正式数据，演示数据库通常每天在21:00后会清空一次</div>";
		} else {
			return "";
		}
	}

	/**
	 * 判断当前用户是否有$fid对应的权限
	 * 
	 * @param string $fid fid
	 * @return boolean true：有对应的权限
	 */
	public function hasPermission($fid = null) {
		$result = session("loginUserId") != null;
		if (!$result) {
			return false;
		}

		// 修改我的密码，重新登录，首页，使用帮助，关于，这五个功能对所有的在线用户均不需要特别的权限
		$idList = array(FIdConst::CHANGE_MY_PASSWORD,
			FIdConst::RELOGIN, FIdConst::HOME,
			FIdConst::HELP, FIdConst::ABOUT);
		if ($fid == null || in_array($fid, $idList)) {
			return $result;
		}

		$userId = $this->getLoginUserId();
		$sql = "select count(*) as cnt from "
				. " t_role_user ru, t_role_permission rp, t_permission p "
				. " where ru.user_id = '%s' and ru.role_id = rp.role_id "
				. "     and rp.permission_id = p.id and p.fid = '%s' ";
		$data = M()->query($sql, $userId, $fid);

		return $data[0]["cnt"] > 0;
	}

	public function getLoginUserId() {
		return session("loginUserId");
	}

	public function getLoginUserName() {
		$sql = "select name from t_user where id = '%s' ";

		$data = M()->query($sql, $this->getLoginUserId());

		if ($data) {
			return $data[0]["name"];
		} else {
			return "";
		}
	}

	public function getLoginName() {
		$sql = "select login_name from t_user where id = '%s' ";

		$data = M()->query($sql, $this->getLoginUserId());

		if ($data) {
			return $data[0]["login_name"];
		} else {
			return "";
		}
	}

	public function doLogin($loginName, $password) {
		$sql = "select id from t_user where login_name = '%s' and password = '%s' and enabled = 1";

		$user = M()->query($sql, $loginName, md5($password));

		if ($user) {
			session("loginUserId", $user[0]["id"]);

			$bls = new BizlogService();
			$bls->insertBizlog("登录系统");
			return $this->ok();
		} else {
			return $this->bad("用户名或者密码错误");
		}
	}

	public function allOrgs() {
		$sql = "select id, name,  org_code, full_name "
				. " from t_org where parent_id is null order by org_code";
		$db = M();
		$orgList1 = $db->query($sql);
		$result = array();

		// 第一级组织
		foreach ($orgList1 as $i => $org1) {
			$result[$i]["id"] = $org1["id"];
			$result[$i]["text"] = $org1["name"];
			$result[$i]["orgCode"] = $org1["org_code"];
			$result[$i]["fullName"] = $org1["full_name"];

			// 第二级
			$sql = "select id, name,  org_code, full_name "
					. " from t_org where parent_id = '%s' order by org_code";
			$orgList2 = $db->query($sql, $org1["id"]);

			$c2 = array();
			foreach ($orgList2 as $j => $org2) {
				$c2[$j]["id"] = $org2["id"];
				$c2[$j]["text"] = $org2["name"];
				$c2[$j]["orgCode"] = $org2["org_code"];
				$c2[$j]["fullName"] = $org2["full_name"];
				$c2[$j]["expanded"] = true;

				// 第三级
				$sql = "select id, name,  org_code, full_name "
						. " from t_org where parent_id = '%s' order by org_code";
				$orgList3 = $db->query($sql, $org2["id"]);
				$c3 = array();
				foreach ($orgList3 as $k => $org3) {
					$c3[$k]["id"] = $org3["id"];
					$c3[$k]["text"] = $org3["name"];
					$c3[$k]["orgCode"] = $org3["org_code"];
					$c3[$k]["fullName"] = $org3["full_name"];
					$c3[$k]["children"] = array();
					$c3[$k]["leaf"] = true;
				}

				$c2[$j]["children"] = $c3;
				$c2[$j]["leaf"] = count($c3) == 0;
			}

			$result[$i]["children"] = $c2;
			$result[$i]["leaf"] = count($orgList2) == 0;
			$result[$i]["expanded"] = true;
		}

		return $result;
	}

	public function users($orgId) {
		$sql = "select id, login_name,  name, enabled, org_code from t_user where org_id = '%s' ";

		$data = M()->query($sql, $orgId);

		$result = array();

		foreach ($data as $key => $value) {
			$result[$key]["id"] = $value["id"];
			$result[$key]["loginName"] = $value["login_name"];
			$result[$key]["name"] = $value["name"];
			$result[$key]["enabled"] = $value["enabled"];
			$result[$key]["orgCode"] = $value["org_code"];
		}

		return $result;
	}

	public function editOrg($id, $name, $parentId, $orgCode) {
		if ($this->isDemo()) {
			if ($id == DemoConst::ORG_COMPANY_ID) {
				return $this->bad("在演示环境下，组织机构[公司]不希望被您修改，请见谅");
			}
			if ($id == DemoConst::ORG_INFODEPT_ID) {
				return $this->bad("在演示环境下，组织机构[信息部]不希望被您修改，请见谅");
			}
		}

		if ($id) {
			// 编辑
			if ($parentId == $id) {
				return $this->bad("上级组织不能是自身");
			}
			$fullName = "";
			$db = M();

			if ($parentId == "root") {
				$parentId = null;
			}

			if ($parentId == null) {
				$fullName = $name;
				$sql = "update t_org set name = '%s', full_name = '%s', org_code = '%s', parent_id = null"
						. " where id = '%s' ";
				$db->execute($sql, $name, $fullName, $orgCode, $id);
			} else {
				$tempParentId = $parentId;
				while ($tempParentId != null) {
					$sql = "select parent_id from t_org where id = '%s' ";
					$d = $db->query($sql, $tempParentId);
					if ($d) {
						$tempParentId = $d[0]["parent_id"];

						if ($tempParentId == $id) {
							return $this->bad("不能选择下级组织作为上级组织");
						}
					} else {
						$tempParentId = null;
					}
				}

				$sql = "select full_name from t_org where id = '%s' ";
				$data = $db->query($sql, $parentId);
				if ($data) {
					$parentFullName = $data[0]["full_name"];
					$fullName = $parentFullName . "\\" . $name;

					$sql = "update t_org set name = '%s', full_name = '%s', org_code = '%s', parent_id = '%s' "
							. " where id = '%s' ";
					$db->execute($sql, $name, $fullName, $orgCode, $parentId, $id);

					$log = "编辑组织机构：名称 = {$name} 编码 = {$orgCode}";
					$bs = new BizlogService();
					$bs->insertBizlog($log, "用户管理");
				} else {
					return $this->bad("上级组织不存在");
				}
			}

			// 同步下级组织的full_name字段
			// 因为目前组织结构就最多三级，所以下面也就两个foreach就够了
			$sql = "select id, name from t_org where parent_id = '%s' ";
			$data = $db->query($sql, $id);
			foreach ($data as $v) {
				$idChild = $v["id"];
				$nameChild = $v["name"];
				$fullNameChild = $fullName . "\\" . $nameChild;
				$sql = "update t_org set full_name = '%s' where id = '%s' ";
				$db->execute($sql, $fullNameChild, $idChild);

				$sql = "select id, name from t_org where parent_id = '%s'";
				$data2 = $db->query($sql, $idChild);
				foreach ($data2 as $v2) {
					$idChild2 = $v2["id"];
					$nameChild2 = $v2["name"];
					$fullNameChild2 = $fullNameChild . "\\" . $nameChild2;
					$sql = "update t_org set full_name = '%s' where id = '%s' ";
					$db->execute($sql, $fullNameChild2, $idChild2);
				}
			}

			return $this->OK($id);
		} else {
			// 新增
			$idGenService = new IdGenService();
			$id = $idGenService->newId();

			$db = M();
			$sql = "select full_name from t_org where id = '%s' ";
			$parentOrg = $db->query($sql, $parentId);
			$fullName = "";
			if (!$parentOrg) {
				$parentId = null;
				$fullName = $name;
			} else {
				$fullName = $parentOrg[0]["full_name"] . "\\" . $name;
			}

			if ($parentId == null) {
				$sql = "insert into t_org (id, name, full_name, org_code, parent_id) "
						. " values ('%s', '%s', '%s', '%s', null)";

				$db->execute($sql, $id, $name, $fullName, $orgCode);
			} else {
				$sql = "insert into t_org (id, name, full_name, org_code, parent_id) "
						. " values ('%s', '%s', '%s', '%s', '%s')";

				$db->execute($sql, $id, $name, $fullName, $orgCode, $parentId);
			}

			$log = "新增组织机构：名称 = {$name} 编码 = {$orgCode}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "用户管理");
		}

		return $this->ok($id);
	}

	public function orgParentName($id) {
		$db = M();

		$data = $db->query("select parent_id from t_org where id = '%s' ", $id);

		if ($data) {
			$parentId = $data[0]["parent_id"];

			if ($parentId == null) {
				return "";
			} else {
				$data = $db->query("select full_name from t_org where id = '%s' ", $parentId);

				if ($data) {
					$result["parentOrgName"] = $data[0]["full_name"];
					return $result;
				} else {
					return "";
				}
			}
		} else {
			return "";
		}
	}

	public function deleteOrg($id) {
		if ($this->isDemo()) {
			if ($id == DemoConst::ORG_COMPANY_ID) {
				return $this->bad("在演示环境下，组织机构[公司]不希望被您删除，请见谅");
			}
			if ($id == DemoConst::ORG_INFODEPT_ID) {
				return $this->bad("在演示环境下，组织机构[信息部]不希望被您删除，请见谅");
			}
		}

		$db = M();
		$sql = "select name, org_code from t_org where id = '%s' ";
		$data = $db->query($sql, $id);
		if (!$data) {
			return $this->bad("要删除的组织机构不存在");
		}
		$name = $data[0]["name"];
		$orgCode = $data[0]["org_code"];

		$sql = "select count(*) as cnt from t_org where parent_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("当前组织机构还有下级组织，不能删除");
		}

		$sql = "select count(*) as cnt from t_user where org_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("当前组织机构还有用户，不能删除");
		}

		$sql = "delete from t_org where id = '%s' ";
		$db->execute($sql, $id);

		$log = "删除组织机构： 名称 = {$name} 编码  = {$orgCode}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, "用户管理");

		return $this->ok();
	}

	public function editUser($params) {
		$id = $params["id"];
		$loginName = $params["loginName"];
		$name = $params["name"];
		$orgCode = $params["orgCode"];
		$orgId = $params["orgId"];
		$enabled = $params["enabled"];

		if ($this->isDemo()) {
			if ($id == DemoConst::ADMIN_USER_ID) {
				return $this->bad("在演示环境下，admin用户不希望被您修改，请见谅");
			}
		}

		$pys = new PinyinService();
		$py = $pys->toPY($name);

		if ($id) {
			// 修改
			// 检查登录名是否被使用
			$db = M();
			$sql = "select count(*) as cnt from t_user where login_name = '%s' and id <> '%s' ";
			$data = $db->query($sql, $loginName, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("登录名 [$loginName] 已经存在");
			}

			// 检查组织机构是否存在
			$sql = "select count(*) as cnt from t_org where id = '%s' ";
			$data = $db->query($sql, $orgId);
			$cnt = $data[0]["cnt"];
			if ($cnt != 1) {
				return $this->bad("组织机构不存在");
			}

			$sql = "update t_user "
					. " set login_name = '%s', name = '%s', org_code = '%s', "
					. "       org_id = '%s', enabled = %d, py = '%s' "
					. " where id = '%s' ";
			$db->execute($sql, $loginName, $name, $orgCode, $orgId, $enabled, $py, $id);

			$log = "编辑用户： 登录名 = {$loginName} 姓名 = {$name} 编码 = {$orgCode}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "用户管理");
		} else {
			// 新建
			// 检查登录名是否被使用
			$db = M();
			$sql = "select count(*) as cnt from t_user where login_name = '%s' ";
			$data = $db->query($sql, $loginName);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("登录名 [$loginName] 已经存在");
			}

			// 检查组织机构是否存在
			$sql = "select count(*) as cnt from t_org where id = '%s' ";
			$data = $db->query($sql, $orgId);
			$cnt = $data[0]["cnt"];
			if ($cnt != 1) {
				return $this->bad("组织机构不存在");
			}

			// 新增用户的默认密码
			$password = md5("123456");

			$idGen = new IdGenService();
			$id = $idGen->newId();

			$sql = "insert into t_user (id, login_name, name, org_code, org_id, enabled, password, py) "
					. " values ('%s', '%s', '%s', '%s', '%s', %d, '%s', '%s') ";
			$db->execute($sql, $id, $loginName, $name, $orgCode, $orgId, $enabled, $password, $py);

			$log = "新建用户： 登录名 = {$loginName} 姓名 = {$name} 编码 = {$orgCode}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "用户管理");
		}

		return $this->ok($id);
	}

	public function deleteUser($params) {
		$id = $params["id"];

		if ($id == "6C2A09CD-A129-11E4-9B6A-782BCBD7746B") {
			return $this->bad("不能删除系统管理员用户");
		}
		// TODO:　检查用户是否存在，以及是否能删除
		$db = M();
		$sql = "select name from t_user where id = '%s' ";
		$data = $db->query($sql, $id);
		if (!$data) {
			return $this->bad("要删除的用户不存在");
		}
		$userName = $data[0]["name"];

		// 判断在采购入库单中是否使用了该用户
		$sql = "select count(*) as cnt from t_pw_bill "
				. " where biz_user_id = '%s' or input_user_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("用户[{$userName}]已经在采购入库单中使用了，不能删除");
		}

		// 判断在销售出库单中是否使用了该用户
		$sql = "select count(*) as cnt from t_ws_bill "
				. " where biz_user_id = '%s' or input_user_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("用户[{$userName}]已经在销售出库单中使用了，不能删除");
		}

		// 判断在销售退货入库单中是否使用了该用户
		$sql = "select count(*) as cnt from t_sr_bill "
				. " where biz_user_id = '%s' or input_user_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("用户[{$userName}]已经在销售退货入库单中使用了，不能删除");
		}

		// TODO 如果增加了其他单据，同样需要做出判断是否使用了该用户

		$sql = "delete from t_user where id = '%s' ";
		$db->execute($sql, $id);

		$bs = new BizlogService();
		$bs->insertBizlog("删除用户[{$userName}]", "用户管理");
		return $this->ok();
	}

	public function changePassword($params) {
		$id = $params["id"];

		if ($this->isDemo() && $id == DemoConst::ADMIN_USER_ID) {
			return $this->bad("在演示环境下，admin用户的密码不希望被您修改，请见谅");
		}

		$password = $params["password"];
		if (strlen($password) < 5) {
			return $this->bad("密码长度不能小于5位");
		}

		$db = M();
		$sql = "select login_name, name from t_user where id = '%s' ";
		$data = $db->query($sql, $id);
		if (!$data) {
			return $this->bad("要修改密码的用户不存在");
		}
		$loginName = $data[0]["login_name"];
		$name = $data[0]["name"];
		
		$sql = "update t_user "
				. " set password = '%s' "
				. " where id = '%s' ";
		$db->execute($sql, md5($password), $id);
		
		$log = "修改用户[登录名 ={$loginName} 姓名 = {$name}]的密码";
		$bs = new BizlogService();
		$bs->insertBizlog($log, "用户管理");

		return $this->ok($id);
	}

	public function clearLoginUserInSession() {
		session("loginUserId", null);
	}

	public function changeMyPassword($params) {
		$userId = $params["userId"];
		$oldPassword = $params["oldPassword"];
		$newPassword = $params["newPassword"];

		if ($this->isDemo() && $userId == DemoConst::ADMIN_USER_ID) {
			return $this->bad("在演示环境下，admin用户的密码不希望被您修改，请见谅");
		}

		if ($userId != $this->getLoginUserId()) {
			return $this->bad("服务器环境发生变化，请重新登录后再操作");
		}

		// 检验旧密码
		$db = M();
		$sql = "select count(*) as cnt from t_user where id = '%s' and password = '%s' ";
		$data = $db->query($sql, $userId, md5($oldPassword));
		$cnt = $data[0]["cnt"];
		if ($cnt != 1) {
			return $this->bad("旧密码不正确");
		}

		if (strlen($newPassword) < 5) {
			return $this->bad("密码长度不能小于5位");
		}

		$sql = "select login_name, name from t_user where id = '%s' ";
		$data = $db->query($sql, $userId);
		if (!$data) {
			return $this->bad("要修改密码的用户不存在");
		}
		$loginName = $data[0]["login_name"];
		$name = $data[0]["name"];

		$sql = "update t_user "
				. " set password = '%s' "
				. " where id = '%s' ";
		$db->execute($sql, md5($newPassword), $userId);

		$log = "用户[登录名 ={$loginName} 姓名 = {$name}]修改了自己的登录密码";
		$bs = new BizlogService();
		$bs->insertBizlog($log, "用户管理");
		
		return $this->ok();
	}

	public function queryData($queryKey) {
		if (!$queryKey) {
			return array();
		}

		$sql = "select id, login_name, name from t_user "
				. " where login_name like '%s' or name like '%s' or py like '%s' "
				. " order by login_name "
				. " limit 20";
		$key = "%{$queryKey}%";
		$data = M()->query($sql, $key, $key, $key);
		$result = array();
		foreach ($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["loginName"] = $v["login_name"];
			$result[$i]["name"] = $v["name"];
		}
		return $result;
	}

}
