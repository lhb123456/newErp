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
	public function addCOCompany(& $params) {
		$db = $this->db;
		
		$code = $params["code"];
		$name = trim($params["name"]);
		$anotherName = trim($params["anotherName"]);
        $address = $params["address"];
		$categoryId = $params["categoryId"];
		$companyType = $params["companyType"];
		$rankId = $params["rankId"];
		$limitCount = trim($params["limitCount"]);
		$addressInvoice = $params["addressInvoice"];
		$invoiceTel = $params["invoiceTel"];
        $bankName = $params["bankName"];
        $bankAccount = $params["bankAccount"];
        $legalPerson = $params["legalPerson"];
        $registerMoney = $params["registerMoney"];
        $companyIntro = $params["companyIntro"];
		$addressReceipt = $params["addressReceipt"];
		$contact = $params["contact"];
		$mobile = $params["mobile"];
		$tel = $params["tel"];
		$qq = $params["qq"];
		$tax = $params["tax"];
		$fax = $params["fax"];
		$memo = $params["memo"];
		$py = $params["py"];
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
        $status = $params["status"];

		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		

		// 检查编码是否已经存在
		$sql = "select count(*) as cnt from t_co_company where name = '%s' ";
		$data = $db->query($sql, $name);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("名称为 [{$name}] 的往来单位已经存在");
		}

		if(in_array("customer",$companyType)){
		    $is_customer=1;
        }else{
		    $is_customer=0;
        }

        if(in_array("supplier",$companyType)){
            $is_supplier=1;
        }else{
            $is_supplier=0;
        }

		$id = $this->newId();
		$params["id"] = $id;
		
		$sql = "insert into t_co_company (id, category_id,is_customer,is_supplier, code, name,another_name, py,
                    rank_id,limit_count,address_invoice,invoice_tel,bank_name, bank_account,legal_person,register_money,
                    company_intro,contact,qq, tel, mobile, address, address_receipt,tax_number, fax, memo, data_org,
                    company_id,date_created,date_update,status)
				values ('%s', '%s', %d, %d, '%s', '%s','%s', '%s', '%s', %f, '%s', '%s', '%s', '%s', '%s','%s',
						'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s','%s','%s','%s',now(),now(),%d)  ";
		$rc = $db->execute($sql, $id, $categoryId,$is_customer,$is_supplier, $code, $name,$anotherName, $py,
                $rankId,$limitCount,$addressInvoice,$invoiceTel,$bankName,$bankAccount,$legalPerson,$registerMoney,
                $companyIntro,$contact,$qq,$tel,$mobile,$address,$addressReceipt,$tax,$fax,$memo,$dataOrg,
                $companyId,$status);
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
	public function updateCOCompany(& $params) {
		$db = $this->db;

		$id=$params["id"];
        $name = trim($params["name"]);
        $anotherName = trim($params["anotherName"]);
        $address = $params["address"];
        $categoryId = $params["categoryId"];
        $companyType = $params["companyType"];
        $rankId = $params["rankId"];
        $limitCount = trim($params["limitCount"]);
        $addressInvoice = $params["addressInvoice"];
        $invoiceTel = $params["invoiceTel"];
        $bankName = $params["bankName"];
        $bankAccount = $params["bankAccount"];
        $legalPerson = $params["legalPerson"];
        $registerMoney = $params["registerMoney"];
        $companyIntro = $params["companyIntro"];
        $addressReceipt = $params["addressReceipt"];
        $contact = $params["contact"];
        $mobile = $params["mobile"];
        $tel = $params["tel"];
        $qq = $params["qq"];
        $tax = $params["tax"];
        $fax = $params["fax"];
        $memo = $params["memo"];
        $py = $params["py"];
        $status = $params["status"];

        if(in_array("customer",$companyType)){
            $is_customer=1;
        }else{
            $is_customer=0;
        }

        if(in_array("supplier",$companyType)){
            $is_supplier=1;
        }else{
            $is_supplier=0;
        }
		
		$sql = "update t_co_company
					set  category_id = '%s',is_customer=%d,is_supplier=%d,name = '%s',another_name='%s', py = '%s',
					rank_id='%s',limit_count=%f,address_invoice='%s',invoice_tel='%s',bank_name='%s',bank_account='%s',
					legal_person='%s',register_money='%s',company_intro='%s',contact = '%s', qq = '%s', tel = '%s',
					mobile = '%s',address = '%s', address_receipt = '%s',tax_number = '%s',fax = '%s',
                    memo='%s',status = %d,date_update=now()
				where id = '%s'  ";
		
		$rc = $db->execute($sql, $categoryId,$is_customer,$is_supplier, $name,$anotherName, $py,
                $rankId,$limitCount,$addressInvoice,$invoiceTel,$bankName, $bankAccount,
                $legalPerson,$registerMoney,$companyIntro,$contact, $qq, $tel,
				$mobile,$address, $addressReceipt,$tax, $fax, $memo, $status, $id);
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
	public function cocompanyList($params) {
		$db = $this->db;
		
		$categoryId = $params["categoryId"];
		$companyType=$params["companyType"];
		$name = trim($params["name"]);
		$categoryName = trim($params["categoryName"]);
        $page = $params["page"];
        $start = $params["start"];
        $limit = $params["limit"];
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$sql = "select c.*,r.rank
		        from t_co_company c,t_company_category cg,t_rank r 
		        where c.category_id=cg.id and c.rank_id=r.id and cg.id='%s'";
		$queryParam = [];
		$queryParam[] = $categoryId;

		if(in_array("customer",$companyType)){
		    $sql=" and c.is_customer=1 ";
        }

        if(in_array("supplier",$companyType)){
		    $sql=" and c.is_supplier=1 ";
        }
		if ($name) {
			$sql .= " and (c.name like '%s' or c.py like '%s' ) ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}

		if($categoryName){
		    $sql.= " and cg.name like '%s' ";
		    $queryParam[]="%{$categoryName}%";
        }
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::CO_COMPANY, "c", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}

		$sql2=$sql;

		$count=$db->query($sql,$queryParam);
		$cnt=count($count);

		$sql .= " order by code limit %d, %d";
		$queryParam[] = $start;
		$queryParam[] = $limit;
		$result = [];
		$data = $db->query($sql, $queryParam);

		foreach ( $data as $v ) {

			$result[] = [
					"id" => $v["id"],
					"categoryId" => $v["category_id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"anotherName" => $v["another_name"],
					"address" => $v["address"],
                    "is_customer"=>$v["is_customer"],
                    "is_supplier"=>$v["is_supplier"],
                    "rankId"=>$v["rank_id"],
                    "rank"=>$v["rank"],
                    "limitCount"=>$v["limit_count"],
                    "addressInvoice"=>$v["address_invoice"],
                    "invoiceTel"=>$v["invoice_tel"],
                    "bankName" => $v["bank_name"],
                    "bankAccount" => $v["bank_account"],
					"addressReceipt" => $v["address_receipt"],
					"contact" => $v["contact"],
					"qq" => $v["qq"],
					"tel" => $v["tel"],
					"mobile" => $v["mobile"],
					"tax" => $v["tax_number"],
					"fax" => $v["fax"],
                    "legalPerson"=>$v["legal_person"],
                    "registerMoney"=>$v["register_money"],
                    "companyIntro"=>$v["company_intro"],
					"memo" => $v["memo"],
					"dataOrg" => $v["data_org"],
					"status" => $v["status"]
			];
		}
		

		return [
				"cocompanyList" => $result,
				"totalCount" =>$cnt
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
	public function cocompanyInfo($id) {
		$db = $this->db;
		
		$result = [];

        $sql = "select c.*,r.rank
		        from t_co_company c,t_rank r 
		        where  c.rank_id=r.id and c.id='%s'";
		$data = $db->query($sql, $id);


		if ($data) {
			$result["categoryId"] = $data[0]["category_id"];
			$result["code"] = $data[0]["code"];
			$result["name"] = $data[0]["name"];
			$result["anotherName"] = $data[0]["another_name"];
            $result["address"] = $data[0]["address"];
            $result["is_customer"] = $data[0]["is_customer"];
            $result["is_supplier"] = $data[0]["is_supplier"];
            $result["rankId"] = $data[0]["rank_id"];
            $result["rank"] = $data[0]["rank"];
            $result["limitCount"] = $data[0]["limit_count"];
            $result["addressInvoice"] = $data[0]["address_invoice"];
            $result["invoiceTel"] = $data[0]["invoice_tel"];
            $result["bankName"] = $data[0]["bank_name"];
            $result["bankAccount"] = $data[0]["bank_account"];
            $result["addressReceipt"] = $data[0]["address_receipt"];
			$result["contact"] = $data[0]["contact"];
			$result["qq"] = $data[0]["qq"];
			$result["mobile"] = $data[0]["mobile"];
			$result["tel"] = $data[0]["tel"];
			$result["initReceivables"] = $data[0]["init_receivables"];
			$result["tax"] = $data[0]["tax_number"];
			$result["fax"] = $data[0]["fax"];
			$result["memo"] = $data[0]["memo"];
			$result["dataOrg"] = $data[0]["data_org"];
			$result["status"] = $data[0]["status"];

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