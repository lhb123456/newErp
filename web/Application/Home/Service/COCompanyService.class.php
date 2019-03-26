<?php

namespace Home\Service;

use Home\DAO\COCompanyDAO;


/**
 * 往来单位Service
 *
 * @author 李静波
 */
class COCompanyService extends PSIBaseExService {
	private $LOG_CATEGORY = "往来单位";

	/**
	 * 往来单位分类列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function categoryList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new COCompanyDAO($this->db());
		return $dao->categoryList($params);
	}

	public function getCategoryInfo($params){
        if ($this->isNotOnline()) {
            return $this->emptyResult();
        }

        $db=$this->db();

        $id=$params["id"];

        $result=[];

        if($id){            //编辑
            $sql="select code,name,limit_count
                  from t_company_category where id='%s'";

            $category=$db->query($sql,$id);

            foreach ($category as $v){
                $result=[
                    "code"=>$v["code"],
                    "name"=>$v["name"],
                    "limitCount"=>sprintf("%.2f",$v["limit_count"])
                ];
            }
        }else{
            $sql="select max(code) as max_code from t_company_category ";
            $code=$db->query($sql);
            $max_code=$code[0]["max_code"];
            $num=$max_code+1;
            if($num<10){
                $newCode="00".$num;
            }else if($num>=10&&$num<100){
                $newCode="0".$num;
            }else{
                $newCode=$num;
            }

            $result=[
                "code"=>$newCode
            ];
        }

        return $result;

    }

	/**
	 * 新建或编辑往来单位分类
	 */
	public function editCategory($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new COCompanyDAO($db);
		
		$log = null;
		
		if ($id) {
			// 编辑
			$rc = $dao->updateCOCompanyCategory($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "编辑往来单位分类: 编码 = {$code}, 分类名 = {$name}";
		} else {
			// 新增
			
			$params["dataOrg"] = $this->getLoginUserDataOrg();
			$params["companyId"] = $this->getCompanyId();
			
			$rc = $dao->addCOCompanyCategory($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $params["id"];
			
			$log = "新增往来单位分类：编码 = {$code}, 分类名 = {$name}";
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 删除往来单位分类
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function deleteCategory($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new COCompanyDAO($db);
		
		$rc = $dao->deleteCOCompanyCategory($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$log = "删除往来单位分类： 编码 = {$params['code']}, 分类名称 = {$params['name']}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 新建或编辑往来单位资料
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function editCustomer($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		
		$ps = new PinyinService();
		$params["py"] = $ps->toPY($name);
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new CustomerDAO($db);
		
		$params["dataOrg"] = $this->getLoginUserDataOrg();
		$params["companyId"] = $this->getCompanyId();
		
		$category = $dao->getCustomerCategoryById($params["categoryId"]);
		if (! $category) {
			$db->rollback();
			return $this->bad("往来单位分类不存在");
		}
		
		$log = null;
		
		if ($id) {
			// 编辑
			$rc = $dao->updateCustomer($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "编辑往来单位：编码 = {$code}, 名称 = {$name}";
		} else {
			// 新增
			$rc = $dao->addCustomer($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $params["id"];
			
			$log = "新增往来单位：编码 = {$code}, 名称 = {$name}";
		}
		
		// 处理应收账款
		$rc = $dao->initReceivables($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 获得某个分类的往来单位列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function customerList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new CustomerDAO($this->db());
		return $dao->customerList($params);
	}

	/**
	 * 删除往来单位资料
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function deleteCustomer($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$db = $this->db();
		$db->startTrans();
		
		$dao = new CustomerDAO($db);
		
		$rc = $dao->deleteCustomer($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$code = $params["code"];
		$name = $params["name"];
		$log = "删除往来单位资料：编码 = {$code},  名称 = {$name}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 往来单位字段，查询数据
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function queryData($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new CustomerDAO($this->db());
		return $dao->queryData($params);
	}

	/**
	 * 获得某个往来单位的详情
	 *
	 * @param string $id
	 *        	往来单位资料id
	 * @return array
	 */
	public function customerInfo($id) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new CustomerDAO($this->db());
		return $dao->customerInfo($id);
	}

	/**
	 * 判断给定id的往来单位是否存在
	 *
	 * @param string $customerId        	
	 *
	 * @return true: 存在
	 */
	public function customerExists($customerId, $db) {
		$dao = new CustomerDAO($db);
		
		$customer = $dao->getCustomerById($customerId);
		
		return $customer != null;
	}

	/**
	 * 根据往来单位Id查询往来单位名称
	 */
	public function getCustomerNameById($customerId, $db) {
		$dao = new CustomerDAO($db);
		
		$customer = $dao->getCustomerById($customerId);
		if ($customer) {
			return $customer["name"];
		} else {
			return "";
		}
	}

	/**
	 * 获得所有的价格体系中的价格
	 */
	public function priceSystemList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new CustomerDAO($this->db());
		return $dao->priceSystemList($params);
	}
}