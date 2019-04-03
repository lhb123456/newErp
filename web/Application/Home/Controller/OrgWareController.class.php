<?php
/**
 * Created by PhpStorm.
 * User: hongwang
 * Date: 2019/3/21
 * Time: 10:10
 */
namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\UserService;
use Home\Service\WarehouseService;


class OrgWareController extends PSIBaseController
{

    /**
     * 仓库信息 - 主页面
     */
    protected function bad($msg)
    {
        return array(
            "success" => false,
            "msg" => $msg
        );
    }

    public function index()
    {
        $us = new UserService();

        if ($us->hasPermission(FIdConst::ORG_WAREHOUSE)) {
            $this->initVar();

            $this->assign("title", "仓库基础数据");

            $this->assign("pAdd", $us->hasPermission(FIdConst::ORG_WAREHOUSE_ADD) ? "1" : "0");
            $this->assign("pEdit", $us->hasPermission(FIdConst::ORG_WAREHOUSE_EDIT) ? "1" : "0");
            $this->assign("pDelete", $us->hasPermission(FIdConst::ORG_WAREHOUSE_DELETE) ? "1" : "0");


            $us = new UserService();
            $company_id = $us->getCompanyId();
            $company = M("t_org")->field("full_name")->where("id='{$company_id}'")->find();
            $company_name = $company['full_name'];
            $this->assign("company_id", $company_id);
            $this->assign("company_name", $company_name);

            $this->display("Warehouse/org_warehouse");
        } else {
            $this->gotoLoginPage("/Home/OrgWare/index");
        }
    }
    /**
     * 仓库列表
     */
    public function OrgWare(){
        $us = new UserService();
        $role=$us->hasPermission(FIdConst::ORG_WAREHOUSE ) ? 1 : 0;
        if (IS_POST) {
            $params = array(
                "code" => I("post.code"),
                "name" => I("post.name"),
                "address"=>I("post.address"),
                "role"=>$role,

            );
            $ws = new WarehouseService();
            $this->ajaxReturn($ws->OrgwarehouseList($params));
        }
    }
    //自动生成仓库编码
    public function code(){
        $db=M();
        $sql="select max(code) as code from t_warehouse_base";
        $code=$db->query($sql);
        if($code){
            $result["code"]=$code[0]["code"]+1;
        }else{
            $result["code"]='01';
        }
        $this->ajaxReturn($result);
    }
    /**
     * 新增或编辑仓库信息
     */
    public function editOrgWarehouse() {
        if (IS_POST) {
            $us = new UserService();
            if (I("post.id")) {
                // 编辑仓库
                if (! $us->hasPermission(FIdConst::WAREHOUSE_EDIT)) {
                    $this->ajaxReturn($this->noPermission("编辑仓库"));
                    return;
                }
            } else {
                // 新增仓库
                if (! $us->hasPermission(FIdConst::WAREHOUSE_ADD)) {
                    $this->ajaxReturn($this->noPermission("新增仓库"));
                    return;
                }
            }

            $params = array(
                "id" => I("post.id"),
                "hisCode" => I("post.hisCode"),
                "code" => I("post.code"),
                "name" => I("post.name"),
                "number"=>I("post.number"),
                "address"=>I("post.address"),

            );
            $ws = new WarehouseService();
            $this->ajaxReturn($ws->editOrgWarehouse($params));
        }
    }
    /**
     * 删除仓库
     */
    public function deleteOrgWarehouse() {
        if (IS_POST) {
            $us = new UserService();
            if (! $us->hasPermission(FIdConst::WAREHOUSE_DELETE)) {
                $this->ajaxReturn($this->noPermission("删除仓库"));
                return;
            }

            $params = array(
                "id" => I("post.id")
            );
            $ws = new WarehouseService();
            $this->ajaxReturn($ws->deleteOrgWarehouse($params));
        }
    }

    //查询公司列表
    public function orgList(){
        if (IS_POST) {
            $us = new WarehouseService();
            $this->ajaxReturn($us->selectOrg());
        }
    }
    public function wareList(){
        if (IS_POST) {
            $params = array(
                 "code" => I("post.code"),
                "name" => I("post.name"),
                "orgId" => I("post.orgId"),
                "start" => I("post.start"),
                "limit" => I("post.limit")
            );
            $us = new WarehouseService();
            $this->ajaxReturn($us->wareList($params));
        }
    }

    public function deleteOrgWare(){
        if (IS_POST) {
            $params = array(
                 "wareId" => I("post.wareId"),
                "orgId" => I("post.orgId"),
            );
            $us = new WarehouseService();
            $this->ajaxReturn($us->deleteOrgWare($params));
        }
    }


}