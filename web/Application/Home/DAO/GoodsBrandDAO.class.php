<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 商品品牌 DAO
 *
 * @author
 */
class GoodsBrandDAO extends PSIBaseExDAO {

	/**
	 * 用递归调用的方式查询所有品牌
	 */
	private function allBrandsInternal($db, $parentId, $rs) {
		$result = [];
		$sql = "select id, name, full_name
				from t_goods_brand b
				where (parent_id = '%s')
				";
		$queryParam = [];
		$queryParam[] = $parentId;
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " order by name";
		$data = $db->query($sql, $queryParam);
		foreach ( $data as $v ) {
			$id = $v["id"];
			$fullName = $v["full_name"];
			if (! $fullName) {
				$fullName = $v["name"];
			}
			
			$children = $this->allBrandsInternal($db, $id, $rs); // 自身递归调用
			
			$result[] = [
					"id" => $id,
					"text" => $v["name"],
					"fullName" => $fullName,
					"children" => $children,
					"leaf" => count($children) == 0,
					"expanded" => true,
					"iconCls" => "PSI-GoodsBrand"
			];
		}

		return $result;
	}

	/**
	 * 获得所有的品牌
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function allBrands($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}

		$result = [];
		$sql = "select id, name, full_name
				from t_goods_brand b
				where (parent_id is null)
				";
		$queryParam = [];
		/*$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::GOODS_BRAND, "b", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}*/
		
		$sql .= " order by name";
		
		$data = $db->query($sql, $queryParam);
		$result = [];
		foreach ( $data as $v ) {
			$id = $v["id"];
			$fullName = $v["full_name"];
			if (! $fullName) {
				$fullName = $v["name"];
			}
			
			$children = $this->allBrandsInternal($db, $id, $rs);
			
			$result[] = [
					"id" => $id,
					"text" => $v["name"],
					"fullName" => $fullName,
					"children" => $children,
					"leaf" => count($children) == 0,
					"expanded" => true,
					"iconCls" => "PSI-GoodsBrand"
			];
		}
		
