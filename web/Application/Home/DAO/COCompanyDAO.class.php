<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 往来单位 DAO
 *
 * @author 李静波
 */
class COCompanyDAO extends PSIBaseExDAO {

	/**
	 * 往来单位分类列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function categoryList($params) {
		$db = $this->db;
		
		$companyType = $params["companyType"];
		$categoryName = trim($params["categoryName"]);
		$name = trim($params["name"]);

		
		$inQuery = false;
		if ( $name ||$categoryName) {
			$inQuery = true;
		}
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$ds = new DataOrgDAO($db);
		$queryParam = [];
		
		$sql = "select c.id, c.code, c.name, c.limit_count
				from t_company_category c ";
		$rs = $ds->buildSQL(FIdConst::CO_COMPANY, "c", $loginUserId);
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}

		if($categoryName){
		    $sql.=" and c.name like '%s'";
		    $queryParam[]="%{$categoryName}%";
        }

		$sql .= " order by c.code ";
		
		$data = $db->query($sql, $queryParam);
		
		$result = [];
		foreach ( $data as $v ) {
			// 分类中的往来单位数量
			$id = $v["id"];
			$queryParam = [];
			$sql = "select count(cc.id) as cnt
					from t_co_company cc 
					where (cc.category_id = '%s') ";
			$queryParam[] = $id;

			if($companyType==1){            //供应商
			    $sql.=" and cc.is_supplier=1";
            }else if($companyType==2){      //客户
			    $sql.=" and cc.is_customer=1";
            }

			if ($name) {
				$sql .= " and (cc.name like '%s' or cc.py like '%s' ) ";
				$queryParam[] = "%{$name}%";
				$queryParam[] = "%{$name}%";
			}

			$rs = $ds->buildSQL(FIdConst::CO_COMPANY, "cc", $loginUserId);
			if ($rs) {
				$sql .= " and " . $rs[0];
				$queryParam = array_merge($queryParam, $rs[1]);
			}
			$d = $db->query($sql, $queryParam);
			$companyCount = $d[0]["cnt"];
			
			if ($inQuery && $companyCount == 0) {
				// 当前是带查询条件 而且该分类下没有符合的往来单位资料，则不返回该分类
				continue;
			}
			

			$result[] = [
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"limitCount" =>sprintf("%.2f",$v["limit_count"]),
					"cnt" => $companyCount
			];
		}
		return $result;
	}

	/**
	 * 新增往来单位分类
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function addCOCompanyCategory(& $params) {
		$db = $this->db;
		
		$code = trim($params["code"]);
		$name = trim($params["name"]);
		$limitCount = $params["limitCount"]?$params["limitCount"]:0;

		
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->bad("参数dataOrg不正确");
		}
		if ($this->companyIdNotExists($companyId)) {
			return $this->bad("参数companyId不正确");
		}
		
		if ($this->isEmptyStringAfterTrim($code)) {
			return $this->bad("分类编码不能为空");
		}
		if ($this->isEmptyStringAfterTrim($name)) {
			return $this->bad("分类名称不能为空");
		}

		if ($this->stringBeyondLimit($name, 40)) {
			return $this->bad("分类名称长度不能超过40位");
		}
		
		// 检查分类编码是否已经存在
		$sql = "select count(*) as cnt from t_company_category where name = '%s' ";
		$data = $db->query($sql, $name);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("名称为 [{$name}] 的往来单位分类已经存在");
		}
		
		$id = $this->newId();
		$params["id"] = $id;
		
		$sql = "insert into t_company_category (id, code, name,limit_count)
				values ('%s', '%s', '%s', '%s') ";
		$rc = $db->execute($sql, $id, $code, $name,$limitCount);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 编辑往来单位分类
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function updateCOCompanyCategory(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		$code = trim($params["code"]);
		$name = trim($params["name"]);
        $limitCount = $params["limitCount"]?$params["limitCount"]:0;
		
		if ($this->isEmptyStringAfterTrim($code)) {
			return $this->bad("分类编码不能为空");
		}
		if ($this->isEmptyStringAfterTrim($name)) {
			return $this->bad("分类名称不能为空");
		}

		if ($this->stringBeyondLimit($name, 40)) {
			return $this->bad("分类名称长度不能超过40位");
		}
		
		// 检查分类名称是否已经存在
		$sql = "select count(*) as cnt from t_customer_category where name = '%s' and id <> '%s' ";
		$data = $db->query($sql, $name, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("名称为 [{$name}] 的分类已经存在");
		}
		
		$sql = "update t_company_category
				set code = '%s', name = '%s',limit_count = '%s'
				where id = '%s' ";
		$rc = $db->execute($sql, $code, $name, $limitCount, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 根据往来单位分类id查询往来单位分类
	 *
	 * @param string $id
	 *        	往来单位分类id
	 * @return array|NULL
	 */
	public function getCOCompanyCategoryById($id) {
		$db = $this->db;
		
		$sql = "select code, name from t_company_category where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			return [
					"code" => $data[0]["code"],
					"name" => $data[0]["name"]
			];
		} else {
			return null;
		}
	}

	/**
	 * 删除往来单位分类
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function deleteCOCompanyCategory(& $params) {
		$db = $this->db;
		
		// 往来单位分类id
		$id = $params["id"];
		
		$category = $this->getCOCompanyCategoryById($id);
		if (! $category) {
			return $this->bad("要删除的分类不存在");
		}
		$params["code"] = $category["code"];
		$params["name"] = $category["name"];
		$name = $params["name"];
		
		$sql = "select count(*) as cnt from t_co_company where category_id = '%s' ";
		$query = $db->query($sql, $id);
		$cnt = $query[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("当前分类 [{$name}] 下还有往来单位资料，不能删除");
		}
		
		$sql = "delete from t_company_category where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 新增往来单位资料
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function addCustomer(& $params) {
		$db = $this->db;
		
		$code = $params["code"];
		$name = $params["name"];
		$address = $params["address"];
		$addressReceipt = $params["addressReceipt"];
		$contact01 = $params["contact01"];
		$mobile01 = $params["mobile01"];
		$tel01 = $params["tel01"];
		$qq01 = $params["qq01"];
		$contact02 = $params["contact02"];
		$mobile02 = $params["mobile02"];
		$tel02 = $params["tel02"];
		$qq02 = $params["qq02"];
		$bankName = $params["bankName"];
		$bankAccount = $params["bankAccount"];
		$tax = $params["tax"];
		$fax = $params["fax"];
		$note = $params["note"];
		
		// 销售出库仓库
		$warehouseId = $params["warehouseId"];
		$warehouseDAO = new WarehouseDAO($db);
		$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
		if (! $warehouse) {
			// 没有选择销售出库仓库
			$warehouseId = "";
		}
		
		$py = $params["py"];
		$categoryId = $params["categoryId"];
		
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		
		$recordStatus = $params["recordStatus"];
		
		// 检查编码是否已经存在
		$sql = "select count(*) as cnt from t_customer where code = '%s' ";
		$data = $db->query($sql, $code);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [{$code}] 的往来单位已经存在");
		}
		
		$id = $this->newId();
		$params["id"] = $id;
		
		$sql = "insert into t_customer (id, category_id, code, name, py, contact01,
					qq01, tel01, mobile01, contact02, qq02, tel02, mobile02, address, address_receipt,
					bank_name, bank_account, tax_number, fax, note, data_org, company_id, sales_warehouse_id,
					record_status)
				values ('%s', '%s', '%s', '%s', '%s', '%s',
						'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
						'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
						%d)  ";
		$rc = $db->execute($sql, $id, $categoryId, $code, $name, $py, $contact01, $qq01, $tel01, 
				$mobile01, $contact02, $qq02, $tel02, $mobile02, $address, $addressReceipt, 
				$bankName, $bankAccount, $tax, $fax, $note, $dataOrg, $companyId, $warehouseId, 
				$recordStatus);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 初始化应收账款
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function initReceivables(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		$initReceivables = $params["initReceivables"];
		$initReceivablesDT = $params["initReceivablesDT"];
		
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		$initReceivables = floatval($initReceivables);
		if ($initReceivables && $initReceivablesDT) {
			$sql = "select count(*) as cnt
					from t_receivables_detail
					where ca_id = '%s' and ca_type = 'customer' and ref_type <> '应收账款期初建账'
						and company_id = '%s' ";
			$data = $db->query($sql, $id, $companyId);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				// 已经有应收业务发生，就不再更改期初数据
				return null;
			}
			
			$sql = "update t_customer
					set init_receivables = %f, init_receivables_dt = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $initReceivables, $initReceivablesDT, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			// 应收明细账
			$sql = "select id from t_receivables_detail
					where ca_id = '%s' and ca_type = 'customer' and ref_type = '应收账款期初建账'
						and company_id = '%s' ";
			$data = $db->query($sql, $id, $companyId);
			if ($data) {
				$rvId = $data[0]["id"];
				$sql = "update t_receivables_detail
						set rv_money = %f, act_money = 0, balance_money = %f, biz_date ='%s', date_created = now()
						where id = '%s' ";
				$rc = $db->execute($sql, $initReceivables, $initReceivables, $initReceivablesDT, 
						$rvId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			} else {
				$rvId = $this->newId();
				$sql = "insert into t_receivables_detail (id, rv_money, act_money, balance_money,
						biz_date, date_created, ca_id, ca_type, ref_number, ref_type, data_org, company_id)
						values ('%s', %f, 0, %f, '%s', now(), '%s', 'customer', '%s', '应收账款期初建账', '%s', '%s') ";
				$rc = $db->execute($sql, $rvId, $initReceivables, $initReceivables, 
						$initReceivablesDT, $id, $id, $dataOrg, $companyId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			}
			
			// 应收总账
			$sql = "select id from t_receivables
					where ca_id = '%s' and ca_type = 'customer'
						and company_id = '%s' ";
			$data = $db->query($sql, $id, $companyId);
			if ($data) {
				$rvId = $data[0]["id"];
				$sql = "update t_receivables
						set rv_money = %f, act_money = 0, balance_money = %f
						where id = '%s' ";
				$rc = $db->execute($sql, $initReceivables, $initReceivables, $rvId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			} else {
				$rvId = $this->newId();
				$sql = "insert into t_receivables (id, rv_money, act_money, balance_money,
							ca_id, ca_type, data_org, company_id)
						values ('%s', %f, 0, %f, '%s', 'customer', '%s', '%s')";
				$rc = $db->execute($sql, $rvId, $initReceivables, $initReceivables, $id, $dataOrg, 
						$companyId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			}
		} else {
			$sql = "update t_customer
					set init_receivables = null, init_receivables_dt = null
					where id = '%s' ";
			$rc = $db->execute($sql, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			// 应收明细账
			$sql = "delete from t_receivables_detail
					where ca_id = '%s' and ca_type = 'customer' and ref_type = '应收账款期初建账'
						and company_id = '%s' ";
			$rc = $db->execute($sql, $id, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			// 应收总账
			$sql = "delete from t_receivables
					where ca_id = '%s' and ca_type = 'customer'
						and company_id = '%s' ";
			$rc = $db->execute($sql, $id, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 编辑往来单位资料
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function updateCustomer(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$address = $params["address"];
		$addressReceipt = $params["addressReceipt"];
		$contact01 = $params["contact01"];
		$mobile01 = $params["mobile01"];
		$tel01 = $params["tel01"];
		$qq01 = $params["qq01"];
		$contact02 = $params["contact02"];
		$mobile02 = $params["mobile02"];
		$tel02 = $params["tel02"];
		$qq02 = $params["qq02"];
		$bankName = $params["bankName"];
		$bankAccount = $params["bankAccount"];
		$tax = $params["tax"];
		$fax = $params["fax"];
		$note = $params["note"];
		
		// 销售出库仓库
		$warehouseId = $params["warehouseId"];
		$warehouseDAO = new WarehouseDAO($db);
		$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
		if (! $warehouse) {
			// 没有选择销售出库仓库
			$warehouseId = "";
		}
		
		$py = $params["py"];
		$categoryId = $params["categoryId"];
		
		$recordStatus = $params["recordStatus"];
		
		// 检查编码是否已经存在
		$sql = "select count(*) as cnt from t_customer where code = '%s'  and id <> '%s' ";
		$data = $db->query($sql, $code, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [{$code}] 的往来单位已经存在");
		}
		
		$sql = "update t_customer
					set code = '%s', name = '%s', category_id = '%s', py = '%s',
					contact01 = '%s', qq01 = '%s', tel01 = '%s', mobile01 = '%s',
					contact02 = '%s', qq02 = '%s', tel02 = '%s', mobile02 = '%s',
					address = '%s', address_receipt = '%s',
					bank_name = '%s', bank_account = '%s', tax_number = '%s',
					fax = '%s', note = '%s', sales_warehouse_id = '%s',
					record_status = %d
				where id = '%s'  ";
		
		$rc = $db->execute($sql, $code, $name, $categoryId, $py, $contact01, $qq01, $tel01, 
				$mobile01, $contact02, $qq02, $tel02, $mobile02, $address, $addressReceipt, 
				$bankName, $bankAccount, $tax, $fax, $note, $warehouseId, $recordStatus, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 删除往来单位资料
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function deleteCustomer(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$customer = $this->getCustomerById($id);
		
		if (! $customer) {
			return $this->bad("要删除的往来单位资料不存在");
		}
		$code = $customer["code"];
		$name = $customer["name"];
		$params["code"] = $code;
		$params["name"] = $name;
		
		// 判断是否能删除往来单位资料
		$sql = "select count(*) as cnt from t_ws_bill where customer_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("往来单位资料 [{$code} {$name}] 已经在销售出库单中使用了，不能删除");
		}
		
		$sql = "select count(*) as cnt
				from t_receivables_detail r, t_receiving v
				where r.ref_number = v.ref_number and r.ref_type = v.ref_type
				  and r.ca_id = '%s' and r.ca_type = 'customer' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("往来单位资料 [{$code} {$name}] 已经有收款记录，不能删除");
		}
		
		// 判断在销售退货入库单中是否使用了往来单位资料
		$sql = "select count(*) as cnt from t_sr_bill where customer_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("往来单位资料 [{$code} {$name}]已经在销售退货入库单中使用了，不能删除");
		}
		
		$sql = "delete from t_customer where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 删除往来单位应收总账和明细账
		$sql = "delete from t_receivables where ca_id = '%s' and ca_type = 'customer' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_receivables_detail where ca_id = '%s' and ca_type = 'customer' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 通过往来单位id查询往来单位资料
	 *
	 * @param string $id        	
	 * @return array|NULL
	 */
	public function getCustomerById($id) {
		$db = $this->db;
		
		$sql = "select code, name from t_customer where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			return [
					"code" => $data[0]["code"],
					"name" => $data[0]["name"]
			];
		} else {
			return null;
		}
	}

	/**
	 * 获得某个分类的往来单位列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function customerList($params) {
		$db = $this->db;
		
		$categoryId = $params["categoryId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$code = $params["code"];
		$name = $params["name"];
		$address = $params["address"];
		$contact = $params["contact"];
		$mobile = $params["mobile"];
		$tel = $params["tel"];
		$qq = $params["qq"];
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$sql = "select id, category_id, code, name, address, contact01, qq01, tel01, mobile01,
				 	contact02, qq02, tel02, mobile02, init_receivables, init_receivables_dt,
					address_receipt, bank_name, bank_account, tax_number, fax, note, data_org,
					sales_warehouse_id, record_status
				 from t_customer where (category_id = '%s') ";
		$queryParam = [];
		$queryParam[] = $categoryId;
		if ($code) {
			$sql .= " and (code like '%s' ) ";
			$queryParam[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (name like '%s' or py like '%s' ) ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}
		if ($address) {
			$sql .= " and (address like '%s' or address_receipt like '%s') ";
			$queryParam[] = "%$address%";
			$queryParam[] = "%{$address}%";
		}
		if ($contact) {
			$sql .= " and (contact01 like '%s' or contact02 like '%s' ) ";
			$queryParam[] = "%{$contact}%";
			$queryParam[] = "%{$contact}%";
		}
		if ($mobile) {
			$sql .= " and (mobile01 like '%s' or mobile02 like '%s' ) ";
			$queryParam[] = "%{$mobile}%";
			$queryParam[] = "%{$mobile}";
		}
		if ($tel) {
			$sql .= " and (tel01 like '%s' or tel02 like '%s' ) ";
			$queryParam[] = "%{$tel}%";
			$queryParam[] = "%{$tel}";
		}
		if ($qq) {
			$sql .= " and (qq01 like '%s' or qq02 like '%s' ) ";
			$queryParam[] = "%{$qq}%";
			$queryParam[] = "%{$qq}";
		}
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::CUSTOMER, "t_customer", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " order by code limit %d, %d";
		$queryParam[] = $start;
		$queryParam[] = $limit;
		$result = [];
		$data = $db->query($sql, $queryParam);
		$warehouseDAO = new WarehouseDAO($db);
		foreach ( $data as $v ) {
			$initDT = $v["init_receivables_dt"] ? $this->toYMD($v["init_receivables_dt"]) : null;
			
			$warehouseId = $v["sales_warehouse_id"];
			$warehouseName = "";
			if ($warehouseId) {
				$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
				$warehouseName = $warehouse["name"];
			}
			
			$result[] = [
					"id" => $v["id"],
					"categoryId" => $v["category_id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"address" => $v["address"],
					"addressReceipt" => $v["address_receipt"],
					"contact01" => $v["contact01"],
					"qq01" => $v["qq01"],
					"tel01" => $v["tel01"],
					"mobile01" => $v["mobile01"],
					"contact02" => $v["contact02"],
					"qq02" => $v["qq02"],
					"tel02" => $v["tel02"],
					"mobile02" => $v["mobile02"],
					"initReceivables" => $v["init_receivables"],
					"initReceivablesDT" => $initDT,
					"bankName" => $v["bank_name"],
					"bankAccount" => $v["bank_account"],
					"tax" => $v["tax_number"],
					"fax" => $v["fax"],
					"note" => $v["note"],
					"dataOrg" => $v["data_org"],
					"warehouseName" => $warehouseName,
					"recordStatus" => $v["record_status"]
			];
		}
		
		$sql = "select count(*) as cnt from t_customer where (category_id  = '%s') ";
		$queryParam = [];
		$queryParam[] = $categoryId;
		if ($code) {
			$sql .= " and (code like '%s' ) ";
			$queryParam[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (name like '%s' or py like '%s' ) ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}
		if ($address) {
			$sql .= " and (address like '%s' or address_receipt like '%s') ";
			$queryParam[] = "%$address%";
			$queryParam[] = "%$address%";
		}
		if ($contact) {
			$sql .= " and (contact01 like '%s' or contact02 like '%s' ) ";
			$queryParam[] = "%{$contact}%";
			$queryParam[] = "%{$contact}%";
		}
		if ($mobile) {
			$sql .= " and (mobile01 like '%s' or mobile02 like '%s' ) ";
			$queryParam[] = "%{$mobile}%";
			$queryParam[] = "%{$mobile}";
		}
		if ($tel) {
			$sql .= " and (tel01 like '%s' or tel02 like '%s' ) ";
			$queryParam[] = "%{$tel}%";
			$queryParam[] = "%{$tel}";
		}
		if ($qq) {
			$sql .= " and (qq01 like '%s' or qq02 like '%s' ) ";
			$queryParam[] = "%{$qq}%";
			$queryParam[] = "%{$qq}";
		}
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::CUSTOMER, "t_customer", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$data = $db->query($sql, $queryParam);
		
		return [
				"customerList" => $result,
				"totalCount" => $data[0]["cnt"]
		];
	}

	/**
	 * 往来单位字段，查询数据
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function queryData($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$queryKey = $params["queryKey"];
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$sql = "select id, code, name, mobile01, tel01, fax, address_receipt, contact01,
					sales_warehouse_id
				from t_customer
				where (record_status = 1000) 
					and (code like '%s' or name like '%s' or py like '%s'
							or mobile01 like '%s' or mobile02 like '%s' ) ";
		$queryParams = [];
		$key = "%{$queryKey}%";
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::CUSTOMER_BILL, "t_customer", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by code limit 20";
		
		$data = $db->query($sql, $queryParams);
		
		$result = [];
		
		$warehouseDAO = new WarehouseDAO($db);
		
		foreach ( $data as $v ) {
			$warehouseId = $v["sales_warehouse_id"];
			$warehouseName = null;
			if ($warehouseId) {
				$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
				if ($warehouse) {
					$warehouseName = $warehouse["name"];
				}
			}
			$result[] = [
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"mobile01" => $v["mobile01"],
					"tel01" => $v["tel01"],
					"fax" => $v["fax"],
					"address_receipt" => $v["address_receipt"],
					"contact01" => $v["contact01"],
					"warehouseId" => $warehouseId,
					"warehouseName" => $warehouseName
			];
		}
		
		return $result;
	}

	/**
	 * 获得某个往来单位的详情
	 *
	 * @param string $id
	 *        	往来单位id
	 * @return array
	 */
	public function customerInfo($id) {
		$db = $this->db;
		
		$result = [];
		
		$sql = "select category_id, code, name, contact01, qq01, mobile01, tel01,
					contact02, qq02, mobile02, tel02, address, address_receipt,
					init_receivables, init_receivables_dt,
					bank_name, bank_account, tax_number, fax, note, sales_warehouse_id,
					record_status
				from t_customer
				where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			$result["categoryId"] = $data[0]["category_id"];
			$result["code"] = $data[0]["code"];
			$result["name"] = $data[0]["name"];
			$result["contact01"] = $data[0]["contact01"];
			$result["qq01"] = $data[0]["qq01"];
			$result["mobile01"] = $data[0]["mobile01"];
			$result["tel01"] = $data[0]["tel01"];
			$result["contact02"] = $data[0]["contact02"];
			$result["qq02"] = $data[0]["qq02"];
			$result["mobile02"] = $data[0]["mobile02"];
			$result["tel02"] = $data[0]["tel02"];
			$result["address"] = $data[0]["address"];
			$result["addressReceipt"] = $data[0]["address_receipt"];
			$result["initReceivables"] = $data[0]["init_receivables"];
			$d = $data[0]["init_receivables_dt"];
			if ($d) {
				$result["initReceivablesDT"] = $this->toYMD($d);
			}
			$result["bankName"] = $data[0]["bank_name"];
			$result["bankAccount"] = $data[0]["bank_account"];
			$result["tax"] = $data[0]["tax_number"];
			$result["fax"] = $data[0]["fax"];
			$result["note"] = $data[0]["note"];
			$result["recordStatus"] = $data[0]["record_status"];
			
			$result["warehouseId"] = null;
			$result["warehouseName"] = null;
			$warehouseId = $data[0]["sales_warehouse_id"];
			if ($warehouseId) {
				$warehouseDAO = new WarehouseDAO($db);
				$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
				if ($warehouse) {
					$result["warehouseId"] = $warehouseId;
					$result["warehouseName"] = $warehouse["name"];
				}
			}
		}
		
		return $result;
	}

	/**
	 * 获得所有的价格体系中的价格
	 */
	public function priceSystemList($params) {
		$db = $this->db;
		
		// id: 往来单位分类id
		$id = $params["id"];
		
		$sql = "select id, name 
				from t_price_system
				order by name";
		$data = $db->query($sql);
		
		$result = [
				[
						"id" => "-1",
						"name" => "[无]"
				]
		];
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"name" => $v["name"]
			];
		}
		
		$psId = null;
		if ($id) {
			$sql = "select ps_id from t_customer_category where id = '%s' ";
			$data = $db->query($sql, $id);
			if ($data) {
				$psId = $data[0]["ps_id"];
			}
		}
		
		return [
				"psId" => $psId,
				"priceList" => $result
		];
	}

	/**
	 * 获得往来单位的销售出库仓库
	 *
	 * @param string $id
	 *        	往来单位id
	 * @return array 仓库, 如果没有设置销售出库仓库则返回null
	 */
	public function getSalesWarehouse(string $id) {
		$db = $this->db;
		
		$sql = "select sales_warehouse_id from t_customer where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return null;
		}
		
		$warehouseId = $data[0]["sales_warehouse_id"];
		
		$sql = "select id, name from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			return null;
		} else {
			return [
					"id" => $data[0]["id"],
					"name" => $data[0]["name"]
			];
		}
	}
}