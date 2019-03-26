<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\COCompanyService;
use Home\Service\CustomerService;
use Home\Service\ImportService;
use Home\Service\UserService;

/**
 * 往来单位资料Controller
 *
 * @author 李静波
 *        
 */
class COCompanyController extends PSIBaseController {

	/**
	 * 往来单位档案 - 主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::CO_COMPANY)) {
			$this->initVar();
			
			$this->assign("title", "往来单位档案");
			
			/*$this->assign("pAddCategory",
					$us->hasPermission(FIdConst::CUSTOMER_CATEGORY_ADD) ? 1 : 0);
			$this->assign("pEditCategory", 
					$us->hasPermission(FIdConst::CUSTOMER_CATEGORY_EDIT) ? 1 : 0);
			$this->assign("pDeleteCategory", 
					$us->hasPermission(FIdConst::CUSTOMER_CATEGORY_DELETE) ? 1 : 0);
			$this->assign("pAddCustomer", $us->hasPermission(FIdConst::CUSTOMER_ADD) ? 1 : 0);
			$this->assign("pEditCustomer", $us->hasPermission(FIdConst::CUSTOMER_EDIT) ? 1 : 0);
			$this->assign("pDeleteCustomer", $us->hasPermission(FIdConst::CUSTOMER_DELETE) ? 1 : 0);
			$this->assign("pImportCustomer", $us->hasPermission(FIdConst::CUSTOMER_IMPORT) ? 1 : 0);
			*/
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/COCompany/index");
		}
	}

	/**
	 * 获得往来单位分类列表
	 */
	public function categoryList() {
		if (IS_POST) {
			$params = array(
					"companyType" => I("post.companyType"),
                    "categoryName" => I("post.categoryName"),
					"name" => I("post.name")

			);
            $cs = new COCompanyService();
			$this->ajaxReturn($cs->categoryList($params));
		}
	}

	//往来单位分类信息
    public function getCategoryInfo(){

        if(IS_POST){

            $params=[
                'id'=>I("post.id")
            ];

            $cs=new COCompanyService();
            $this->ajaxReturn($cs->getCategoryInfo($params));

        }

    }


    /**
	 * 新增或编辑往来单位分类
	 */
	public function editCategory() {
		if (IS_POST) {
			$us = new UserService();
			if (I("post.id")) {
				// 编辑往来单位分类
				if (! $us->hasPermission(FIdConst::CO_COMPANY_EDIT)) {
					$this->ajaxReturn($this->noPermission("编辑往来单位分类"));
					return;
				}
			} else {
				// 新增往来单位分类
				if (! $us->hasPermission(FIdConst::CO_COMPANY_ADD)) {
					$this->ajaxReturn($this->noPermission("新增往来单位分类"));
					return;
				}
			}
			
			$params = array(
					"id" => I("post.id"),
					"code" => strtoupper(I("post.code")),
					"name" => I("post.name"),
					"limitCount" => I("post.limitCount")
			);
			$cs = new COCompanyService();
			$this->ajaxReturn($cs->editCategory($params));
		}
	}



	/**
	 * 删除往来单位分类
	 */
	public function deleteCategory() {
		if (IS_POST) {
			$us = new UserService();
			if (! $us->hasPermission(FIdConst::COMPANY_CATEGORY_DEL)) {
				$this->ajaxReturn($this->noPermission("删除往来单位分类"));
				return;
			}
			
			$params = array(
					"id" => I("post.id")
			);
			$cs = new COCompanyService();
			$this->ajaxReturn($cs->deleteCategory($params));
		}
	}

	/**
	 * 新增或编辑往来单位资料
	 */
	public function editCustomer() {
		if (IS_POST) {
			$us = new UserService();
			if (I("post.id")) {
				// 编辑往来单位
				if (! $us->hasPermission(FIdConst::CUSTOMER_EDIT)) {
					$this->ajaxReturn($this->noPermission("编辑往来单位"));
					return;
				}
			} else {
				// 新增往来单位
				if (! $us->hasPermission(FIdConst::CUSTOMER_ADD)) {
					$this->ajaxReturn($this->noPermission("新增往来单位"));
					return;
				}
			}
			
			$params = [
					"id" => I("post.id"),
					"code" => strtoupper(I("post.code")),
					"name" => I("post.name"),
					"address" => I("post.address"),
					"addressReceipt" => I("post.addressReceipt"),
					"contact01" => I("post.contact01"),
					"mobile01" => I("post.mobile01"),
					"tel01" => I("post.tel01"),
					"qq01" => I("post.qq01"),
					"contact02" => I("post.contact02"),
					"mobile02" => I("post.mobile02"),
					"tel02" => I("post.tel02"),
					"qq02" => I("post.qq02"),
					"bankName" => I("post.bankName"),
					"bankAccount" => I("post.bankAccount"),
					"tax" => I("post.tax"),
					"fax" => I("post.fax"),
					"note" => I("post.note"),
					"categoryId" => I("post.categoryId"),
					"initReceivables" => I("post.initReceivables"),
					"initReceivablesDT" => I("post.initReceivablesDT"),
					"warehouseId" => I("post.warehouseId"),
					"recordStatus" => I("post.recordStatus")
			];
			$cs = new CustomerService();
			$this->ajaxReturn($cs->editCustomer($params));
		}
	}

	/**
	 * 获得往来单位列表
	 */
	public function customerList() {
		if (IS_POST) {
			$params = array(
					"categoryId" => I("post.categoryId"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"address" => I("post.address"),
					"contact" => I("post.contact"),
					"mobile" => I("post.mobile"),
					"tel" => I("post.tel"),
					"qq" => I("post.qq"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			$cs = new CustomerService();
			$this->ajaxReturn($cs->customerList($params));
		}
	}

	/**
	 * 删除往来单位
	 */
	public function deleteCustomer() {
		if (IS_POST) {
			$us = new UserService();
			if (! $us->hasPermission(FIdConst::CUSTOMER_DELETE)) {
				$this->ajaxReturn($this->noPermission("删除往来单位"));
				return;
			}
			
			$params = array(
					"id" => I("post.id")
			);
			$cs = new CustomerService();
			$this->ajaxReturn($cs->deleteCustomer($params));
		}
	}

	/**
	 * 往来单位自定义字段，查询往来单位
	 */
	public function queryData() {
		if (IS_POST) {
			$params = array(
					"queryKey" => I("post.queryKey")
			);
			$cs = new CustomerService();
			$this->ajaxReturn($cs->queryData($params));
		}
	}

	/**
	 * 获得某个往来单位的信息
	 */
	public function customerInfo() {
		if (IS_POST) {
			$id = I("post.id");
			$cs = new CustomerService();
			$this->ajaxReturn($cs->customerInfo($id));
		}
	}

	/**
	 * 通过Excel导入往来单位资料
	 */
	public function import() {
		if (IS_POST) {
			$us = new UserService();
			if (! $us->hasPermission(FIdConst::CUSTOMER_IMPORT)) {
				$this->ajaxReturn($this->noPermission("导入往来单位"));
				return;
			}
			
			$upload = new \Think\Upload();
			
			// 允许上传的文件后缀
			$upload->exts = array(
					'xls',
					'xlsx'
			);
			
			// 保存路径
			$upload->savePath = '/Customer/';
			
			// 先上传文件
			$fileInfo = $upload->uploadOne($_FILES['data_file']);
			if (! $fileInfo) {
				$this->ajaxReturn(
						array(
								"msg" => $upload->getError(),
								"success" => false
						));
			} else {
				$uploadFileFullPath = './Uploads' . $fileInfo['savepath'] . $fileInfo['savename']; // 获取上传到服务器文件路径
				$uploadFileExt = $fileInfo['ext']; // 上传文件扩展名
				
				$params = array(
						"datafile" => $uploadFileFullPath,
						"ext" => $uploadFileExt
				);
				$cs = new ImportService();
				$this->ajaxReturn($cs->importCustomerFromExcelFile($params));
			}
		}
	}

	/**
	 * 获得所有的价格体系中的价格
	 */
	public function priceSystemList() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$cs = new CustomerService();
			
			$this->ajaxReturn($cs->priceSystemList($params));
		}
	}
}