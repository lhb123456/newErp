<?php

/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/7/23
 * Time: 15:42
 */
namespace Home\Service;

use Home\DAO\BankDAO;
use Home\DAO\ReceivablesDAO;

class BankService extends PSIBaseExService
{


    public function bankList($params)
    {
        if ($this->isNotOnline()) {
            return $this->emptyResult();
        }

        $params["loginUserId"] = $this->getLoginUserId();
        $params["companyId"] = $this->getCompanyId();
        $dao = new BankDAO($this->db());
        return $dao->bankList($params);

    }

    public function bankInfo($params) {
        if ($this->isNotOnline()) {
            return $this->emptyResult();
        }

        $dao = new BankDAO($this->db());
        return $dao->bankInfo($params);
    }

    public function bankDetail($params) {
        if ($this->isNotOnline()) {
            return $this->emptyResult();
        }

        $params["companyId"] = $this->getCompanyId();
        $params["loginUserId"] = $this->getLoginUserId();
        $params["loginUserName"] = $this->getLoginUserName();
        $params["dataOrg"] = $this->getLoginUserDataOrg();

        $dao = new BankDAO($this->db());
        return $dao->bankDetail($params);
        }


 public function bankAdd($json) {
     if ($this->isNotOnline()) {
         return $this->notOnlineError();
     }

     $bill = json_decode(html_entity_decode($json), true);
     if ($bill == null) {
         return $this->bad("传入的参数错误，不是正确的JSON格式");
     }
     if(!$bill["companyId"]){
         $bill["companyId"] = $this->getCompanyId();
     }
     if(!$bill["dataOrg"]){
         $bill["dataOrg"] = $this->getLoginUserDataOrg();
     }
     if(!$bill["bizUserId"]){
         $bill["loginUserId"] = $this->getLoginUserId();
     }
     $db = $this->db();
     $db->startTrans();
    if($bill["id"]){
        $dao = new BankDAO($db);
        $rc = $dao->bankEdit($bill);
        if ($rc) {
            $db->rollback();
            return $rc;
        }
        // 记录业务日志
        $log = "为{$bill["caName"]}编辑银行信息，银行名称：{$bill["bankName"]},银行账户：{$bill["bankAccount"]}";

    }else{
        $dao = new BankDAO($db);
        $rc = $dao->bankAdd($bill);
        if ($rc) {
            $db->rollback();
            return $rc;
        }
        // 记录业务日志
        $log = "为{$bill["caName"]}新建银行信息，银行名称：{$bill["bankName"]},银行账户：{$bill["bankAccount"]}";

    }


    $bs = new BizlogService($db);
     $bs->insertBizlog($log, $this->LOG_CATEGORY);

     $db->commit();

     return $this->ok();
    }


    public function deleteBank($id) {

         if ($this->isNotOnline()) {
             return $this->notOnlineError();
         }

         $db = $this->db();
         $db->startTrans();

         $dao = new BankDAO($db);
         $rc = $dao->deleteBank($id);

         if ($rc) {
             $db->rollback();
             return $rc;
         }
         // 记录业务日志
         $log = "删除银行信息";
         $bs = new BizlogService($db);
         $bs->insertBizlog($log, $this->LOG_CATEGORY);

         $db->commit();

         return $this->ok();
        }



 public function isDefault($id) {

         if ($this->isNotOnline()) {
             return $this->notOnlineError();
         }

         $db = $this->db();
         $db->startTrans();

         $dao = new BankDAO($db);
         $rc = $dao->isDefault($id);

         if ($rc) {
             $db->rollback();
             return $rc;
         }
         $db->commit();

         return $this->ok();
        }





}