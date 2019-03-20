<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 业务日志 DAO
 *
 * @author 李静波
 */
class BizlogDAO extends PSIBaseExDAO {

	/**
	 * 返回日志列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function logList($params) {
		$loginUserId = $params["loginUserId"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$ip = $params["ip"];
		$loginName = $params["loginName"];
		
		$db = $this->db;
		
		$sql = "select b.id, u.login_name, u.name, b.ip, b.info, b.date_created,
					b.log_category, b.ip_from
				from t_biz_log b, t_user u
				where (b.user_id = u.id) ";
		$queryParams = [];
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::BIZ_LOG, "b", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($ip) {
			$sql .= " and (b.ip like '%s') ";
			$queryParams[] = "{$ip}%";
		}
		if ($loginName) {
			$sql .= " and (u.login_name like '%s') ";
			$queryParams[] = "{$loginName}%";
		}
		
		$sql .= " order by b.date_created desc
				limit %d, %d ";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		
		$data = $db->query($sql, $queryParams);
		$result = [];
		
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"loginName" => $v["login_name"],
					"userName" => $v["name"],
					"ip" => $v["ip"],
					"ipFrom" => $v["ip_from"],
					"content" => $v["info"],
					"dt" => $v["date_created"],
					"logCategory" => $v["log_category"]
			];
		}
		
		$sql = "select count(*) as cnt
				from t_biz_log b, t_user u
				where (b.user_id = u.id) ";
		$queryParams = [];
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::BIZ_LOG, "b", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($ip) {
			$sql .= " and (b.ip like '%s') ";
			$queryParams[] = "{$ip}%";
		}
		if ($loginName) {
			$sql .= " and (u.login_name like '%s') ";
			$queryParams[] = "{$loginName}%";
		}
		
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return [
				"logs" => $result,
				"totalCount" => $cnt
		];
	}

	/**
	 * 记录业务日志
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function insertBizlog($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		$log = $params["log"];
		$category = $params["category"];
		$ip = $params["ip"];
		$ipFrom = $params["ipFrom"];
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		$sql = "insert into t_biz_log (user_id, info, ip, date_created, log_category, data_org,
						ip_from, company_id)
				values ('%s', '%s', '%s',  now(), '%s', '%s', '%s', '%s')";
		$rc = $db->execute($sql, $loginUserId, $log, $ip, $category, $dataOrg, $ipFrom, $companyId);
		
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}
}