<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/5/15
 * Time: 9:36
 */
namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\UserService;
use Home\Service\BizlogService;
use Home\DAO\WarehouseDAO;



class CurrencyController extends PSIBaseController{
    private $LOG_CATEGORY = "货币管理";

        /*币种*/
        public function currency(){
            $us = new UserService();

            if ($us->hasPermission(FIdConst::COIN_CURRENCY)) {
                $this->initVar();

                $this->assign("title", "币种");

                $this->assign("pAdd", $us->hasPermission(FIdConst::COIN_CURRENCY_ADD) ? "1" : "0");
                $this->assign("pEdit", $us->hasPermission(FIdConst::COIN_CURRENCY_EDIT) ? "1" : "0");
                $this->assign("pDelete", $us->hasPermission(FIdConst::COIN_CURRENCY_DELETE) ? "1" : "0");

                $this->display();
            } else {
                $this->gotoLoginPage("/Home/Currency/currency");
            }
        }
            /* 获取全部币种*/

        public function allCurrency(){
            $date=M('t_currency')->select();
            echo json_encode($date);


        }

        /* 获取一个币种*/

        public function oneCurrency($id){
            $date=M('t_currency')->where("id={$id}")->find();
            return $date;
        }
        /*新增币种*/
        public function addCurrency(){

            if (IS_POST) {
                $us = new UserService();
                if (I("post.id")) {
                    // 编辑仓库
                    if (! $us->hasPermission(FIdConst::COIN_CURRENCY)) {
                        $this->ajaxReturn($this->noPermission("编辑币种"));
                        return;
                    }
                } else {
                    // 新增仓库
                    if (! $us->hasPermission(FIdConst::COIN_CURRENCY)) {
                        $this->ajaxReturn($this->noPermission("新增币种"));
                        return;
                    }
                }
                $id=I("post.id");
                $db=M();
                $db->startTrans();
                if($id){
                    /*修改操作*/
                    $data["currency_name"]=I("post.currency_name");
                    $data["currency_symbol"]=I("post.currency_symbol");
                    $data["currency_time"]=time();
                    $add=M("t_currency")->where('id = '.$id)->save($data);
                    $log = "编辑币种：名称=".I("post.currency_name");
                }else{
                    /*新增操作*/
                    $data["currency_name"]=I("post.currency_name");
                    $data["currency_symbol"]=I("post.currency_symbol");
                    $data["currency_time"]=time();
                    $add=M("t_currency")->add($data);
                    $log = "新增币种：币种名称=".I("post.currency_name");
                }
                $bs = new BizlogService($db);
                $bs->insertBizlog($log, $this->LOG_CATEGORY);

                $db->commit();
                $this->ajaxReturn($this->ok($add));
            }
        }
        /*图标*/
        public function icon(){
            $date=array(array('icon'=>'￥'),array('icon'=>'$'),array('icon'=>'NT$'),array('icon'=>'£'),array('icon'=>'€'),array('icon'=>'฿'),array('icon'=>'₩'));
            echo json_encode($date);
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

        /*删除币种*/
        public function deleteCurrency(){
            if (IS_POST) {
                $us = new UserService();
                if (! $us->hasPermission(FIdConst::COIN_CURRENCY)) {
                    $this->ajaxReturn($this->noPermission("删除仓库"));
                    return;
                }

                    $id = I("post.id");
                $db=M();
                $db->startTrans();
                $date=M("t_currency")->where("id =".$id)->find();
                $delete=M("t_currency")->delete($id);

                $log="删除币种 : 币种名称 = ".$date["currency_name"] ;
                $bs = new BizlogService($db);
                $bs->insertBizlog($log, $this->LOG_CATEGORY);
                $db->commit();
                $this->ajaxReturn($this->ok($delete));
            }
        }

        /*汇率主页面*/
        public function rateIndex(){
            $us = new UserService();

            if ($us->hasPermission(FIdConst::COIN_RATE)) {
                $this->initVar();

                $this->assign("title", "汇率");

                $this->assign("pAdd", $us->hasPermission(FIdConst::COIN_RATE_ADD) ? "1" : "0");
                $this->assign("pEdit", $us->hasPermission(FIdConst::COIN_RATE_EDIT) ? "1" : "0");
                $this->assign("pDelete", $us->hasPermission(FIdConst::COIN_RATE_DELETE) ? "1" : "0");

                $this->display();
            } else {

                $this->gotoLoginPage("/Home/Currency/rateIndex");
            }

        }

        /**
         * 获得所有的汇率
         */
        public function allRates()
        {
            if (IS_POST) {
                $rates = M("t_currency_rate")->select();
                $currencies = M("t_currency")->field("id,currency_name")->select();

                foreach ($rates as $k => $v) {
                    foreach ($currencies as $val) {
                        if ($v['trade_currency'] == $val["id"]) {
                            $v['trade_currency_name'] = $val['currency_name'];
                        }
                        if($v['change_currency']==$val['id']){
                            $v['change_currency_name']=$val['currency_name'];
                        }
                    }

                    $rates[$k]=$v;
                }
                //var_dump($rates);
                $this->ajaxReturn($rates);
            }
        }

        /**
         * 获得汇率
         */
        public function oneRate($id)
        {
            if (IS_POST) {
                $rates = M("t_currency_rate")->where("id={$id}")->find();
                $currencies = M("t_currency")->field("id,currency_name")->select();

                foreach ($rates as $k => $v) {
                    foreach ($currencies as $val) {
                        if ($v['trade_currency'] == $val["id"]) {
                            $v['trade_currency_name'] = $val['currency_name'];
                        }
                        if($v['change_currency']==$val['id']){
                            $v['change_currency_name']=$val['currency_name'];
                        }
                    }

                    $rates[$k]=$v;
                }
                //var_dump($rates);
               return  $rates;
            }
        }



        /**
         * 新增或编辑货币汇率
         */
        public function editRate() {
            if (IS_POST) {
                $params = array(
                    "id" => I("post.id"),
                    "trade_currency" => I("post.trade_currency"),
                    "change_currency" => I("post.change_currency"),
                    "rate"=>I("post.rate"),
                    "effective_time"=>I("post.effective_time"),
                    "create_time"=>time()
                );
                //交易币名
                $trade_currency=$this->oneCurrency($params['trade_currency']);
                $trade_currency_name=$trade_currency['currency_name'];
                //兑换币名
                $change_currency=$this->oneCurrency($params['change_currency']);
                $change_currency_name=$change_currency['currency_name'];

                $db=M();
                $db->startTrans();

                if($params['id']){  //编辑
                    $result=M("t_currency_rate")->save($params);
                    if (!$result) {
                        $db->rollback();
                        return $result;
                    }

                    $log = "编辑汇率： 交易币种 = {$trade_currency_name} 兑换币种 = {$change_currency_name} 汇率 = {$params['rate']} 有效时间 = {$params['effective_time']}";
                }else{               //新增
                    unset($params['id']);
                    $result=M("t_currency_rate")->add($params);
                    if (!$result) {
                        $db->rollback();
                        return $result;
                    }

                    $log = "新增汇率： 交易币种 = {$trade_currency_name} 兑换币种 = {$trade_currency_name} 汇率 = {$params['rate']} 有效时间 = {$params['effective_time']}";
                }

                // 记录业务日志
                $bs = new BizlogService($db);
                $bs->insertBizlog($log, $this->LOG_CATEGORY);

                $db->commit();

                $this->ajaxReturn($this->ok($result));
            }
        }

        /**
         * 删除货币汇率
         */
        public function deleteRate() {
            if (IS_POST) {
                $params = array(
                    "id" => I("post.id")
                );

                $db=M();
                $db->startTrans();
                $currency_rate=$this->oneRate($params['id']);
                $delete=M("t_currency_rate")->where($params)->delete();

                if (!$delete) {
                    return $db->rollback();
                }

                $log = "删除汇率： 交易币种 = {$currency_rate['trade_currency_name']} 兑换币种 = {$currency_rate['change_currency_name']} 汇率 = {$currency_rate['rate']} 有效时间 = {$currency_rate['effective_time']}";
                // 记录业务日志
                $bs = new BizlogService($db);
                $bs->insertBizlog($log, $this->LOG_CATEGORY);

                $db->commit();


                $this->ajaxReturn($this->ok($delete));
            }
        }

        //以currency_id为兑换币种，查相关的币种和汇率
        public function selectCurrency(){
            if(IS_POST){
                $currency_id=I("post.currency_id");
                $currency_name=I("post.currency_name");
                $allRate=M("t_currency_rate")->field("trade_currency,rate")->where("change_currency='{$currency_id}'")->order("create_time desc")->limit("0,1")->select();
                $currency_M=M("t_currency");
                $result[]=array(
                    "trade_id"=>$currency_id,
                    "trade_name"=>$currency_name,
                    "change_id"=>$currency_id,
                    "change_name"=>$currency_name,
                    "rate"=>"1.0000"
                );
                foreach ($allRate as $v){
                    $currency=$currency_M->field("currency_name")->where("id=".$v['trade_currency'])->find();
                    $result[]=[
                        "trade_id"=>$v["trade_currency"],
                        "trade_name"=>$currency["currency_name"],
                        "change_id"=>$currency_id,
                        "change_name"=>$currency_name,
                        "rate"=>$v["rate"]
                    ];
                }

                $this->ajaxReturn($result);
            }
        }
}




