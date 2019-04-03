<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\FIdService;
use Home\Service\BizlogService;
use Home\Service\UserService;
use Home\Common\FIdConst;
use Home\Service\MainMenuService;

/**
 * 主菜单Controller
 *
 * @author 李静波
 *        
 */
class MainMenuController extends Controller {

	/**
	 * 页面跳转
	 */
	public function navigateTo() {
		$this->assign("uri", __ROOT__ . "/");
		
		$fid = I("get.fid");
		
		// $t == 1的时候，是从常用功能链接点击而来的
		$t = I("get.t");
		
		$fidService = new FIdService();
		$fidService->insertRecentFid($fid);
		$fidName = $fidService->getFIdName($fid);
		if ($fidName) {
			// 记录业务日志
			
			$bizLogService = new BizlogService();
			
			if ($t == "1") {
				$bizLogService->insertBizlog("通过常用功能进入模块：" . $fidName, "常用功能");
			} else {
				$bizLogService->insertBizlog("通过主菜单进入模块：" . $fidName);
			}
		}
		if (! $fid) {
			redirect(__ROOT__ . "/Home");
		}
		
		switch ($fid) {
			case FIdConst::ABOUT :
				// 修改我的密码
				redirect(__ROOT__ . "/Home/About/index");
				break;
			case FIdConst::RELOGIN :
				// 重新登录
				$us = new UserService();
				$us->clearLoginUserInSession();
				redirect(__ROOT__ . "/Home");
				break;
			case FIdConst::CHANGE_MY_PASSWORD :
				// 修改我的密码
				redirect(__ROOT__ . "/Home/User/changeMyPassword");
				break;
			case FIdConst::USR_MANAGEMENT :
				// 用户管理
				redirect(__ROOT__ . "/Home/User");
				break;
			case FIdConst::PERMISSION_MANAGEMENT :
				// 权限管理
				redirect(__ROOT__ . "/Home/Permission");
				break;
			case FIdConst::BIZ_LOG :
				// 业务日志
				redirect(__ROOT__ . "/Home/Bizlog");
				break;
			case FIdConst::WAREHOUSE :
				// 基础数据 - 仓库
				redirect(__ROOT__ . "/Home/Warehouse");
				break;
			case FIdConst::SUPPLIER :
				// 基础数据 - 供应商档案
				redirect(__ROOT__ . "/Home/Supplier");
				break;
			case FIdConst::GOODS :
				// 基础数据 - 商品
				redirect(__ROOT__ . "/Home/Goods");
				break;
			case FIdConst::GOODS_UNIT :
				// 基础数据 - 商品计量单位
				redirect(__ROOT__ . "/Home/Goods/unitIndex");
				break;
			case FIdConst::CUSTOMER :
				// 客户关系 - 客户资料
				redirect(__ROOT__ . "/Home/Customer");
				break;
			case FIdConst::INVENTORY_INIT :
				// 库存建账
				redirect(__ROOT__ . "/Home/Inventory/initIndex");
				break;
			case FIdConst::PURCHASE_WAREHOUSE :
				// 采购入库
				redirect(__ROOT__ . "/Home/Purchase/pwbillIndex");
				break;
			case FIdConst::INVENTORY_QUERY :
				// 库存账查询
				redirect(__ROOT__ . "/Home/Inventory/inventoryQuery");
				break;
			case FIdConst::PAYABLES :
				// 应付账款管理
				redirect(__ROOT__ . "/Home/Funds/payIndex");
				break;
			case FIdConst::RECEIVING :
				// 应收账款管理
				redirect(__ROOT__ . "/Home/Funds/rvIndex");
				break;
			case FIdConst::WAREHOUSING_SALE :
				// 销售出库
				redirect(__ROOT__ . "/Home/Sale/wsIndex");
				break;
			case FIdConst::SALE_REJECTION :
				// 销售退货入库
				redirect(__ROOT__ . "/Home/Sale/srIndex");
				break;
			case FIdConst::BIZ_CONFIG :
				// 业务设置
				redirect(__ROOT__ . "/Home/BizConfig");
				break;
			case FIdConst::INVENTORY_TRANSFER :
				// 库间调拨
				redirect(__ROOT__ . "/Home/InvTransfer");
				break;
			case FIdConst::INVENTORY_CHECK :
				// 库存盘点
				redirect(__ROOT__ . "/Home/InvCheck");
				break;
			case FIdConst::PURCHASE_REJECTION :
				// 采购退货出库
				redirect(__ROOT__ . "/Home/PurchaseRej");
				break;
			case FIdConst::REPORT_SALE_DAY_BY_GOODS :
				// 销售日报表(按商品汇总)
				redirect(__ROOT__ . "/Home/Report/saleDayByGoods");
				break;
			case FIdConst::REPORT_SALE_DAY_BY_CUSTOMER :
				// 销售日报表(按客户汇总)
				redirect(__ROOT__ . "/Home/Report/saleDayByCustomer");
				break;
			case FIdConst::REPORT_SALE_DAY_BY_WAREHOUSE :
				// 销售日报表(按仓库汇总)
				redirect(__ROOT__ . "/Home/Report/saleDayByWarehouse");
				break;
			case FIdConst::REPORT_SALE_DAY_BY_BIZUSER :
				// 销售日报表(按业务员汇总)
				redirect(__ROOT__ . "/Home/Report/saleDayByBizuser");
				break;
			case FIdConst::REPORT_SALE_MONTH_BY_GOODS :
				// 销售月报表(按商品汇总)
				redirect(__ROOT__ . "/Home/Report/saleMonthByGoods");
				break;
			case FIdConst::REPORT_SALE_MONTH_BY_CUSTOMER :
				// 销售月报表(按客户汇总)
				redirect(__ROOT__ . "/Home/Report/saleMonthByCustomer");
				break;
			case FIdConst::REPORT_SALE_MONTH_BY_WAREHOUSE :
				// 销售月报表(按仓库汇总)
				redirect(__ROOT__ . "/Home/Report/saleMonthByWarehouse");
				break;
			case FIdConst::REPORT_SALE_MONTH_BY_BIZUSER :
				// 销售月报表(按业务员汇总)
				redirect(__ROOT__ . "/Home/Report/saleMonthByBizuser");
				break;
			case FIdConst::REPORT_SAFETY_INVENTORY :
				// 安全库存明细表
				redirect(__ROOT__ . "/Home/Report/safetyInventory");
				break;
			case FIdConst::REPORT_RECEIVABLES_AGE :
				// 应收账款账龄分析表
				redirect(__ROOT__ . "/Home/Report/receivablesAge");
				break;
			case FIdConst::REPORT_PAYABLES_AGE :
				// 应付账款账龄分析表
				redirect(__ROOT__ . "/Home/Report/payablesAge");
				break;
			case FIdConst::REPORT_INVENTORY_UPPER :
				// 库存超上限明细表
				redirect(__ROOT__ . "/Home/Report/inventoryUpper");
				break;
			case FIdConst::CASH_INDEX :
				// 现金收支查询
				redirect(__ROOT__ . "/Home/Funds/cashIndex");
				break;
			case FIdConst::PRE_RECEIVING :
				// 预收款管理
				redirect(__ROOT__ . "/Home/Funds/prereceivingIndex");
				break;
			case FIdConst::PRE_PAYMENT :
				// 预付款管理
				redirect(__ROOT__ . "/Home/Funds/prepaymentIndex");
				break;
			case FIdConst::PURCHASE_ORDER :
				// 采购订单
				redirect(__ROOT__ . "/Home/Purchase/pobillIndex");
				break;
			case FIdConst::SALE_ORDER :
				// 销售订单
				redirect(__ROOT__ . "/Home/Sale/soIndex");
				break;
			case FIdConst::GOODS_BRAND :
				// 基础数据 - 商品品牌
				redirect(__ROOT__ . "/Home/Goods/brandIndex");
				break;
			case FIdConst::FORM_SYSTEM :
				// 自定义表单
				redirect(__ROOT__ . "/Home/Form/index");
				break;
			case FIdConst::PRICE_SYSTEM :
				// 价格体系
				redirect(__ROOT__ . "/Home/Goods/psIndex");
				break;
			case FIdConst::GL_SUBJECT :
				// 会计科目
				redirect(__ROOT__ . "/Home/Subject/index");
				break;
			case FIdConst::GL_BANK_ACCOUNT :
				// 银行账户
				redirect(__ROOT__ . "/Home/Bank/index");
				break;
			case FIdConst::FORM_VIEW_SYSTEM_DEV :
				// 表单视图开发助手
				redirect(__ROOT__ . "/Home/FormView/devIndex");
				break;
			case FIdConst::GL_PERIOD :
				// 会计期间
				redirect(__ROOT__ . "/Home/GLPeriod/index");
				break;
			case FIdConst::SALE_CONTRACT :
				// 销售合同
				redirect(__ROOT__ . "/Home/SaleContract/index");
				break;
			case FIdConst::WSP :
				// 存货拆分
				redirect(__ROOT__ . "/Home/WSP/index");
				break;
			case FIdConst::FACTORY :
				// 工厂
				redirect(__ROOT__ . "/Home/Factory/index");
				break;
			case FIdConst::DMO :
				// 成品委托生产订单
				redirect(__ROOT__ . "/Home/DM/dmobillIndex");
				break;
			case FIdConst::DMW :
				// 成品委托生产入库
				redirect(__ROOT__ . "/Home/DM/dmwbillIndex");
				break;
            case FIdConst::CO_COMPANY :
                // 往来单位档案
                redirect(__ROOT__ . "/Home/COCompany/index");
                break;
            case FIdConst::CREDIT_ASSESS :
                // 信用额度评估
                redirect(__ROOT__ . "/Home/COCompany/creditAssess");
                break;
			default :
				redirect(__ROOT__ . "/Home");
		}
	}

	/**
	 * 返回生成主菜单的JSON数据
	 * 目前只能处理到生成三级菜单的情况
	 */
	public function mainMenuItems() {
		if (IS_POST) {
			$ms = new MainMenuService();
			
			$this->ajaxReturn($ms->mainMenuItems());
		}
	}

	/**
	 * 常用功能
	 */
	public function recentFid() {
		if (IS_POST) {
			$fidService = new FIdService();
			$data = $fidService->recentFid();
			
			$this->ajaxReturn($data);
		}
	}
}
