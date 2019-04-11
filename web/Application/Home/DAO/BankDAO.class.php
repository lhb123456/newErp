<?php
/**
 * Created by PhpStorm.
 * User: hongwang
 * Date: 2018/8/24
 * Time: 16:53
 */
namespace Home\DAO;

use Home\Common\FIdConst;
use Home\Service\UserService;

class BankDAO extends PSIBaseExDAO
{

    public function bankList($params)
    {
        $db = $this->db;

        $loginUserId = $params["loginUserId"];
        $companyId = $params["companyId"];
        if ($this->loginUserIdNotExists($loginUserId)) {
            return $this->emptyResult();
        }

        $name = $params["name"];
        $code = $params["code"];
        $status = $params["status"];
        $page = $params["page"];
        $start = $params["start"];
        $limit = $params["limit"];
        $queryParams = array();

        if($status == 0){
            $sql = "select id,code,name  from t_customer 
					where status = 1 ";

            $ds = new DataOrgDAO($db);
            $rs = $ds->buildSQL(FIdConst::BANK_MESSAGE, "t_customer", $loginUserId);
            if ($rs) {
                $sql .= " and " . $rs[0];
                $queryParams = array_merge($queryParams, $rs[1]);
            }
            if ($name) {
                $sql .= " AND name like '%s' ";
                $queryParams[] = "%$name%";
            }
            if ($code) {
                $sql .= " AND code like '%s' ";
                $queryParams[] = "%$code%";
            }
            $sql .= " order by code
					limit %d , %d ";
            $queryParams[] = $start;
            $queryParams[] = $limit;
            $data = $db->query($sql, $queryParams);
            $result = array();

            foreach ($data as $i => $v) {
                        $result[]=[
                          "id"=>$v["id"],
                          "ca_code"=>$v["code"],
                          "ca_name"=>$v["name"],
                        ];

            }

            $queryParams = array();
            $sql = "select count(*) as cnt
					from t_customer  where 1 = 1 ";

            $ds = new DataOrgDAO($db);
            $rs = $ds->buildSQL(FIdConst::BANK_MESSAGE, "t_customer", $loginUserId);
            if ($rs) {
                $sql .= " and " . $rs[0];
                $queryParams = array_merge($queryParams, $rs[1]);
            }
            if ($name) {
                $sql .= " AND name like '%s' ";
                $queryParams[] = "%$name%";
            }
            if ($code) {
                $sql .= " AND code like '%s' ";
                $queryParams[] = "%$code%";
            }
            $data = $db->query($sql, $queryParams);
            $cnt = $data[0]["cnt"];
        }elseif ($status == 1000){
            $sql = "select id,code,name  from t_supplier 
					where status = 1 ";

            $ds = new DataOrgDAO($db);
            $rs = $ds->buildSQL(FIdConst::BANK_MESSAGE, "t_supplier", $loginUserId);
            if ($rs) {
                $sql .= " and " . $rs[0];
                $queryParams = array_merge($queryParams, $rs[1]);
            }
            if ($name) {
                $sql .= " AND name like '%s' ";
                $queryParams[] = "%$name%";
            }
            if ($code) {
                $sql .= " AND code like '%s' ";
                $queryParams[] = "%$code%";
            }
            $sql .= " order by code
					limit %d , %d ";
            $queryParams[] = $start;
            $queryParams[] = $limit;
            $data = $db->query($sql, $queryParams);
            $result = array();

            foreach ($data as $i => $v) {
                $result[]=[
                    "id"=>$v["id"],
                    "ca_code"=>$v["code"],
                    "ca_name"=>$v["name"],
                ];

            }

            $queryParams = array();
            $sql = "select count(*) as cnt
					from t_supplier  where 1 = 1 ";

            $ds = new DataOrgDAO($db);
            $rs = $ds->buildSQL(FIdConst::BANK_MESSAGE, "t_supplier", $loginUserId);
            if ($rs) {
                $sql .= " and " . $rs[0];
                $queryParams = array_merge($queryParams, $rs[1]);
            }
            if ($name) {
                $sql .= " AND name like '%s' ";
                $queryParams[] = "%$name%";
            }
            if ($code) {
                $sql .= " AND code like '%s' ";
                $queryParams[] = "%$code%";
            }

            $data = $db->query($sql, $queryParams);
            $cnt = $data[0]["cnt"];
        }else if ($status == 2000){

            $sql = "select id,org_code,name  from t_org ";
            //判断是否拥有特殊权限
            $us=new UserService();
            $is_supper=$us->hasPermission(FIdConst::SELECT_ORG)?1:0;

            if($is_supper==0){
                $where=" where id = '{$companyId}' ";
            }else{
                $where=" where parent_id is null ";
            }

            $sql.=$where;

            $ds = new DataOrgDAO($db);
            $rs = $ds->buildSQL(FIdConst::BANK_MESSAGE, "t_org", $loginUserId);
            if ($rs) {
                $sql .= " and " . $rs[0];
                $queryParams = array_merge($queryParams, $rs[1]);
            }
            if ($name) {
                $sql .= " AND name like '%s' ";
                $queryParams[] = "%$name%";
            }
            if ($code) {
                $sql .= " AND org_code like '%s'";
                $queryParams[] = "%$code%";
            }
            $data = $db->query($sql, $queryParams);
            $result = array();

            foreach ($data as $i => $v) {
                $result[]=[
                    "id"=>$v["id"],
                    "ca_code"=>$v["org_code"],
                    "ca_name"=>$v["name"],
                ];

            }

            $queryParams = array();
            $sql = "select count(*) as cnt
					from t_org  where id = '".$companyId."'";

            $ds = new DataOrgDAO($db);
            $rs = $ds->buildSQL(FIdConst::BANK_MESSAGE, "t_org", $loginUserId);
            if ($rs) {
                $sql .= " and " . $rs[0];
                $queryParams = array_merge($queryParams, $rs[1]);
            }
            if ($name) {
                $sql .= " AND name like '%s' ";
                $queryParams[] = "%$name%";
            }
            if ($code) {
                $sql .= " AND org_code like '%s' ";
                $queryParams[] = "%$code%";
            }
            $data = $db->query($sql, $queryParams);
            $cnt = $data[0]["cnt"];

        }
        return array(
            "dataList" => array_values($result),
            "totalCount" => $cnt
        );
    }

