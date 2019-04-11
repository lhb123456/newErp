<?php
/**
 * Created by PhpStorm.
 * User: hongwang
 * Date: 2018/8/24
 * Time: 15:42
 */
namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\BankService;
use Home\Service\BizConfigService;
use Home\Service\UserService;





class BankController extends PSIBaseController {
    public function index(){
        $us = new UserService();

        if ($us->hasPermission(FIdConst::BANK_MESSAGE)) {
            $this->initVar();

            $this->assign("title", "往来单位银行设定");


            $this->assign("pAdd",
                $us->hasPermission(FIdConst::BANK_ADD) ? "1" : "0");


            $this->assign("pDelete",
                $us->hasPermission(FIdConst::BANK_DELETE) ? "1" : "0");


            $this->assign("pDefault",
                $us->hasPermission(FIdConst::BANK_DEFAULT) ? "1" : "0");
            $this->display();
        } else {
            $this->gotoLoginPage("/Home/Bank/index");
        }
    }
    /**
     * 往来单位列表
     */
    public function bankList() {
        if (IS_POST) {
            $params = array(
                "status" => I("post.status"),
                "name" => I("post.name"),
                "code" => I("post.code"),
                "page" => I("post.page"),
                "start" => I("post.start"),
                "limit" => I("post.limit")
            );
            $rs = new BankService();
            $this->ajaxReturn($rs->bankList($params));
        }
    }

    public function bankDetail(){
        if (IS_POST) {
            $params = array(
                "id" => I("post.id")
            );

            $ps = new BankService();
            $this->ajaxReturn($ps->bankDetail($params));
        }
    }


    /**
     * 银行信息列表
     */
    public function bankInfo() {
        if (IS_POST) {
            $params = array(
                "caId" => I("post.caId"),
                "page" => I("post.page"),
                "start" => I("post.start"),
                "limit" => I("post.limit")
            );
            $rs = new BankService();
            $this->ajaxReturn($rs->bankInfo($params));
        }
    }


 /**
     * 添加银行信息
     */
    public function bankAdd() {
        if (IS_POST) {
            $json = I("post.jsonStr");
            $rs = new BankService();
            $this->ajaxReturn($rs->bankAdd($json));
        }
    }

    public function deleteBank(){
        if (IS_POST) {
            $id = I("post.id");

            $rs = new BankService();
            $this->ajaxReturn($rs->deleteBank($id));
        }
    }

    public function isDefault(){
        if (IS_POST) {
            $id = I("post.id");

            $rs = new BankService();
            $this->ajaxReturn($rs->isDefault($id));
        }
    }



}