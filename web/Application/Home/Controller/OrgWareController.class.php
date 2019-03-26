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
}