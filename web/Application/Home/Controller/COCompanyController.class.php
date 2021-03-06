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
	public function editCOCompany() {
		if (IS_POST) {
			$us = new UserService();
			if (I("post.id")) {
				// 编辑往来单位
				if (! $us->hasPermission(FIdConst::CO_COMPANY_EDIT)) {
					$this->ajaxReturn($this->noPermission("编辑往来单位"));
					return;
				}
			} else {
				// 新增往来单位
				if (! $us->hasPermission(FIdConst::CO_COMPANY_ADD)) {
					$this->ajaxReturn($this->noPermission("新增往来单位"));
					return;
				}
			}
            $params=json_decode(html_entity_decode($_POST["jsonStr"]), true);


			$cs = new COCompanyService();
			$this->ajaxReturn($cs->editCOCompany($params));
		}
	}

	/**
	 * 获得往来单位列表
	 */
	public function cocompanyList() {
		if (IS_POST) {
			$params = array(
					"categoryId" => I("post.categoryId"),
                    "companyType"=>I("post.companyType"),
					"name" => I("post.name"),
					"categoryName" => I("post.categoryName"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			$cs = new COCompanyService();
			$this->ajaxReturn($cs->cocompanyList($params));
		}
	}



	//获得新code
    public function getCompanyNewCode(){
	    if(IS_POST){

	        $cs=new COCompanyService();
	        $this->ajaxReturn($cs->getCompanyNewCode());
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
	public function cocompanyInfo() {
		if (IS_POST) {
			$id = I("post.id");
			$cs = new COCompanyService();
			$this->ajaxReturn($cs->cocompanyInfo($id));
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

    /**
     * 往来单位档案 - 主页面
     */
    public function creditAssess() {
        $us = new UserService();

        if ($us->hasPermission(FIdConst::CREDIT_ASSESS)) {
            $this->initVar();

            $this->assign("title", "往来单位信用额度评估");

            $this->assign("add",
                $us->hasPermission(FIdConst::CREDIT_ASSESS_ADD) ? 1 : 0);

            $this->assign("edit",
                $us->hasPermission(FIdConst::CREDIT_ASSESS_EDIT) ? 1 : 0);

            $this->assign("del",
                $us->hasPermission(FIdConst::CREDIT_ASSESS_DEL) ? 1 : 0);

            $this->assign("commit",
                $us->hasPermission(FIdConst::CREDIT_ASSESS_COMMIT) ? 1 : 0);

            $this->assign("creditEdit",
                $us->hasPermission(FIdConst::CREDIT_EDIT) ? 1 : 0);

            $this->assign("creditEditBase",
                $us->hasPermission(FIdConst::CREDIT_EDIT_BASE) ? 1 : 0);

            $this->assign("creditEditAnalysis",
                $us->hasPermission(FIdConst::CREDIT_EDIT_ANALYSIS) ? 1 : 0);

            $this->assign("creditEditRisk",
                $us->hasPermission(FIdConst::CREDIT_EDIT_RISK) ? 1 : 0);

            $this->display();
        } else {
            $this->gotoLoginPage("/Home/COCompany/creditAssess");
        }
    }

    //新增或编辑评估信息
    public function editCreditAssess(){
        if(IS_POST){
            //var_dump(json_decode(html_entity_decode($_POST["jsonStr"]), true));exit;

            $params=json_decode(html_entity_decode($_POST["jsonStr"]), true);


            $cs = new COCompanyService();
            $this->ajaxReturn($cs->editCreditAssess($params));
        }
    }

    //获取评估信息
    public function getAssessInfo(){
        if(IS_POST){
            $id=I("post.id");

            $cs = new COCompanyService();
            $this->ajaxReturn($cs->getAssessInfo($id));
        }
    }

    //往来单位评估列表
    public function creditAssessList(){
        if(IS_POST){
            $us=new UserService();
            $basePermission=$us->hasPermission(FIdConst::CREDIT_EDIT_BASE) ? 1: 0;
            $analysisPermission=$us->hasPermission(FIdConst::CREDIT_EDIT_ANALYSIS) ? 1: 0;
            $riskPermission=$us->hasPermission(FIdConst::CREDIT_EDIT_RISK) ? 1: 0;

            $params=[
                "companyName" => I("post.companyName"),
                "tableStatus" => I("post.tableStatus"),
                "basePermission" => $basePermission,
                "analysisPermission" => $analysisPermission,
                "riskPermission" => $riskPermission,
                "start" => I("post.start"),
                "limit" => I("post.limit")
            ];

            $cs = new COCompanyService();
            $this->ajaxReturn($cs->creditAssessList($params));
        }
    }

    //删除信用评估
    public function deleteAssess(){
        if(IS_POST){
            $params=array(
                "id"=>I("post.id"),
                "companyName"=>I("post.companyName")
            );

            $cs = new COCompanyService();
            $this->ajaxReturn($cs->deleteAssess($params));
        }
    }

    //审核信用评估
    public function commitAssess(){
        if(IS_POST){
            $params=[
                "id"=>I("post.id"),
                "companyName"=>I("post.companyName")
            ];

            $cs = new COCompanyService();
            $this->ajaxReturn($cs->commitAssess($params));
        }
    }

    //等级评分主页面
    public function rankAssess(){
        $us = new UserService();

        if ($us->hasPermission(FIdConst::RANK_ASSESS)) {
            $this->initVar();

            $this->assign("title", "往来单位等级评分");

            $this->assign("add",
                $us->hasPermission(FIdConst::CREDIT_ASSESS_ADD) ? 1 : 0);

            $this->assign("edit",
                $us->hasPermission(FIdConst::CREDIT_ASSESS_EDIT) ? 1 : 0);

            $this->assign("del",
                $us->hasPermission(FIdConst::CREDIT_ASSESS_DEL) ? 1 : 0);

            $this->assign("commit",
                $us->hasPermission(FIdConst::CREDIT_ASSESS_COMMIT) ? 1 : 0);

            $this->assign("creditEdit",
                $us->hasPermission(FIdConst::CREDIT_EDIT) ? 1 : 0);

            $this->assign("creditEditBase",
                $us->hasPermission(FIdConst::CREDIT_EDIT_BASE) ? 1 : 0);

            $this->assign("creditEditAnalysis",
                $us->hasPermission(FIdConst::CREDIT_EDIT_ANALYSIS) ? 1 : 0);

            $this->assign("creditEditRisk",
                $us->hasPermission(FIdConst::CREDIT_EDIT_RISK) ? 1 : 0);

            $this->display();
        } else {
            $this->gotoLoginPage("/Home/COCompany/rankAssess");
        }
    }

    public function selectAssessCompany(){
        if(IS_POST){
            $params=[
                "company" => I("post.company"),
                "start" => I("post.start"),
                "limit" => I("post.limit")
            ];

            $cs = new COCompanyService();
            $this->ajaxReturn($cs->selectAssessCompany($params));
        }
    }
}
