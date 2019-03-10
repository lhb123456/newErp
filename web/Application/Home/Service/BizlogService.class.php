<?php

namespace Home\Service;

use Home\DAO\BizlogDAO;

/**
 * 业务日志Service
 *
 * @author 李静波
 */
class BizlogService extends PSIBaseService {
	var $db;

	function __construct($db = null) {
		if ($db == null) {
			$db = M();
		}
		
		$this->db = $db;
	}

	/**
	 * 返回日志列表
	 */
	public function logList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = $this->db;
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new BizlogDAO($db);
		return $dao->logList($params);
	}

	/**
	 * 记录业务日志
	 *
	 * @param string $log
	 *        	日志内容
	 * @param string $category
	 *        	日志分类
	 */
	public function insertBizlog($log, $category = "系统") {
		$us = new UserService();
		if ($us->getLoginUserId() == null) {
			return;
		}
		
		$ip = session("PSI_login_user_ip");
		if ($ip == null || $ip == "") {
			$ip = $this->getClientIP();
		}
		
		$ipFrom = session("PSI_login_user_ip_from");
		
		$db = $this->db;
		
		$dataOrg = $us->getLoginUserDataOrg();
		$companyId = $us->getCompanyId();
		
		$params = array(
				"loginUserId" => $us->getLoginUserId(),
				"log" => $log,
				"category" => $category,
				"ip" => $ip,
				"ipFrom" => $ipFrom,
				"dataOrg" => $dataOrg,
				"companyId" => $companyId
		);
		
		$dao = new BizlogDAO($db);
		
		return $dao->insertBizlog($params);
	}

	private function getClientIP() {
		return get_client_ip();
	}
}