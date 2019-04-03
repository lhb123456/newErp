<?php

namespace Home\DAO;

use Home\Common\FIdConst;
use Home\Service\PinyinService;

/**
 * 仓库 DAO
 *
 * @author 李静波
 */
class WarehouseDAO extends PSIBaseExDAO {

	/**
	 * 获得所有的公司仓库列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function warehouseList($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$sql = "select id, code, name, inited, data_org from t_warehouse ";
		$ds = new DataOrgDAO($db);
		$queryParams = [];
		$rs = $ds->buildSQL(FIdConst::WAREHOUSE, "t_warehouse", $loginUserId);
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParams = $rs[1];
		}
		
		$sql .= " order by code";
		
		$result = [];
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"inited" => $v["inited"],
					"dataOrg" => $v["data_org"]
			];
		}
		
		return $result;
	}

    /**
     * 获得所有的基础仓库列表
     *
     * @param array $params
     * @return array
     */
    public function OrgwarehouseList($params) {
        $db = M();
        $loginUserId = $params["loginUserId"];
        $role = $params["role"];
        $name = trim($params["name"]);
        $code = trim($params["code"]);
        $address = trim($params["address"]);
        if ($this->loginUserIdNotExists($loginUserId)) {
            return $this->emptyResult();
        }
        //$ware=M("t_all_warehouse")->select();
        $queryParams = [];
        $sql="select id,code,name,address,number from t_warehouse_base where 1 = 1 ";

        if($code){
            $sql.=" AND code like '%s'";
            $queryParams[] = "%{$code}%";
        }
        if($name){
            $sql.=" AND name like '%s'";
            $queryParams[] = "%{$name}%";
        }
        if($address){
            $sql.=" AND address like '%s'";
            $queryParams[] = "%{$address}%";
        }
        $sql .=" order by name";
        $ware = $db->query($sql,$queryParams);
        $result = [];
        foreach ($ware as $v){
            $result[] = [
                'id' =>$v["id"],
                'code' =>$v["code"],
                'name' =>$v["name"],
                'address' =>$v["address"],
                'number' =>$v["number"],
            ];
        }
        return $result;
    }

    /**
	 * 新增一个仓库
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function addWarehouse(& $params) {
		$db = $this->db;
		$orgId = trim($params["orgId"]);
		$items = $params["items"];
		$text = $params["text"];
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->bad("参数dataOrg不正确");
		}
		
		if ($this->companyIdNotExists($companyId)) {
			return $this->bad("参数companyId不正确");
		}
		if($text == 'yes'){
		    $number=1;
        }else{
            $number=0;
        }
		foreach ($items as $k=>$v){
            $ps =new PinyinService();
            $py = $ps->toPY($v["name"]);
		    $ware_id=$v["id"];
            $id = $this->newId();
		    $sql="select count(id) as cnt from t_warehouse where company_id = '%s' AND warehouse_id = '%s'";
		    $data=$db->query($sql,$orgId,$ware_id);
		    //当查询没有值时添加
		    if($data[0]["cnt"] == 0){
		        //查询基础库的数据
                $sql="select code from t_warehouse_base where id = '%s'";
                $base=$db->query($sql,$ware_id);
                //查询此公司的数据域
                $sql="select data_org from t_org where id = '%s'";
                $dataOrg=$db->query($sql,$orgId);
                $wareData[]=[
                    "code"=>$base[0]["code"],
                    "name"=>$v["name"],
                ];
                $sql = "insert into t_warehouse(id, code, name, inited, py, data_org, company_id,warehouse_id)
					values ('%s', '%s', '%s', '%s', '%s', '%s', '%s','%s')";
                $rc = $db->execute($sql, $id, $base[0]["code"], $v["name"],$number, $py, $dataOrg[0]["data_org"], $orgId,$ware_id);
                if ($rc === false) {
                    return $this->sqlError(__METHOD__, __LINE__);
                }
            }
        }
        $params["data"]=$wareData;
		// 操作成功
		return null;
	}

	/**
	 * 修改仓库
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function updateWarehouse(& $params) {
		$id = $params["id"];
		$code = trim($params["code"]);
		$name = trim($params["name"]);
		$py = $params["py"];
		
		if ($this->isEmptyStringAfterTrim($code)) {
			return $this->bad("仓库编码不能为空");
		}
		
		if ($this->isEmptyStringAfterTrim($name)) {
			return $this->bad("仓库名称不能为空");
		}
		
		$db = $this->db;
		
		// 检查同编号的仓库是否存在
		$sql = "select count(*) as cnt from t_warehouse where code = '%s' and id <> '%s' ";
		$data = $db->query($sql, $code, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [$code] 的仓库已经存在");
		}
		
		$warehouse = $this->getWarehouseById($id);
		if (! $warehouse) {
			return $this->bad("要编辑的仓库不存在");
		}
		
		$sql = "update t_warehouse
				set code = '%s', name = '%s', py = '%s'
				where id = '%s' ";
		$rc = $db->execute($sql, $code, $name, $py, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 删除仓库
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function deleteWarehouse(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		// 判断仓库是否能删除
		$warehouse = $this->getWarehouseById($id);
		if (! $warehouse) {
			return $this->bad("要删除的仓库不存在");
		}
		$params["code"] = $warehouse["code"];
		$params["name"] = $warehouse["name"];
		
		$warehouseName = $warehouse["name"];
		if ($warehouse["inited"] == 1) {
			return $this->bad("仓库[{$warehouseName}]已经建账，不能删除");
		}
		
		// 判断仓库是否在采购入库单中使用
		$sql = "select count(*) as cnt from t_pw_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("仓库[$warehouseName]已经在采购入库单中使用，不能删除");
		}
		
		// 判断仓库是否在采购退货出库单中使用
		$sql = "select count(*) as cnt from t_pr_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("仓库[$warehouseName]已经在采购退货出库单中使用，不能删除");
		}
		
		// 判断仓库是否在销售出库单中使用
		$sql = "select count(*) as cnt from t_ws_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("仓库[$warehouseName]已经在销售出库单中使用，不能删除");
		}
		
		// 判断仓库是否在销售退货入库单中使用
		$sql = "select count(*) as cnt from t_sr_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("仓库[$warehouseName]已经在销售退货入库单中使用，不能删除");
		}
		
		// 判断仓库是否在调拨单中使用
		$sql = "select count(*) as cnt from t_it_bill
				where from_warehouse_id = '%s' or to_warehouse_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("仓库[$warehouseName]已经在调拨单中使用，不能删除");
		}
		
		// 判断仓库是否在盘点单中使用
		$sql = "select count(*) as cnt from t_ic_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("仓库[$warehouseName]已经在盘点单中使用，不能删除");
		}
		
		// 判断仓库是否在业务设置中使用
		$sql = "select o.name
				from t_config c, t_org o
				where c.company_id = o.id
					and c.value = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			$companyName = $data[0]["name"];
			return $this->bad("仓库[$warehouseName]已经在公司[$companyName]的业务设置中使用，不能删除");
		}
		
		$sql = "delete from t_warehouse where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 通过仓库id查询仓库
	 *
	 * @param string $id        	
	 * @return array|NULL
	 */
	public function getWarehouseById($id) {
		$db = $this->db;
		$sql = "select code, name, data_org, inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $id);
		
		if (! $data) {
			return null;
		}
		
		return array(
				"code" => $data[0]["code"],
				"name" => $data[0]["name"],
				"dataOrg" => $data[0]["data_org"],
				"inited" => $data[0]["inited"]
		);
	}

