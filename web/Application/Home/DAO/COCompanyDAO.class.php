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

	public function addCreditAssess(&$params){
	    $db=$this->db;
        //var_dump($params);exit;
        $companyName=trim($params["companyName"]);
        $companyType=json_encode($params["companyType"]);
        $limit=floatval(trim($params["limit"]));
        $companyAssetType=$params["companyAssetType"];
        $companyAddrType=$params["companyAddrType"];
        $otherCompany=trim($params["otherCompany"]);
        $companyTradeType=json_encode($params["companyTradeType"]);
        $companyStrength=$params["companyStrength"];
        $registerAddr=trim($params["registerAddr"]);
        $legalPerson=trim($params["legalPerson"]);
        $registerMoneySubscribe=trim($params["registerMoneySubscribe"]);
        $registerMoneyPaid=trim($params["registerMoneyPaid"]);
        $check=$params["check"];
        $contact=trim($params["contact"]);
        $contactTel=trim($params["contactTel"]);
        $mainWork=trim($params["mainWork"]);
        $operateArea=trim($params["operateArea"]);
        $operateAddr=trim($params["operateAddr"]);
        $assetOffice=$params["assetOffice"];
        $assetWarehouse=$params["assetWarehouse"];
        $assetProductline=$params["assetProductline"];
        $asset=trim($params["asset"]);
        $workTime=$params["workTime"];
        $employeeNum=$params["employeeNum"];
        $assureAgreement=json_encode($params["assureAgreement"]);
        $baseData=json_encode($params["baseData"]);
        $otherData=json_encode($params["otherData"]);
        $tradeBreed=trim($params["tradeBreed"]);
        $isTrade=$params["isTrade"];
        $tradeReason=trim($params["tradeReason"]);
        $position=$params["position"];
        $planQuantity=trim($params["planQuantity"]);
        $tradePeriod=trim($params["tradePeriod"]);
        $influence=trim($params["influence"]);
        $interflow=trim($params["interflow"]);
        $risk=json_encode($params["risk"]);
        $riskDescribe=trim($params["riskDescribe"]);
        $riskKeepaway=trim($params["riskKeepaway"]);
        $dataOrg=$params["dataOrg"];
        $companyId=$params["companyId"];

        $basePermission=$params["basePermission"];
        $analysisPermission=$params["analysisPermission"];
        $riskPermission=$params["riskPermission"];

        if($analysisPermission&&$tradeBreed){
            $tableStaus=2000;
        }elseif($riskPermission&&$params["risk"]){
            $tableStaus=3000;
        }else{
            $tableStaus=1000;
        }

        $id=$this->newId();
        $params['id']=$id;

        $sql="insert into t_credit_assess_record(id,company_name,company_type,assess_times,limit_count,company_asset_type,
             company_addr_type,other_company,company_trade_type,company_strenght,legal_person,register_addr,
             register_money_subscribe,register_money_paid,contact,contact_tel,main_work,check_result,partner_num,operate_area,
             operate_addr,asset_office,asset_warehouse,asset_productline,work_time,employee_num,asset,relate_company,
             base_data,assure_agreement,other_data,trade_breed,is_trade,trade_reason,company_position,plan_quantity,trade_period,
             influence,interflow,risk,risk_describe,risk_keepaway,table_status,status,date_created,date_update,data_org,company_id) 
             values('%s','%s','%s',1,%f,%d,%d,'%s','%s',%d,'%s','%s','%s','%s','%s','%s','%s',%d,1,'%s',
                    '%s',%d,%d,%d,%d,%d,'%s','%s','%s','%s','%s','%s',%d,'%s',%d,'%s','%s','%s','%s',
                    '%s','%s','%s','%s',0,now(),now(),'%s','%s') ";

        $insert=$db->execute($sql,$id,$companyName,$companyType,$limit,$companyAssetType,$companyAddrType,$otherCompany,
                    $companyTradeType,$companyStrength,$legalPerson,$registerAddr,$registerMoneySubscribe,$registerMoneyPaid,
                    $contact,$contactTel,$mainWork,$check,$operateArea,$operateAddr,$assetOffice,$assetWarehouse,$assetProductline,
                    $workTime,$employeeNum,$asset,"",$baseData,$assureAgreement,$otherData,$tradeBreed,$isTrade,$tradeReason,
                    $position,$planQuantity,$tradePeriod,$influence,$interflow,$risk,$riskDescribe,$riskKeepaway,$tableStaus,
                    $dataOrg,$companyId);
        if($insert==false){
            return $this->sqlError(__METHOD__,__LINE__);
        }

        return null;
    }

    public function updateCreditAssess($params){
	    $db=$this->db;

	    $id=$params["id"];
        $companyName=trim($params["companyName"]);
        $companyType=json_encode($params["companyType"]);
        $limit=floatval(trim($params["limit"]));
        $companyAssetType=$params["companyAssetType"];
        $companyAddrType=$params["companyAddrType"];
        $otherCompany=trim($params["otherCompany"]);
        $companyTradeType=json_encode($params["companyTradeType"]);
        $companyStrength=$params["companyStrength"];
        $registerAddr=trim($params["registerAddr"]);
        $legalPerson=trim($params["legalPerson"]);
        $registerMoneySubscribe=trim($params["registerMoneySubscribe"]);
        $registerMoneyPaid=trim($params["registerMoneyPaid"]);
        $check=$params["check"];
        $contact=trim($params["contact"]);
        $contactTel=trim($params["contactTel"]);
        $mainWork=trim($params["mainWork"]);
        $operateArea=trim($params["operateArea"]);
        $operateAddr=trim($params["operateAddr"]);
        $assetOffice=$params["assetOffice"];
        $assetWarehouse=$params["assetWarehouse"];
        $assetProductline=$params["assetProductline"];
        $asset=trim($params["asset"]);
        $workTime=$params["workTime"];
        $employeeNum=$params["employeeNum"];
        $assureAgreement=json_encode($params["assureAgreement"]);
        $baseData=json_encode($params["baseData"]);
        $otherData=json_encode($params["otherData"]);
        $tradeBreed=trim($params["tradeBreed"]);
        $isTrade=$params["isTrade"];
        $tradeReason=trim($params["tradeReason"]);
        $position=$params["position"];
        $planQuantity=trim($params["planQuantity"]);
        $tradePeriod=trim($params["tradePeriod"]);
        $influence=trim($params["influence"]);
        $interflow=trim($params["interflow"]);
        $risk=json_encode($params["risk"]);
        $riskDescribe=trim($params["riskDescribe"]);
        $riskKeepaway=trim($params["riskKeepaway"]);
        $tableStatus=$params["tableStatus"];
        $dataOrg=$params["dataOrg"];
        $companyId=$params["companyId"];

        $basePermission=$params["basePermission"];
        $analysisPermission=$params["analysisPermission"];
        $riskPermission=$params["riskPermission"];

        $sql="select id from t_credit_assess_record where id='%s' ";
        $res=$db->query($sql,$id);
        if(!$res[0]["id"]){
            return $this->bad("本条记录不存在，不可进行编辑");
        }

        if($tableStatus==1000&&$analysisPermission&&$tradeBreed){
            $tableStatus=2000;
        }elseif($tableStatus>=1000&&$riskPermission&&$risk){
            $tableStatus=3000;
        }


        $sql="update t_credit_assess_record 
              set company_name='%s',company_type='%s',limit_count=%f,company_asset_type=%d,company_addr_type=%d,
              other_company='%s',company_trade_type='%s',company_strenght=%d,legal_person='%s',register_addr='%s',
              register_money_subscribe='%s',register_money_paid='%s',contact='%s',contact_tel='%s',main_work='%s',
              check_result=%d,partner_num=1,operate_area='%s',operate_addr='%s',asset_office=%d,asset_warehouse=%d,
              asset_productline=%d,work_time=%d,employee_num=%d,asset='%s',relate_company='',base_data='%s',
              assure_agreement='%s',other_data='%s',trade_breed='%s',is_trade=%d,trade_reason='%s',company_position=%d,
              plan_quantity='%s',trade_period='%s',influence='%s',interflow='%s',risk='%s',risk_describe='%s',
              risk_keepaway='%s',table_status='%s',date_update=now()
              where id='%s'";

        $update=$db->execute($sql,$companyName,$companyType,$limit,$companyAssetType,$companyAddrType,$otherCompany,
                 $companyTradeType,$companyStrength,$legalPerson,$registerAddr,$registerMoneySubscribe,$registerMoneyPaid,
                 $contact,$contactTel,$mainWork,$check,$operateArea,$operateAddr,$assetOffice,$assetWarehouse,
                 $assetProductline,$workTime,$employeeNum,$asset,$baseData,$assureAgreement,$otherData,$tradeBreed,
                 $isTrade,$tradeReason,$position,$planQuantity,$tradePeriod,$influence,$interflow,$risk,$riskDescribe,
                 $riskKeepaway,$tableStatus,$id);

        if($update==false){
            return $this->sqlError(__METHOD__,__LINE__);
        }

        return null;
    }

    public function getAssessInfo($id){
	    $db=$this->db;

	    $sql="select*from t_credit_assess_record where id='%s' ";
	    $data=$db->query($sql,$id);


	    $result=[];
	    foreach ($data as $v){
	        $result["id"]=$v["id"];
	        $result["companyName"]=$v["company_name"];
	        $result["companyType"]=json_decode($v["company_type"]);
            $result["assessTimes"]=$v["assess_times"];
	        $result["limit"]=floatval($v["limit_count"]);
	        $result["companyAssetType"]=$v["company_asset_type"];
	        $result["companyAddrType"]=$v["company_addr_type"];
	        $result["otherCompany"]=$v["other_company"];
	        $result["companyTradeType"]=json_decode($v["company_trade_type"]);
	        $result["companyStrength"]=$v["company_strenght"];
	        $result["registerAddr"]=$v["register_addr"];
	        $result["legalPerson"]=$v["legal_person"];
	        $result["registerMoneySubscribe"]=$v["register_money_subscribe"];
	        $result["registerMoneyPaid"]=$v["register_money_paid"];
	        $result["check"]=$v["check_result"];
	        $result["contact"]=$v["contact"];
	        $result["contactTel"]=$v["contact_tel"];
	        $result["mainWork"]=$v["main_work"];
	        $result["operateArea"]=$v["operate_area"];
	        $result["operateAddr"]=$v["operate_addr"];
	        $result["assetOffice"]=$v["asset_office"];
	        $result["assetWarehouse"]=$v["asset_warehouse"];
	        $result["assetProductline"]=$v["asset_productline"];
	        $result["asset"]=$v["asset"];
	        $result["workTime"]=$v["work_time"];
	        $result["employeeNum"]=$v["employee_num"];
	        $result["assureAgreement"]=json_decode($v["assure_agreement"]);
	        $result["baseData"]=json_decode($v["base_data"]);
	        $result["otherData"]=json_decode($v["other_data"]);
	        $result["tradeBreed"]=$v["trade_breed"];
	        $result["isTrade"]=$v["is_trade"];
	        $result["tradeReason"]=$v["trade_reason"];
	        $result["position"]=$v["company_position"];
	        $result["planQuantity"]=$v["plan_quantity"];
	        $result["tradePeriod"]=$v["trade_period"];
	        $result["influence"]=$v["influence"];
	        $result["interflow"]=$v["interflow"];
	        $result["risk"]=json_decode($v["risk"]);
	        $result["riskDescribe"]=$v["risk_describe"];
	        $result["riskKeepaway"]=$v["risk_keepaway"];
	        $result["tableStatus"]=$v["table_status"];
	        $result["status"]=$v["status"];
        }


        return $result;
    }

    public function creditAssessList($params){
	    $db=$this->db;

	    $companyName=trim($params["companyName"]);
	    $tableStatus=$params["tableStatus"];
	    $basePermission=$params["basePermission"];
	    $analysisPermission=$params["analysisPermission"];
	    $riskPermission=$params["riskPermission"];
	    $start=$params["start"];
	    $limit=$params["limit"];

        $companyId=$params["companyId"];

	    $sql="select id,company_name,company_type,assess_times,limit_count,company_asset_type,company_addr_type,
              company_trade_type,company_strenght,legal_person,register_addr,contact,contact_tel,main_work,
              table_status,status 
	          from t_credit_assess_record 
	          where company_id='%s'";
	    $queryParams=[];
	    $queryParams[]=$companyId;

	    if($tableStatus==0){
	        $sql.=" and table_status=0";
        }else if($tableStatus==1){
	        if($basePermission){
                $sql.=" and table_status>0 and table_status<4000";
            }
            if(!$basePermission&&$analysisPermission){
                $sql.=" and table_status>0 and table_status<4000";
            }
            if(!$basePermission&&!$analysisPermission&&$riskPermission){
                $sql.=" and table_status>1000 and table_status<4000";
            }

        }else if($tableStatus==2){
            $sql.=" and table_status=4000";
        }

	    if($companyName){
	        $sql.=" and company_name like '%s' ";
	        $queryParams[]="%{$companyName}%";
        }

        $sql2=$sql;
	    $countData=$db->query($sql2,$queryParams);
	    $cnt=count($countData);

	    $sql.=" order by date_update,company_name ";
	    $sql.=" limit %d,%d ";
	    $queryParams[]=$start;
	    $queryParams[]=$limit;

	    $data=$db->query($sql,$queryParams);

	    $result=[];
	    foreach ($data as $v){

	        $result[]=[
	            "id"=>$v["id"],
	            "companyName"=>$v["company_name"],
	            "assessTimes"=>$v["assess_times"],
	            "companyType"=>json_decode($v["company_type"]),
	            "limit"=>floatval($v["limit_count"]),
	            "companyAssetType"=>$v["company_asset_type"],
	            "companyAddrType"=>$v["company_addr_type"],
	            "companyTradeType"=>json_decode($v["company_trade_type"]),
	            "companyStrength"=>$v["company_strenght"],
	            "registerAddr"=>$v["register_addr"],
	            "legalPerson"=>$v["legal_person"],
	            "contact"=>$v["contact"],
	            "contactTel"=>$v["contact_tel"],
	            "mainWork"=>$v["main_work"],
	            "tableStatus"=>$v["table_status"],
	            "status"=>$v["status"]
            ];

        }

        return [
            "dataList"=>$result,
            "totalCount"=>$cnt
        ];
    }

    public function deleteAssess($id){
	    $db=$this->db;

	    $sql="select id from t_credit_assess_record where id='%s' and table_status>0";
	    $assess=$db->query($sql,$id);

	    if(!$assess[0]["id"]){
	        return $this->bad("本条记录不存在或已删除");
        }

        $sql="update t_credit_assess_record set table_status=0,date_update=now() where id='%s'";
	    $res=$db->execute($sql,$id);
	    if($res==false){
	        return $this->sqlError(__METHOD__,__LINE__);
        }

        return null;
    }

    public function commitAssess($id){
	    $db=$this->db;

        $sql="select id from t_credit_assess_record where id='%s' and table_status>0";
        $assess=$db->query($sql,$id);
        
        if(!$assess[0]["id"]){
            return $this->bad("本条记录不存在或已删除");
        }

        $sql="update t_credit_assess_record set table_status=4000,date_update=now() where id='%s'";
        $res=$db->execute($sql,$id);
        if($res==false){
            return $this->sqlError(__METHOD__,__LINE__);
        }

        return null;
    }

    public function selectAssessCompany($params){
	    $db=$this->db;

	    $id=$params["id"];
	    $company=trim($params["company"]);
	    $start=$params["start"];
	    $limit=$params["limit"];

        $companyId=$params["companyId"];

        $sql="select id,company_name,company_type,company_asset_type,
              legal_person,register_addr,table_status,status 
	          from t_credit_assess_record 
	          where company_id='%s' and table_status=4000 and status=0";
        $queryParams=[];
        $queryParams[]=$companyId;


        if($company){
            $sql.=" and company_name like '%s' ";
            $queryParams[]="%{$company}%";
        }

        $sql2=$sql;
        $countData=$db->query($sql2,$queryParams);
        $cnt=count($countData);

        $sql.=" order by date_update,company_name ";
        $sql.=" limit %d,%d ";
        $queryParams[]=$start;
        $queryParams[]=$limit;

        $data=$db->query($sql,$queryParams);
        $result=[];
        foreach ($data as $v){

            $result[]=[
                "id"=>$v["id"],
                "companyName"=>$v["company_name"],
                "companyType"=>json_decode($v["company_type"]),
                "companyAssetType"=>$v["company_asset_type"],
                "registerAddr"=>$v["register_addr"],
                "legalPerson"=>$v["legal_person"],
                "tableStatus"=>$v["table_status"],
                "status"=>$v["status"]
            ];

        }

        return [
            "dataList"=>$result,
            "totalCount"=>$cnt
        ];
    }
}