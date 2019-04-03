<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/6/26
 * Time: 16:04
 */
namespace Home\Controller;

use API\Service\UploadApiService;
use Home\Common\FIdConst;
use Home\DAO\ContractDAO;
use Home\Service\BizlogService;
use Home\DAO\PSIBaseExDAO;
use Home\Service\ContractService;
use Home\Service\POBillService;
use Home\Service\PSIBaseExService;
use Home\Service\PWBillService;
use Home\Service\UserService;

class ClearingFormController extends PSIBaseController
{
    public function ClearingForm(){
        $us = new UserService();

        if ($us->hasPermission(FIdConst::CLEARING_FORM)) {
            $this->initVar();


            $this->assign("title", "结算方式");
            $us=new UserService();
            $company_id=$us->getCompanyId();
            $company=M("t_org")->field("full_name")->where("id='{$company_id}'")->find();
            $company_name=$company['full_name'];
            $this->assign("company_id",$company_id);
            $this->assign("company_name",$company_name);


            $this->display();
        } else {
            $this->gotoLoginPage("/Home/ClearingForm/ClearingForm");
        }

    }
    public function ClearingList(){
        $db=M();
        $result=[];
        $sql="select * from t_clearing_form";
        $clear=$db->query($sql);
        foreach ($clear as $k=>$v){
            $result[]=[
                "id"=>$v["id"],
                "clearing"=>$v["clearing_form"],
                "status"=>$v["status"],
                "memo"=>$v["memo"],
            ];
        }
        $this->ajaxReturn($result);
    }
    public function add(){
        if (IS_POST) {
            $us = new UserService();

            $id=I("post.id");
            $db=M();
            $db->startTrans();
            if($id){
                /*修改操作*/
                $data["clearing_form"]=I("post.Clearing");
                $data["status"]=I("post.status");
                $data["memo"]=I("post.memo");
                $data["time"]=date("Y-m-d",time());
                $add=M("t_clearing_form")->where('id = '.$id)->save($data);
                $log = "编辑结算方式：结算方式=".$data["clearing_form"];
            }else{
                /*新增操作*/
                $data["clearing_form"]=I("post.Clearing");
                $data["status"]=I("post.status");
                $data["memo"]=I("post.memo");
                $data["time"]=date("Y-m-d",time());
                $add=M("t_clearing_form")->add($data);
                $log = "新增结算方式：结算方式名称=".$data["clearing_form"];
            }
            $bs = new BizlogService($db);
            $bs->insertBizlog($log, $this->LOG_CATEGORY);

            $db->commit();
            $this->ajaxReturn($this->ok($add));
        }
    }

    public function del(){
            $us = new UserService();
            $id = I("post.id");
            $db=M();
            $db->startTrans();
            $date=M("t_clearing_form")->where("id =".$id)->find();
            $delete=M("t_clearing_form")->delete($id);

            $log="删除结算方式 : 结算方式名称 = ".$date["clearing_form"] ;
            $bs = new BizlogService($db);
            $bs->insertBizlog($log, $this->LOG_CATEGORY);
            $db->commit();
            $this->ajaxReturn($this->ok($delete));
    }

    public function ClearList(){
        $db=M();
        $result=[];
        $sql="select * from t_clearing_form where status = 0";
        $clear=$db->query($sql);
        foreach ($clear as $k=>$v){
            $result[]=[
                "id"=>$v["id"],
                "clearing"=>$v["clearing_form"],
                "memo"=>$v["memo"],
            ];
        }
        $this->ajaxReturn($result);
    }
    public function ok($id = null) {
        if ($id) {
            return array(
                "success" => true,
                "id" => $id
            );
        } else {
            return array(
                "success" => true
            );
        }
    }
}