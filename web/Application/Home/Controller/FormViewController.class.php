<?php

namespace Home\Controller;

use Home\Common\DemoConst;
use Home\Service\UserService;
use Home\Service\FormViewService;

/**
 * 表单视图Controller
 *
 * @author 李静波
 *        
 */
class FormViewController extends PSIBaseController {

	/**
	 * 表单视图 - 测试助手用于查看视图
	 */
	public function devView() {
		$viewId = I("get.id");
		
		$us = new UserService();
		
		// 开发助手只允许admin访问
		if ($us->getLoginUserId() == DemoConst::ADMIN_USER_ID) {
			$this->initVar();
			
			$service = new FormViewService();
			$this->assign("title", $service->getTitle($viewId));
			$this->assign("viewId", $viewId);
			
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home");
		}
	}

	/**
	 * 表单视图开发助手 - 主页面
	 */
	public function devIndex() {
		$us = new UserService();
		
		// 开发助手只允许admin访问
		if ($us->getLoginUserId() == DemoConst::ADMIN_USER_ID) {
			$this->initVar();
			
			$this->assign("title", "表单视图开发助手");
			
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home");
		}
	}

	/**
	 * 视图列表 - 开发助手
	 */
	public function fvListForDev() {
		$service = new FormViewService();
		$this->ajaxReturn($service->fvListForDev());
	}

	/**
	 * 获得某个表单视图的全部元数据
	 */
	public function getFormViewMetaData() {
		if (IS_POST) {
			$viewId = I("post.viewId");
			$service = new FormViewService();
			$this->ajaxReturn($service->getFormViewMetaData($viewId));
		}
	}

	/**
	 * 获得视图的主属性
	 */
	public function viewMainPropList() {
		if (IS_POST) {
			$params = [
					"id" => I("post.id")
			];
			
			$service = new FormViewService();
			$this->ajaxReturn($service->viewMainPropList($params));
		}
	}
}