		return $result;
	}

    public function getBrands($params) {
        $db = $this->db;

        $loginUserId = $params["loginUserId"];
        if ($this->loginUserIdNotExists($loginUserId)) {
            return $this->emptyResult();
        }

        $result = [];
        $sql = "select id, name, full_name
				from t_goods_brand b
				where (parent_id is null)
				";
        $queryParam = [];

        $sql .= " order by name";

        $data = $db->query($sql, $queryParam);
        $result = [];
        foreach ( $data as $v ) {
            $id = $v["id"];
            $fullName = $v["full_name"];
            if (! $fullName) {
                $fullName = $v["name"];
            }

            //$children = $this->getBrandsInternal($db, $id, $rs);
            $children=array();
            $result[] = [
                "id" => $id,
                "text" => $v["name"],
                "fullName" => $fullName,
                "children" => $children,
                "leaf" => count($children) == 0,
                "expanded" => true,
                "iconCls" => "PSI-GoodsBrand"
            ];
        }

        return $result;
    }

	/**
	 * 新增商品品牌
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function addBrand(& $params) {
		$db = $this->db;
		
		$name = $params["name"];
		$parentId = $params["parentId"];
		
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		
		// 检查上级品牌是否存在
		$fullName = $name;
		if ($parentId) {
			$sql = "select full_name
					from t_goods_brand
					where id = '%s' ";
			$data = $db->query($sql, $parentId);
			if (! $data) {
				return $this->bad("所选择的上级商品品牌不存在");
			}
			$fullName = $data[0]["full_name"] . "\\" . $name;
		}
		
		$id = $this->newId();
		if ($parentId) {
			$sql = "insert into t_goods_brand(id, name, full_name, parent_id, data_org, company_id)
					values ('%s', '%s', '%s', '%s', '%s', '%s')";
			$rc = $db->execute($sql, $id, $name, $fullName, $parentId, "", "");
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		} else {
			$sql = "insert into t_goods_brand(id, name, full_name, parent_id, data_org, company_id)
					values ('%s', '%s', '%s', null, '%s', '%s')";
			$rc = $db->execute($sql, $id, $name, $fullName, $dataOrg, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		$params["id"] = $id;
		
		// 操作成功
		return null;
	}

	/**
	 * 更新子品牌的FullName
	 *
	 * @param \Think\Model $db        	
	 * @param string $parentId        	
	 */
	private function updateSubBrandsFullName($db, $parentId) {
		$sql = "select full_name from t_goods_brand where id = '%s' ";
		$data = $db->query($sql, $parentId);
		if (! $data) {
			return;
		}
		
		$parentFullName = $data[0]["full_name"];
		$sql = "select id, name
				from t_goods_brand
				where parent_id = '%s' ";
		$data = $db->query($sql, $parentId);
		foreach ( $data as $i => $v ) {
			$id = $v["id"];
			$fullName = $parentFullName . "\\" . $v["name"];
			$sql = "update t_goods_brand
					set full_name = '%s'
					where id = '%s' ";
			$db->execute($sql, $fullName, $id);
			
			// 递归调用自身
			$this->updateSubBrandsFullName($db, $id);
		}
	}

	/**
	 * 编辑商品品牌
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function updateGoodsBrand(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		$name = $params["name"];
		$parentId = $params["parentId"];
		
		// 检查品牌是否存在
		$brand = $this->getBrandById($id);
		if (! $brand) {
			return $this->bad("要编辑的品牌不存在");
		}
		
		if ($parentId) {
			// 检查上级品牌是否存在
			$sql = "select full_name
					from t_goods_brand
					where id = '%s' ";
			$data = $db->query($sql, $parentId);
			if (! data) {
				return $this->bad("选择的上级品牌不存在");
			}
			$parentFullName = $data[0]["full_name"];
			
			// 上级品牌不能是自身
			if ($parentId == $id) {
				return $this->bad("上级品牌不能是自身");
			}
			
			// 检查下级品牌不能是作为上级品牌
			$tempParentId = $parentId;
			while ( $tempParentId != null ) {
				$sql = "select parent_id
							from t_goods_brand
							where id = '%s' ";
				$data = $db->query($sql, $tempParentId);
				if ($data) {
					$tempParentId = $data[0]["parent_id"];
				} else {
					$tempParentId = null;
				}
				
				if ($tempParentId == $id) {
					return $this->bad("下级品牌不能作为上级品牌");
				}
			}
		}
		
		if ($parentId) {
			$fullName = $parentFullName . "\\" . $name;
			$sql = "update t_goods_brand
					set name = '%s', parent_id = '%s', full_name = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $name, $parentId, $fullName, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		} else {
			$sql = "update t_goods_brand
					set name = '%s', parent_id = null, full_name = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $name, $name, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 同步下级品牌的full_name
		$this->updateSubBrandsFullName($db, $id);
		
		// 操作成功
		return null;
	}

	/**
	 * 通过品牌id查询品牌
	 *
	 * @param string $id        	
	 * @return array|NULL
	 */
	public function getBrandById($id) {
		$db = $this->db;
		
		$sql = "select name, full_name 
				from t_goods_brand 
				where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			return [
					"name" => $data[0]["name"],
					"fullName" => $data[0]["full_name"]
			];
		} else {
			return null;
		}
	}

	/**
	 * 删除商品品牌
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function deleteBrand(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		$brand = $this->getBrandById($id);
		if (! $brand) {
			return $this->bad("要删除的商品品牌不存在");
		}
		$fullName = $brand["fullName"];
		
		$sql = "select count(*) as cnt from t_goods
				where brand_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("品牌[$fullName]已经在商品中使用，不能删除");
		}
		
		$sql = "select count(*) as cnt from t_goods_brand where parent_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("品牌[$fullName]还有子品牌，所以不能被删除");
		}
		
		$sql = "delete from t_goods_brand where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["fullName"] = $fullName;
		
		// 操作成功
		return null;
	}

	/**
	 * 获得某个品牌的上级品牌全称
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function brandParentName($params) {
		$db = $this->db;
		
		$result = [];
		
		$id = $params["id"];
		
		$sql = "select name, parent_id
				from t_goods_brand
				where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $result;
		}
		
		$result["name"] = $data[0]["name"];
		$parentId = $data[0]["parent_id"];
		$result["parentBrandId"] = $parentId;
		if ($parentId) {
			$sql = "select full_name
					from t_goods_brand
					where id = '%s' ";
			$data = $db->query($sql, $parentId);
			if ($data) {
				$result["parentBrandName"] = $data[0]["full_name"];
			} else {
				$result["parentBrandId"] = null;
				$result["parentBrandName"] = null;
			}
		} else {
			$result["parentBrandName"] = null;
		}
		
		return $result;
	}
}