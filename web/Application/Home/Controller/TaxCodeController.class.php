<?php
/**
 * Created by PhpStorm.
 * User: hongwang
 * Date: 2019/4/11
 * Time: 11:12
 */
namespace Home\Controller;
use Home\Common\FIdConst;
use Home\Service\UserService;


class TaxCodeController extends PSIBaseController{

    public function taxCode(){
        $us = new UserService();
        if ($us->hasPermission(FIdConst::TAX_CODE)) {
            $this->initVar();

            $this->assign("title", "基础税控编码信息");

            $this->display();
        } else {
            $this->gotoLoginPage("/Home/TaxCode/taxCode");
        }
    }


}