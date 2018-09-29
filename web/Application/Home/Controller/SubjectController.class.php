<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\UserService;
use Home\Service\SubjectService;

/**
 * 会计科目Controller
 *
 * @author 李静波
 *        
 */
class SubjectController extends PSIBaseController {

	/**
	 * 会计科目 - 主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::GL_SUBJECT)) {
			$this->initVar();
			
			$this->assign("title", "会计科目");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Subject/index");
		}
	}

	/**
	 * 返回所有的公司列表
	 */
	public function companyList() {
		if (IS_POST) {
			$service = new SubjectService();
			$this->ajaxReturn($service->companyList());
		}
	}

	/**
	 * 某个公司的科目码列表
	 */
	public function subjectList() {
		if (IS_POST) {
			$params = [
					"companyId" => I("post.companyId")
			];
			
			$service = new SubjectService();
			$this->ajaxReturn($service->subjectList($params));
		}
	}

	/**
	 * 初始国家标准科目
	 */
	public function init() {
		if (IS_POST) {
			$params = [
					"id" => I("post.id")
			];
			
			$service = new SubjectService();
			$this->ajaxReturn($service->init($params));
		}
	}

	public function editSubject() {
		if (IS_POST) {
			$params = [
					"companyId" => I("post.companyId"),
					"id" => I("post.id"),
					"parentCode" => I("post.parentCode"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"isLeaf" => I("post.isLeaf")
			];
			
			$service = new SubjectService();
			$this->ajaxReturn($service->editSubject($params));
		}
	}

	/**
	 * 上级科目字段 - 查询数据
	 */
	public function queryDataForParentSubject() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$companyId = I("post.companyId");
			
			$service = new SubjectService();
			$this->ajaxReturn($service->queryDataForParentSubject($queryKey, $companyId));
		}
	}

	/**
	 * 某个科目的详情
	 */
	public function subjectInfo() {
		if (IS_POST) {
			$params = [
					"id" => I("post.id")
			];
			
			$service = new SubjectService();
			$this->ajaxReturn($service->subjectInfo($params));
		}
	}

	/**
	 * 删除科目
	 */
	public function deleteSubject() {
		if (IS_POST) {
			$params = [
					"id" => I("post.id")
			];
			
			$service = new SubjectService();
			$this->ajaxReturn($service->deleteSubject($params));
		}
	}
}