    public function bankInfo($params){
        $id=$params["caId"];
        $data=M("t_bank")->where("ca_id ='".$id."' AND status = 0")->select();
        $result=[];
        foreach ($data as $k=>$v){
            $result[]=[
              "id"=>$v["id"],
              "bank_name"=>$v["bank_deposit"],
              "bank_account"=>$v["bank_account"],
              "status"=>$v["is_default"],
              "memo"=>$v["memo"],
            ];
        }

        return array(
            "dataList" => $result,
        );

    }

    public function bankDetail($params){
        $id=$params["id"];
        if($id){
            $bank=M("t_bank")->where("id = ".$id)->find();
            $result=[];
            $result=[
              "id"=>$bank["id"],
              "bank_name"=>$bank["bank_deposit"],
              "account"=>$bank["bank_account"],
              "is_default"=>$bank["is_default"],
              "memo"=>$bank["memo"],
            ];
        }
        return $result;
    }

public function bankAdd($bill){
    $db = $this->db;
    $id=$bill["caId"];
    $bankName=$bill["bankName"];
    $bankAccount=$bill["bankAccount"];
    $default=$bill["default"];
    $date=date("Y-m-d",time());
    $memo=$bill["memo"];
    if($default == 0){
        $bankDetail=M("t_bank")->where("ca_id = '".$id."'")->select();
        if($bankDetail){
            $sql="update t_bank set is_default = 1 where ca_id = '".$id."'";
            $update=$db->execute($sql);
            if ($update === false) {
                return $this->sqlError(__METHOD__, __LINE__);
            }
        }
    }

   $sql="insert into t_bank (ca_id,bank_deposit,bank_account,is_default,status,memo,date_created)values (
        '%s','%s','%s',$default,0,'%s','%s')";
    $insert=$db->execute($sql,$id,$bankName,$bankAccount,$memo,$date);
    if ($insert === false) {
        return $this->sqlError(__METHOD__, __LINE__);
    }
    // 操作成功
    return null;

    }

   public function bankEdit($bill){

    $db = $this->db;
    $id=$bill["id"];
    $caId=$bill["caId"];
    $bankName=$bill["bankName"];
    $bankAccount=$bill["bankAccount"];
    $default=$bill["default"];
    $date=date("Y-m-d",time());
    $memo=$bill["memo"];

    $data["bank_deposit"]=$bankName;
    $data["bank_account"]=$bankAccount;
    $data["is_default"]=$default;
    $data["memo"]=$memo;
    $sql="update t_bank set bank_deposit = '%s',bank_account = '%s',is_default = %d ,memo = '%s' where id =".$id;
    $update=$db->execute($sql,$bankName,$bankAccount,$default,$memo);
    if ($update === false) {
       return $this->sqlError(__METHOD__, __LINE__);
    }
    if($default == 0){
        $bankDetail=M("t_bank")->where("ca_id = '".$id."'")->select();
        if($bankDetail){
            $sql="update t_bank set is_default = 1 where ca_id = '".$caId."'";
            $update=$db->execute($sql);
            if ($update === false) {
                return $this->sqlError(__METHOD__, __LINE__);
            }
        }
    }
    // 操作成功
    return null;
    }

    public function deleteBank($id){
        $db = $this->db;
        $bank=M("t_bank")->where("id = ".$id)->find();
        if($bank){
            $sql="update t_bank set status = 1 where id=".$id;
            $update=$db->execute($sql);
            if ($update === false) {
                return $this->sqlError(__METHOD__, __LINE__);
            }
        }
        return null;
    }

    public function isDefault($id){
        $db = $this->db;
        $bank=M("t_bank")->where("id = ".$id)->find();
        if($bank){
            $sql="update t_bank set is_default = 1 where ca_id='".$bank["ca_id"]."' AND status = 0";
            $update=$db->execute($sql);
            if ($update === false) {
                return $this->sqlError(__METHOD__, __LINE__);
            }

            $sql="update t_bank set is_default = 0 where id=".$id;
            $update=$db->execute($sql);
            if ($update === false) {
                return $this->sqlError(__METHOD__, __LINE__);
            }

        }
        return null;
    }




}