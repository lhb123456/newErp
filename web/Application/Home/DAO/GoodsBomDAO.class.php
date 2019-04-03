<?php

namespace Home\DAO;

/**
 * 商品构成DAO
 *
 * @author 
 */
class GoodsBomDAO extends PSIBaseExDAO {

	/**
	 * 获得某个商品的商品构成
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function goodsBOMList($params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->emptyResult();
		}
		
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		$id = $params["id"];
		
		$result = [];
		
		$sql = "select * 
				from t_goods_tax_code where goods_id = '%s' order by date_created";
		$data = $db->query($sql, $id);
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"taxCode" => $v["tax_code"],
					"dateCreated" => $v["date_created"],
					"defaultCode" => $v["default_code"]?$v["default_code"]:0,
			];
		}
		
		return $result;
	}

	/**
	 * 检查子商品是否形成了循环引用
	 *
	 * @param string $id
	 *        	商品id
	 * @param string $subGoodsId
	 *        	子商品id
	 * @return array|NULL
	 */
	private function checkSubGoods($id, $subGoodsId) {
		if ($id == $subGoodsId) {
			return $this->bad("子商品不能是自身");
		}
		
		$db = $this->db;
		// 检查子商品是否形成了循环引用
		// 目前只检查一级
		// TODO 用递归算法检查
		
		$sql = "select id, sub_goods_id
				from t_goods_bom
				where goods_id = '%s' ";
		$data = $db->query($sql, $subGoodsId);
		foreach ( $data as $v ) {
			$sgi = $v["sub_goods_id"];
			if ($id == $sgi) {
				return $this->bad("子商品形成了循环引用");
			}
		}
		
		return null;
	}

	/**
	 * 新增商品税控编码
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function addGoodsCode(& $params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		// id: 商品id
		$goodsId = $params["goodsId"];
		$historyCode = $params["historyCode"];
		$code = $params["code"];
		$defaultCode = $params["defaultCode"];


		$goodsDAO = new GoodsDAO($db);
		$goods = $goodsDAO->getGoodsById($goodsId);
		if (! $goods) {
			return $this->bad("商品不存在");
		}
		if($historyCode != $code){
            $sql = "select count(*) as cnt 
				from t_goods_tax_code
				where goods_id = '%s' and tax_code = '%s' ";
            $data = $db->query($sql, $goodsId, $code);
            $cnt = $data[0]["cnt"];
            if ($cnt > 0) {
                return $this->bad("商品税编码已经存在");
            }
            //判断新增未默认时，修改其他的默认
            if($defaultCode == 1){
                $sql="update t_goods_tax_code set default_code = 0 where goods_id  = '%s'";
                $update=$db->execute($sql,$goodsId);
                if ($update === false) {
                    return $this->sqlError(__METHOD__, __LINE__);
                }
            }
            $sql = "insert into t_goods_tax_code (id, goods_id, tax_code, date_created, default_code)
				values ('%s', '%s', '%s', now(), '%s')";
            $ins = $db->execute($sql, $this->newId(), $goodsId, $code, $defaultCode);
            if ($ins === false) {
                return $this->sqlError(__METHOD__, __LINE__);
            }
        }

		$params["goodsCode"] = $goods["code"];
		$params["goodsName"] = $goods["name"];
		$params["goodsSpec"] = $goods["spec"];

		
		return null;
	}

	/**
	 * 编辑商品构成
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function updateGoodsCode(& $params) {
		$db = $this->db;
		$companyId = $params["companyId"];
		$goodsCodeId = $params["goodsCodeId"];
		$goodsId = $params["goodsId"];
		$historyCode = $params["historyCode"];
		$code = $params["code"];
		$defaultCode = $params["defaultCode"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		$goodsDAO = new GoodsDAO($db);
		$goods = $goodsDAO->getGoodsById($goodsId);
		if (! $goods) {
			return $this->bad("商品不存在");
		}

        if($historyCode != $code){
            $sql = "select count(*) as cnt 
				from t_goods_tax_code
				where goods_id = '%s' and tax_code = '%s' ";
            $data = $db->query($sql, $goodsId, $code);
            $cnt = $data[0]["cnt"];
            if ($cnt > 0) {
                return $this->bad("商品税编码已经存在");
            }
            //判断新增未默认时，修改其他的默认
            if($defaultCode == 1){
                $sql="update t_goods_tax_code set default_code = 0 where goods_id  = '%s'";
                $update=$db->execute($sql,$goodsId);
                if ($update === false) {
                    return $this->sqlError(__METHOD__, __LINE__);
                }
            }
            $sql="update t_goods_tax_code set tax_code ='%s',date_created = now(),default_code = '%s'
            where id ='%s'";
            $ins = $db->execute($sql,$code, $defaultCode,$goodsCodeId);
            if ($ins === false) {
                return $this->sqlError(__METHOD__, __LINE__);
            }
        }
		$params["goodsCode"] = $goods["code"];
		$params["goodsName"] = $goods["name"];
		$params["goodsSpec"] = $goods["spec"];

		// 操作成功
		return null;
	}

	/**
	 * 查询子商品的信息
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function getGoodsCodeInfo($params) {
		$GoodsCodeId = $params["GoodsCodeId"];
		
		$db = $this->db;
		
		$sql = "select *
				from t_goods_tax_code
				where id = '%s'";
		$data = $db->query($sql, $GoodsCodeId);
		$result=[
            "id"=>$data[0]["id"],
            "tax_code"=>$data[0]["tax_code"],
            "default_code"=>$data[0]["default_code"],
        ];
		return $result;
	}

	/**
	 * 删除商品构成中的子商品
	 *
	 * @param array $params        	
	 * @return null|array
	 */
	public function deleteGoodsCode(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$sql = "select id,tax_code,goods_id  
				from t_goods_tax_code
				where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要删除的税编码不存在");
		}
        $params["code"] = $data[0]["tax_code"];
        $goodsId = $data[0]["goods_id"];
		$goodsDAO = new GoodsDAO($db);
		$goods = $goodsDAO->getGoodsById($goodsId);
		if (! $goods) {
			return $this->badParam("goodsId");
		}

		$sql = "delete from t_goods_tax_code where id = '%s' ";
		
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["goodsCode"] = $goods["code"];
		$params["goodsName"] = $goods["name"];
		$params["goodsSpec"] = $goods["spec"];

		// 操作成功
		return null;
	}
	public function isDefault(& $params) {
		$db = $this->db;

		$id = $params["id"];

		$sql = "select id,tax_code,goods_id  
				from t_goods_tax_code
				where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要设置的税编码不存在");
		}
        $params["code"] = $data[0]["tax_code"];
        $goodsId = $data[0]["goods_id"];
		$goodsDAO = new GoodsDAO($db);
		$goods = $goodsDAO->getGoodsById($goodsId);
		if (! $goods) {
			return $this->badParam("goodsId");
		}
        $sql="update t_goods_tax_code set default_code = 0 where goods_id = '%s'";
        $update=$db->execute($sql,$goodsId);
        if ($update === false) {
            return $this->sqlError(__METHOD__, __LINE__);
        }
        $sql="update t_goods_tax_code set default_code = 1 where id = '%s'";
        $update=$db->execute($sql,$id);
        if ($update === false) {
            return $this->sqlError(__METHOD__, __LINE__);
        }
		$params["goodsCode"] = $goods["code"];
		$params["goodsName"] = $goods["name"];
		$params["goodsSpec"] = $goods["spec"];

		// 操作成功
		return null;
	}
}