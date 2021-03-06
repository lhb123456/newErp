<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 商品DAO
 *
 * @author
 */
class GoodsDAO extends PSIBaseExDAO {

	/**
	 * 商品列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function goodsList($params) {
		$db = $this->db;
		
		$categoryId = $params["categoryId"];
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$barCode = $params["barCode"];
		
		$start = $params["start"];
		$limit = $params["limit"];
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$result = [];
		$sql = "select g.id, g.code,g.method, g.name, g.sale_price, g.spec,  g.unit_id, u.name as unit_name,
					g.purchase_price, g.bar_code, g.memo, g.data_org, g.brand_id,g.tax_rate
				from t_goods g, t_goods_unit u
				where (g.unit_id = u.id) and (g.category_id = '%s') ";
		$queryParam = [];
		$queryParam[] = $categoryId;
		/*$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::GOODS, "g", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}*/
		
		if ($code) {
			$sql .= " and (g.code like '%s') ";
			$queryParam[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (g.name like '%s' or g.py like '%s') ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}
		if ($spec) {
			$sql .= " and (g.spec like '%s')";
			$queryParam[] = "%{$spec}%";
		}
		if ($barCode) {
			$sql .= " and (g.bar_code = '%s') ";
			$queryParam[] = $barCode;
		}
		
		$sql .= " order by g.code limit %d, %d";
		$queryParam[] = $start;
		$queryParam[] = $limit;
		$data = $db->query($sql, $queryParam);
		
		foreach ( $data as $v ) {
			$brandId = $v["brand_id"];
			$brandFullName = $brandId ? $this->getBrandFullNameById($db, $brandId) : null;
			
			$result[] = [
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"taxRate" => $v["tax_rate"],
					"salePrice" => $v["sale_price"],
					"spec" => $v["spec"],
					"unitId" => $v["unit_id"],
					"unitName" => $v["unit_name"],
					"purchasePrice" => $v["purchase_price"] == 0 ? null : $v["purchase_price"],
					"barCode" => $v["bar_code"],
					"memo" => $v["memo"],
					"dataOrg" => $v["data_org"],
					"method" => $v["method"],
					"brandFullName" => $brandFullName
			];
		}
		
		$sql = "select count(*) as cnt from t_goods g where (g.category_id = '%s') ";
		$queryParam = [];
		$queryParam[] = $categoryId;
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::GOODS, "g", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		if ($code) {
			$sql .= " and (g.code like '%s') ";
			$queryParam[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (g.name like '%s' or g.py like '%s') ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}
		if ($spec) {
			$sql .= " and (g.spec like '%s')";
			$queryParam[] = "%{$spec}%";
		}
		if ($barCode) {
			$sql .= " and (g.bar_code = '%s') ";
			$queryParam[] = $barCode;
		}
		
		$data = $db->query($sql, $queryParam);
		$totalCount = $data[0]["cnt"];
		
		return [
				"goodsList" => $result,
				"totalCount" => $totalCount
		];
	}

	private function getBrandFullNameById($db, $brandId) {
		$sql = "select full_name from t_goods_brand where id = '%s' ";
		$data = $db->query($sql, $brandId);
		if ($data) {
			return $data[0]["full_name"];
		} else {
			return null;
		}
	}

	/**
	 * 新增商品
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function addGoods(& $params) {
		$db = $this->db;
		
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$categoryId = $params["categoryId"];
		$unitId = $params["unitId"];
		$salePrice = $params["salePrice"];
		$purchasePrice = $params["purchasePrice"];
		$method = $params["method"];
		$memo = $params["memo"];
		$brandId = $params["brandId"];
		$bizUser=$params["loginUserId"];
		$taxRate=$params["taxRate"];

		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		
		$py = $params["py"];
		$specPY = $params["specPY"];
		
		$goodsUnitDAO = new GoodsUnitDAO($db);
		$unit = $goodsUnitDAO->getGoodsUnitById($unitId);
		if (! $unit) {
			return $this->bad("计量单位不存在");
		}
		
		$goodsCategoryDAO = new GoodsCategoryDAO($db);
		$category = $goodsCategoryDAO->getGoodsCategoryById($categoryId);
		if (! $category) {
			return $this->bad("商品分类不存在");
		}
		
		// 检查商品品牌
		if ($brandId) {
			$brandDAO = new GoodsBrandDAO($db);
			$brand = $brandDAO->getBrandById($brandId);
			if (! $brand) {
				return $this->bad("商品品牌不存在");
			}
		}
		
		// 检查商品编码是否唯一
		$sql = "select count(*) as cnt from t_goods where code = '%s' ";
		$data = $db->query($sql, $code);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [{$code}]的商品已经存在");
		}
		
		// 如果录入了条形码，则需要检查条形码是否唯一
		/*if ($barCode) {
			$sql = "select count(*) as cnt from t_goods where bar_code = '%s' ";
			$data = $db->query($sql, $barCode);
			$cnt = $data[0]["cnt"];
			if ($cnt != 0) {
				return $this->bad("条形码[{$barCode}]已经被其他商品使用");
			}
		}
		*/
		$id = $this->newId();
		$sql = "insert into t_goods (id, code, name, spec, category_id, unit_id, sale_price,
					py, purchase_price, bar_code, memo, data_org, company_id, spec_py, brand_id,tax_rate,method)
				values ('%s', '%s', '%s', '%s', '%s', '%s', %f, '%s', %f, '%s', '%s', '%s', '%s', '%s',
					if('%s' = '', null, '%s'),'%s','%s')";
		$rc = $db->execute($sql, $id, $code, $name, $spec, $categoryId, $unitId, $salePrice, $py, 
				$purchasePrice, "", $memo, "", "", $specPY, $brandId, $brandId,$taxRate,$method);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}

		//现货价格
        if($salePrice||$purchasePrice){
            $sql="insert into t_currentPrice(goods_id,po_price,so_price,price_date,input_user_id,create_date,memo)
              values('%s',%f,%f,now(),'%s',now(),'%s') ";
            $res=$db->execute($sql,$id,$purchasePrice,$salePrice,$bizUser,"");
            if($res==false){
                return $this->sqlError(__METHOD__,__LINE__);
            }
        }
		
		$params["id"] = $id;
		
		// 操作成功
		return null;
	}

	/**
	 * 编辑商品
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function updateGoods(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$categoryId = $params["categoryId"];
		$unitId = $params["unitId"];
		$salePrice = $params["salePrice"];
		$oldSalePrice = $params["oldSalePrice"];
		$purchasePrice = $params["purchasePrice"];
		$oldPurchasePrice = $params["oldPurchasePrice"];
		$method = $params["method"];
		$memo = $params["memo"];
		$brandId = $params["brandId"];
		$bizUser=$params["loginUserId"];
		$taxRate=$params["taxRate"];

		$py = $params["py"];
		$specPY = $params["specPY"];
		
		$goods = $this->getGoodsById($id);
		if (! $goods) {
			return $this->bad("要编辑的商品不存在");
		}
		
		$goodsUnitDAO = new GoodsUnitDAO($db);
		$unit = $goodsUnitDAO->getGoodsUnitById($unitId);
		if (! $unit) {
			return $this->bad("计量单位不存在");
		}
		
		$goodsCategoryDAO = new GoodsCategoryDAO($db);
		$category = $goodsCategoryDAO->getGoodsCategoryById($categoryId);
		if (! $category) {
			return $this->bad("商品分类不存在");
		}
		
		// 检查商品品牌
		if ($brandId) {
			$brandDAO = new GoodsBrandDAO($db);
			$brand = $brandDAO->getBrandById($brandId);
			if (! $brand) {
				return $this->bad("商品品牌不存在");
			}
		}
		
		// 编辑
		// 检查商品编码是否唯一
		$sql = "select count(*) as cnt from t_goods where code = '%s' and id <> '%s' ";
		$data = $db->query($sql, $code, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [{$code}]的商品已经存在");
		}
		
		// 如果录入了条形码，则需要检查条形码是否唯一
		/*if ($barCode) {
			$sql = "select count(*) as cnt from t_goods where bar_code = '%s' and id <> '%s' ";
			$data = $db->query($sql, $barCode, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt != 0) {
				return $this->bad("条形码[{$barCode}]已经被其他商品使用");
			}
		}*/
		
		$sql = "update t_goods
				set code = '%s', name = '%s', spec = '%s', category_id = '%s',
				    unit_id = '%s', sale_price = %f, py = '%s', purchase_price = %f,
					 memo = '%s', spec_py = '%s',tax_rate = '%s',
					brand_id = if('%s' = '', null, '%s'),method = '%s' 
				where id = '%s' ";
		
		$rc = $db->execute($sql, $code, $name, $spec, $categoryId, $unitId, $salePrice, $py, 
				$purchasePrice,  $memo, $specPY,$taxRate ,$brandId, $brandId,$method, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}

		//新增现货价格
        if($salePrice!=$oldSalePrice||$purchasePrice!=$oldPurchasePrice){
            $sql="insert into t_currentPrice(goods_id,po_price,so_price,price_date,input_user_id,create_date,memo)
              values('%s',%f,%f,now(),'%s',now(),'%s') ";
            $res=$db->execute($sql,$id,$purchasePrice,$salePrice,$bizUser,"");
            if($res==false){
                return $this->sqlError(__METHOD__,__LINE__);
            }
        }
		
		// 操作成功
		return null;
	}

	/**
	 * 通过商品id查询商品
	 *
	 * @param string $id        	
	 * @return array|NULL
	 */
	public function getGoodsById($id) {
		$db = $this->db;
		
		$sql = "select code, name, spec from t_goods where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			return array(
					"code" => $data[0]["code"],
					"name" => $data[0]["name"],
					"spec" => $data[0]["spec"]
			);
		} else {
			return null;
		}
	}

	/**
	 * 删除商品
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function deleteGoods(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$goods = $this->getGoodsById($id);
		if (! $goods) {
			return $this->bad("要删除的商品不存在");
		}
		$code = $goods["code"];
		$name = $goods["name"];
		$spec = $goods["spec"];
		
		// 判断商品是否能删除
		$sql = "select count(*) as cnt from t_po_bill_detail where goods_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("商品[{$code} {$name}]已经在采购订单中使用了，不能删除");
		}
		
		$sql = "select count(*) as cnt from t_pw_bill_detail where goods_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("商品[{$code} {$name}]已经在采购入库单中使用了，不能删除");
		}
		
		$sql = "select count(*) as cnt from t_ws_bill_detail where goods_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("商品[{$code} {$name}]已经在销售出库单中使用了，不能删除");
		}
		
		$sql = "select count(*) as cnt from t_inventory_detail where goods_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("商品[{$code} {$name}]在业务中已经使用了，不能删除");
		}
		
		$sql = "delete from t_goods where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["code"] = $code;
		$params["name"] = $name;
		$params["spec"] = $spec;
		
		// 操作成功
		return null;
	}

	/**
	 * 商品字段，查询数据
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function queryData($params) {
		$db = $this->db;
		
		$queryKey = $params["queryKey"];
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$key = "%{$queryKey}%";
		
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name
				from t_goods g, t_goods_unit u
				where (g.unit_id = u.id)
				and (g.code like '%s' or g.name like '%s' or g.py like '%s'
					or g.spec like '%s' or g.spec_py like '%s') ";
		$queryParams = [];
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::GOODS_BILL, "g", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by g.code
				limit 20";
		$data = $db->query($sql, $queryParams);
		$result = [];
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"spec" => $v["spec"],
					"unitName" => $v["unit_name"]
			];
		}
		
		return $result;
	}
    public function queryCodeData($params) {
            $db = $this->db;

            $queryKey = $params["queryKey"];
            if ($queryKey == null) {
                $queryKey = "";
            }
            $key = "%{$queryKey}%";

            $sql = "select id,merge_code,name,cate_name,memo 
                    from t_tax_code where status = 1 AND 
                     (merge_code like '%s' or name like '%s' or cate_name like '%s') ";
            $queryParams = [];
            $queryParams[] = $key;
            $queryParams[] = $key;
            $queryParams[] = $key;
            $queryParams[] = $key;
            $sql .= " order by merge_code
                     limit 1000";
            $data = $db->query($sql, $queryParams);
            $result = [];
            foreach ( $data as $v ) {
                $result[] = [
                        "id" => $v["id"],
                        "code" => $v["merge_code"],
                        "name" => $v["name"],
                        "cate_name" => $v["cate_name"],
                        "memo" => $v["memo"]
                ];
            }
            return $result;
        }

	private function getPsIdForCustomer($customerId) {
		$result = null;
		$db = $this->db;
		$sql = "select c.ps_id
				from t_customer_category c, t_customer u
				where c.id = u.category_id and u.id = '%s' ";
		$data = $db->query($sql, $customerId);
		if ($data) {
			$result = $data[0]["ps_id"];
		}
		
		return $result;
	}

	/**
	 * 商品字段，查询数据
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function queryDataWithSalePrice($params) {
		$db = $this->db;
		
		$queryKey = $params["queryKey"];
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$customerId = $params["customerId"];
		$companyId = $params["companyId"];

		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$key = "%{$queryKey}%";
		
		$sql = "select  g.id, g.code, g.brand_id,g.tax_rate,g.name,g.category_id, g.spec, u.name as unit_name, g.sale_price, g.memo
				from t_goods g, t_goods_unit u
				where (g.unit_id = u.id)
				and (g.code like '%s' or g.name like '%s' or g.py like '%s'
					or g.spec like '%s' or g.spec_py like '%s') ";

		$queryParams = [];
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		
		/*$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::GOODS_BILL, "g", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}*/
		
		$sql .= " order by g.py ";
		$data = $db->query($sql, $queryParams);
		$result = [];
		foreach ( $data as $v ) {
			$priceSystem = "";
			
			$price = $v["sale_price"];
            $category=M("t_goods_category")->where("id ='".$v["category_id"]."'")->find();
            $brand=M("t_goods_brand")->where("id='".$v["brand_id"]."'")->find();
            if($category["parent_id"]){
                $top_category=M("t_goods_category")->where("id ='".$category["parent_id"]."'")->find();
            }else{
                $top_category["name"]="无";
                $top_category["code"]="无";
            }
            // 取价格体系里面的价格
            $goodsId = $v["id"];
            $sql = "select g.price, p.name
                    from t_goods_price g, t_price_system p
                    where g.goods_id = '%s' 
                        and g.ps_id = p.id";
            $d = $db->query($sql, $goodsId);
            if ($d) {
                $priceSystem = $d[0]["name"];
                //$price = $d[0]["price"];
            }

			//查询库存数量
            $sql="select sum(balance_count) as balance_count,sum(afloat_count) as afloat_count from t_inventory_lot where goods_id='%s' and company_id='%s'";
            $t_lot=$db->query($sql,$goodsId,$companyId);
            if($t_lot){
                $balance_count=floatval($t_lot[0]["balance_count"]);
                $afloat_count=floatval($t_lot[0]["afloat_count"]);
            }else{
                $balance_count=0;
                $afloat_count=0;
            }


			$result[] = [
					"id" => $v["id"],
                    "one_code" => $top_category["code"],
                    "one_name" => $top_category["name"],
                    "two_code" => $category["code"],
                    "two_name" => $category["name"],
                    "brand" => $brand["name"],
                    "code" => $v["code"],
                    "name" => $v["name"],
                    "spec" => $v["spec"],
                    "tax_rate" => $v["tax_rate"],
                    "unitName" => $v["unit_name"],
					"salePrice" => $price,
					"priceSystem" => $priceSystem,
					"memo" => $v["memo"],
                    "balance_count"=>$balance_count,
                    "afloat_count"=>$afloat_count
			];
		}

		return $result;
	}

	/**
	 * 商品字段，查询数据
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function queryDataWithPurchasePrice($params) {
		$db = $this->db;
		
		$queryKey = $params["queryKey"];
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$key = "%{$queryKey}%";
		
		$sql = "select g.id, g.code, g.brand_id,g.name,g.category_id,g.tax_rate, g.spec, u.name as unit_name, g.purchase_price, g.memo
				from t_goods g, t_goods_unit u
				where (g.unit_id = u.id)
				and (g.code like '%s' or g.name like '%s' or g.py like '%s'
					or g.spec like '%s' or g.spec_py like '%s') ";
		
		$queryParams = [];
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;

		$sql .= " order by g.code";
		$data = $db->query($sql, $queryParams);
		$result = [];
		foreach ( $data as $v ) {
		    $category=M("t_goods_category")->where("id ='".$v["category_id"]."'")->find();
		    $brand=M("t_goods_brand")->where("id='".$v["brand_id"]."'")->find();
			if($category["parent_id"]){
                $top_category=M("t_goods_category")->where("id ='".$category["parent_id"]."'")->find();
            }else{
                $top_category["name"]="无";
                $top_category["code"]="无";
            }
		    $result[] = [
                    "one_code" => $top_category["code"],
                    "one_name" => $top_category["name"],
                    "two_code" => $category["code"],
                    "two_name" => $category["name"],
					"id" => $v["id"],
					"brand" => $brand["name"],
					"code" => $v["code"],
					"tax_rate" => $v["tax_rate"],
					"name" => $v["name"],
					"spec" => $v["spec"],
					"unitName" => $v["unit_name"],
					"purchasePrice" => $v["purchase_price"] == 0 ? null : $v["purchase_price"],
					"memo" => $v["memo"]
			];
		}
		
		return $result;
	}

	/**
	 * 获得某个商品的详情
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function getGoodsInfo($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$categoryId = $params["categoryId"];
		
		$sql = "select category_id, code, name, spec, unit_id, sale_price, purchase_price,
					bar_code, memo, brand_id ,method 
				from t_goods
				where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			$result = array();
			$categoryId = $data[0]["category_id"];
			$result["categoryId"] = $categoryId;
			
			$result["code"] = $data[0]["code"];
			$result["name"] = $data[0]["name"];
			$result["spec"] = $data[0]["spec"];
			$result["unitId"] = $data[0]["unit_id"];
			$result["salePrice"] = $data[0]["sale_price"];
			$result["method"] = $data[0]["method"];
			$brandId = $data[0]["brand_id"];
			$result["brandId"] = $brandId;
			
			$v = $data[0]["purchase_price"];
			if ($v == 0) {
				$result["purchasePrice"] = null;
			} else {
				$result["purchasePrice"] = $v;
			}
			
			$result["barCode"] = $data[0]["bar_code"];
			$result["memo"] = $data[0]["memo"];
			
			$sql = "select full_name from t_goods_category where id = '%s' ";
			$data = $db->query($sql, $categoryId);
			if ($data) {
				$result["categoryName"] = $data[0]["full_name"];
			}
			
			if ($brandId) {
				$sql = "select full_name from t_goods_brand where id = '%s' ";
				$data = $db->query($sql, $brandId);
				$result["brandFullName"] = $data[0]["full_name"];
			}
			
			return $result;
		} else {
			$result = array();
			
			$sql = "select full_name from t_goods_category where id = '%s' ";
			$data = $db->query($sql, $categoryId);
			if ($data) {
				$result["categoryId"] = $categoryId;
				$result["categoryName"] = $data[0]["full_name"];
			}
			return $result;
		}
	}

	/**
	 * 通过条形码查询商品信息, 销售出库单使用
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function queryGoodsInfoByBarcode($params) {
		$db = $this->db;
		
		$barcode = $params["barcode"];
		
		$result = array();
		
		$sql = "select g.id, g.code, g.name, g.spec, g.sale_price, u.name as unit_name
				from t_goods g, t_goods_unit u
				where g.bar_code = '%s' and g.unit_id = u.id ";
		$data = $db->query($sql, $barcode);
		
		if (! $data) {
			$result["success"] = false;
			$result["msg"] = "条码为[{$barcode}]的商品不存在";
		} else {
			$result["success"] = true;
			$result["id"] = $data[0]["id"];
			$result["code"] = $data[0]["code"];
			$result["name"] = $data[0]["name"];
			$result["spec"] = $data[0]["spec"];
			$result["salePrice"] = $data[0]["sale_price"];
			$result["unitName"] = $data[0]["unit_name"];
		}
		
		return $result;
	}

	/**
	 * 通过条形码查询商品信息, 采购入库单使用
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function queryGoodsInfoByBarcodeForPW($params) {
		$db = $this->db;
		
		$barcode = $params["barcode"];
		
		$result = array();
		
		$sql = "select g.id, g.code, g.name, g.spec, g.purchase_price, u.name as unit_name
				from t_goods g, t_goods_unit u
				where g.bar_code = '%s' and g.unit_id = u.id ";
		$data = $db->query($sql, $barcode);
		
		if (! $data) {
			$result["success"] = false;
			$result["msg"] = "条码为[{$barcode}]的商品不存在";
		} else {
			$result["success"] = true;
			$result["id"] = $data[0]["id"];
			$result["code"] = $data[0]["code"];
			$result["name"] = $data[0]["name"];
			$result["spec"] = $data[0]["spec"];
			$result["purchasePrice"] = $data[0]["purchase_price"];
			$result["unitName"] = $data[0]["unit_name"];
		}
		
		return $result;
	}

	/**
	 * 查询商品种类总数
	 *
	 * @param array $params        	
	 * @return int
	 */
	public function getTotalGoodsCount($params) {
		$db = $this->db;
		
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$barCode = $params["barCode"];
		
		$loginUserId = $params["loginUserId"];
		
		$sql = "select count(*) as cnt
					from t_goods c
					where (1 = 1) ";
		$queryParam = array();
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::GOODS, "c", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		if ($code) {
			$sql .= " and (c.code like '%s') ";
			$queryParam[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (c.name like '%s' or c.py like '%s') ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}
		if ($spec) {
			$sql .= " and (c.spec like '%s')";
			$queryParam[] = "%{$spec}%";
		}
		if ($barCode) {
			$sql .= " and (c.bar_code = '%s') ";
			$queryParam[] = $barCode;
		}
		$data = $db->query($sql, $queryParam);
		
		return array(
				"cnt" => $data[0]["cnt"]
		);
	}

	/**
	 * 子商品字段，查询数据
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function queryDataForSubGoods($params) {
		$db = $this->db;
		
		$parentGoodsId = $params["parentGoodsId"];
		if (! $parentGoodsId) {
			return $this->emptyResult();
		}
		
		$queryKey = $params["queryKey"];
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$key = "%{$queryKey}%";
		
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name
				from t_goods g, t_goods_unit u
				where (g.unit_id = u.id)
				and (g.code like '%s' or g.name like '%s' or g.py like '%s'
					or g.spec like '%s' or g.spec_py like '%s') 
				and (g.id <> '%s')";
		$queryParams = [];
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $parentGoodsId;
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::GOODS, "g", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by g.code
				limit 20";
		$data = $db->query($sql, $queryParams);
		$result = [];
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"spec" => $v["spec"],
					"unitName" => $v["unit_name"]
			];
		}
		
		return $result;
	}
}