	/**
	 * 编辑仓库数据域
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function editDataOrg(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		$dataOrg = $params["dataOrg"];
		
		$sql = "select name, data_org from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要编辑数据域的仓库不存在");
		}
		
		$name = $data[0]["name"];
		$oldDataOrg = $data[0]["data_org"];
		if ($oldDataOrg == $dataOrg) {
			return $this->bad("数据域没有改动，不用保存");
		}
		
		// 检查新数据域是否存在
		$sql = "select count(*) as cnt from t_user where data_org = '%s' ";
		$data = $db->query($sql, $dataOrg);
		$cnt = $data[0]["cnt"];
		if ($cnt != 1) {
			return $this->bad("数据域[{$dataOrg}]不存在");
		}
		
		$sql = "update t_warehouse
				set data_org = '%s'
				where id = '%s' ";
		$rc = $db->execute($sql, $dataOrg, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 查询数据，用于仓库自定义字段
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
		
		$sql = "select id, code, name from t_warehouse
					where (code like '%s' or name like '%s' or py like '%s' ) ";
		$key = "%{$queryKey}%";
		$queryParams = [];
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::WAREHOUSE_BILL, "t_warehouse", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by code";
		
		return $db->query($sql, $queryParams);
	}

    /**
     * 新增一个公司仓库
     *
     * @param array $params
     * @return NULL|array
     */
    public function addOrgWarehouse(& $params) {
        $db = $this->db;
        $name=$params["name"];
        $code=$params["code"];
        $address=$params["address"];
        $number=$params["number"];

        if(!$name){
            return $this->bad("仓库名称不能为空");
        }
        if (!$code) {
            return $this->bad("仓库编码不能为空");
        }
        if (!$address) {
            return $this->bad("仓库地址不能为空");
        }
        //判断编码是否重复
        $date=M("t_warehouse_base")->where("code = '".$code."'")->find();
        if($date){
            return $this->bad("编码已存在");
        }
        $id = $this->newId();
        $data["id"] = $id;
        $data["name"]=$name;
        $data["code"]=$code;
        $data["address"]=$address;
        $data["number"]=$number;
        $ware_id=M("t_warehouse_base")->add($data);
        $params["id"]=$ware_id;
        // 操作成功
        return null;
    }
    public function updateOrgWarehouse(& $params) {
        $id = $params["id"];
        $name=$params['name'];
        $hisCode=$params['hisCode'];
        $code=$params['code'];
        $address=$params['address'];
        $number=$params["number"];
        if(!$name){
            return $this->bad("仓库名称不能为空");
        }
        if (!$code) {
            return $this->bad("仓库编码不能为空");
        }
        if (!$address) {
            return $this->bad("仓库地址不能为空");
        }
        //判断编码是否重复
        if(!($hisCode == $code)){
            $date=M("t_warehouse_base")->where("code = '".$code."'")->find();
            if($date){
                return $this->bad("编码已存在");
            }
        }
        $data["name"] = $name;
        $data["code"] = $code;
        $data["address"] = $address;
        $data["number"] = $number;
        //创建新的数据
        M("t_warehouse_base")->where("id = '".$id."'")->save($data);
        //修改公司-仓库表中的数据
        $date["warehouse_id"]=$id;
        $date["name"] = $name;
        $date["code"] = $code;
        M("t_warehouse")->where("warehouse_id = '".$id."'")->save($date);

        // 操作成功
        return null;
    }
    //删除操作
    public function deleteOrgWarehouse($params){
        $db = $this->db;

        $id = $params["id"];
        $data=M("t_warehouse_base")->where("id = '".$id."'")->find();
        if(!$data){
            return $this->bad("仓库不存在");
        }
        // 判断仓库是否能删除
        $warehouse = M("t_warehouse as w")
            ->where("warehouse_id = '".$id."'")->find();
        if ($warehouse) {
            return $this->bad("仓库[{$data["name"]}]已经被关联，不能删除");
        }

        $sql = "delete from t_warehouse_base where id = '%s' ";
        $rc = $db->execute($sql, $id);
        if ($rc === false) {
            return $this->sqlError(__METHOD__, __LINE__);
        }

        // 操作成功
        return null;
    }
    public function selectOrg($params) {
        $db = $this->db;

        $loginUserId = $params["loginUserId"];
        if ($this->loginUserIdNotExists($loginUserId)) {
            return $this->emptyResult();
        }
        $sql = "select id,name, full_name,data_org
				from t_org where org_type = 2000  ";

        $queryParams = array();
        $ds = new DataOrgDAO($db);
        $rs = $ds->buildSQL("-8999-01", "t_org", $loginUserId);
        if ($rs) {
            $sql .= " AND " . $rs[0];
            $queryParams = $rs[1];
        }

        $sql .= " order by full_name";

        $data = $db->query($sql, $queryParams);
        $result = array();
        foreach ( $data as $i => $v ) {
            //查询公司下的仓库
            $sql="select count(id) as cnt from t_warehouse where company_id = '%s'";
            $count=$db->query($sql,$v["id"]);
            $result[$i]["id"] = $v["id"];
            $result[$i]["fullName"] = $v["name"];
            $result[$i]["data_org"] = $v["data_org"];
            $result[$i]["count"] = $count[0]["cnt"];
        }
        return array_values($result);
    }
    public function wareList($params) {
        $db = $this->db;
        $orgId = trim($params["orgId"]);
        $code = trim($params["code"]);
        $name = trim($params["name"]);
        $start = trim($params["start"]);
        $limit = trim($params["limit"]);
        $sql="select w.id,w.code,w.name,b.address,b.number from t_warehouse  w,t_warehouse_base b where w.warehouse_id = b.id ";
        $queryParams = [];
        if($code){
            $sql.=" AND w.code like '%s'";
            $queryParams[] = "%{$code}%";
        }
        if($name){
            $sql.=" AND (w.name like '%s' or w.py like '%s')";
            $queryParams[] = "%{$name}%";
            $queryParams[] = "%{$name}%";
        }
        $sql .=" AND company_id = '%s' order by code limit %d , %d";
        $queryParams[] = $orgId;
        $queryParams[] = $start;
        $queryParams[] = $limit;
        $data=$db->query($sql,$queryParams);
        $result=[];
        foreach ($data as $k=>$v){
            $result[]=[
              "id"=>$v["id"],
              "code"=>$v["code"],
              "name"=>$v["name"],
              "address"=>$v["address"],
              "number"=>$v["number"],
            ];
        }
        // 操作成功
        return $result;
    }
    public function deleteOrgWare($params) {
        $db = $this->db;
        $orgId = trim($params["orgId"]);
        $wareId = trim($params["wareId"]);
        //根据条件删除公司仓库表中相对应的数据
        $sql="delete from t_warehouse where id = '%s' AND company_id = '%s'";
        $del=$db->execute($sql,$wareId,$orgId);
        if ($del === false) {
            return $this->sqlError(__METHOD__, __LINE__);
        }
        // 操作成功
        return null;
    }
}