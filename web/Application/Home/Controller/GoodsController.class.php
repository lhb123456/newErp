<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\GoodsService;
use Home\Service\ImportService;
use Home\Service\UserService;

/**
 * 商品Controller
 *
 * @author
 *        
 */
class GoodsController extends PSIBaseController {

	/**
	 * 商品主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::GOODS)) {
			$this->initVar();
			
			$this->assign("title", "商品");
			
			$this->assign("pAddCategory", $us->hasPermission(FIdConst::GOODS_CATEGORY_ADD) ? 1 : 0);
			$this->assign("pEditCategory", 
					$us->hasPermission(FIdConst::GOODS_CATEGORY_EDIT) ? 1 : 0);
			$this->assign("pDeleteCategory", 
					$us->hasPermission(FIdConst::GOODS_CATEGORY_DELETE) ? 1 : 0);
			$this->assign("pAddGoods", $us->hasPermission(FIdConst::GOODS_ADD) ? 1 : 0);
			$this->assign("pEditGoods", $us->hasPermission(FIdConst::GOODS_EDIT) ? 1 : 0);
			$this->assign("pDeleteGoods", $us->hasPermission(FIdConst::GOODS_DELETE) ? 1 : 0);
			$this->assign("pImportGoods", $us->hasPermission(FIdConst::GOODS_IMPORT) ? 1 : 0);
			$this->assign("pGoodsSI", $us->hasPermission(FIdConst::GOODS_SI) ? 1 : 0);
			
			$this->assign("pAddBOM", $us->hasPermission(FIdConst::GOODS_BOM_ADD) ? 1 : 0);
			$this->assign("pEditBOM", $us->hasPermission(FIdConst::GOODS_BOM_EDIT) ? 1 : 0);
			$this->assign("pDeleteBOM", $us->hasPermission(FIdConst::GOODS_BOM_DELETE) ? 1 : 0);
			
			$this->assign("pPriceSystem", 
					$us->hasPermission(FIdConst::PRICE_SYSTEM_SETTING_GOODS) ? 1 : 0);
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Goods/index");
		}
	}

	/**
	 * 商品计量单位主页面
	 */
	public function unitIndex() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::GOODS_UNIT)) {
			$this->initVar();
			
			$this->assign("title", "商品计量单位");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Goods/unitIndex");
		}
	}

	/**
	 * 获得所有的商品计量单位列表
	 */
	public function allUnits() {
		if (IS_POST) {
			$gs = new GoodsService();
			$this->ajaxReturn($gs->allUnits());
		}
	}

	/**
	 * 新增或编辑商品单位
	 */
	public function editUnit() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id"),
					"name" => I("post.name")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editUnit($params));
		}
	}

	/**
	 * 删除商品计量单位
	 */
	public function deleteUnit() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->deleteUnit($params));
		}
	}

	/**
	 * 获得商品分类
	 */
	public function allCategories() {
		if (IS_POST) {
			$gs = new GoodsService();
			$params = array(
					"code" => I("post.code"),
					"name" => I("post.name"),
					"spec" => I("post.spec"),
					"barCode" => I("post.barCode")
			);
			$this->ajaxReturn($gs->allCategories($params));
		}
	}

	/**
	 * 新增或编辑商品分类
	 */
	public function editCategory() {
		if (IS_POST) {
			$us = new UserService();
			if (I("post.id")) {
				// 编辑商品分类
				if (! $us->hasPermission(FIdConst::GOODS_CATEGORY_EDIT)) {
					$this->ajaxReturn($this->noPermission("编辑商品分类"));
					return;
				}
			} else {
				// 新增商品分类
				if (! $us->hasPermission(FIdConst::GOODS_CATEGORY_ADD)) {
					$this->ajaxReturn($this->noPermission("新增商品分类"));
					return;
				}
			}
			
			$params = array(
					"id" => I("post.id"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"parentId" => I("post.parentId")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editCategory($params));
		}
	}

	public function allCategoryList(){
        if (IS_POST) {
            $gs = new GoodsService();

            $this->ajaxReturn($gs->allCategoryList());
        }
    }



	/**
	 * 获得某个分类的信息
	 */
	public function getCategoryInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->getCategoryInfo($params));
		}
	}

	/**
	 * 删除商品分类
	 */
	public function deleteCategory() {
		if (IS_POST) {
			$us = new UserService();
			if (! $us->hasPermission(FIdConst::GOODS_CATEGORY_DELETE)) {
				$this->ajaxReturn($this->noPermission("删除商品分类"));
				return;
			}
			
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->deleteCategory($params));
		}
	}

	/**
	 * 获得商品列表
	 */
	public function goodsList() {
		if (IS_POST) {
			$params = array(
					"categoryId" => I("post.categoryId"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"spec" => I("post.spec"),
					"barCode" => I("post.barCode"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->goodsList($params));
		}
	}

	/**
	 * 新增或编辑商品
	 */
	public function editGoods() {
		if (IS_POST) {
			$us = new UserService();
			if (I("post.id")) {
				// 编辑商品
				if (! $us->hasPermission(FIdConst::GOODS_EDIT)) {
					$this->ajaxReturn($this->noPermission("编辑商品"));
					return;
				}
			} else {
				// 新增商品
				if (! $us->hasPermission(FIdConst::GOODS_ADD)) {
					$this->ajaxReturn($this->noPermission("新增商品"));
					return;
				}
			}

			$params = array(
					"id" => I("post.id"),
					"categoryId" => I("post.categoryId"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"spec" => I("post.spec"),
					"unitId" => I("post.unitId"),
					"salePrice" => I("post.salePrice"),
					"purchasePrice" => I("post.purchasePrice"),
                    "oldSalePrice" => I("post.oldSalePrice"),
                    "oldPurchasePrice" => I("post.oldPurchasePrice"),
					"barCode" => I("post.barCode"),
					"brandId" => I("post.brandId"),
					"taxRate" => I("post.taxRate"),
					"memo" => I("post.memo")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editGoods($params));
		}
	}

	/**
	 * 删除商品
	 */
	public function deleteGoods() {
		if (IS_POST) {
			$us = new UserService();
			if (! $us->hasPermission(FIdConst::GOODS_DELETE)) {
				$this->ajaxReturn($this->noPermission("删除商品"));
				return;
			}
			
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->deleteGoods($params));
		}
	}

	/**
	 * 商品自定义字段，查询数据
	 */
	public function queryData() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryData($queryKey));
		}
	}

	/**
	 * 商品自定义字段，查询数据
	 */
	public function queryDataWithSalePrice() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$customerId = I("post.customerId");
			$companyId = I("post.companyId");
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryDataWithSalePrice($queryKey, $customerId,$companyId));
		}
	}

	/**
	 * 商品自定义字段，查询数据
	 */
	public function queryDataWithPurchasePrice() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryDataWithPurchasePrice($queryKey));
		}
	}

	/**
	 * 查询某个商品的信息
	 */
	public function goodsInfo() {
		if (IS_POST) {
			$id = I("post.id");
			$categoryId = I("post.categoryId");
			$gs = new GoodsService();
			$data = $gs->getGoodsInfo($id, $categoryId);
			$data["units"] = $gs->allUnits();
			$this->ajaxReturn($data);
		}
	}

	/**
	 * 获得商品的安全库存信息
	 */
	public function goodsSafetyInventoryList() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->goodsSafetyInventoryList($params));
		}
	}

	/**
	 * 设置安全库存时候，查询信息
	 */
	public function siInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->siInfo($params));
		}
	}

	/**
	 * 设置安全库存
	 */
	public function editSafetyInventory() {
		if (IS_POST) {
			$us = new UserService();
			if (! $us->hasPermission(FIdConst::GOODS_SI)) {
				$this->ajaxReturn($this->noPermission("设置商品安全库存"));
				return;
			}
			
			$params = array(
					"jsonStr" => I("post.jsonStr")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editSafetyInventory($params));
		}
	}

	/**
	 * 根据条形码，查询商品信息, 销售出库单使用
	 */
	public function queryGoodsInfoByBarcode() {
		if (IS_POST) {
			$params = array(
					"barcode" => I("post.barcode")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryGoodsInfoByBarcode($params));
		}
	}

	/**
	 * 根据条形码，查询商品信息, 采购入库单使用
	 */
	public function queryGoodsInfoByBarcodeForPW() {
		if (IS_POST) {
			$params = array(
					"barcode" => I("post.barcode")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryGoodsInfoByBarcodeForPW($params));
		}
	}

	/**
	 * 通过Excel导入商品
	 */
	public function import() {
		if (IS_POST) {
			$us = new UserService();
			if (! $us->hasPermission(FIdConst::GOODS_IMPORT)) {
				$this->ajaxReturn($this->noPermission("导入商品"));
				return;
			}
			
			$upload = new \Think\Upload();
			
			// 允许上传的文件后缀
			$upload->exts = array(
					'xls',
					'xlsx'
			);
			
			// 保存路径
			$upload->savePath = '/Goods/';
			
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
				$ims = new ImportService();
				$this->ajaxReturn($ims->importGoodsFromExcelFile($params));
			}
		}
	}

	/**
	 * 获得所有的商品种类数
	 */
	public function getTotalGoodsCount() {
		if (IS_POST) {
			$params = array(
					"code" => I("post.code"),
					"name" => I("post.name"),
					"spec" => I("post.spec"),
					"barCode" => I("post.barCode")
			);
			
			$gs = new GoodsService();
			$this->ajaxReturn($gs->getTotalGoodsCount($params));
		}
	}

	/**
	 * 商品品牌主页面
	 */
	public function brandIndex() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::GOODS_BRAND)) {
			$this->initVar();
			
			$this->assign("title", "商品品牌");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Goods/brandIndex");
		}
	}

	/**
	 * 获得所有的品牌
	 */
	public function allBrands() {
		if (IS_POST) {
			$gs = new GoodsService();
			$this->ajaxReturn($gs->allBrands());
		}
	}

    /**
     * 获得大品牌
     */
    public function getBrands() {
        if (IS_POST) {
            $gs = new GoodsService();
            $this->ajaxReturn($gs->getBrands());
        }
    }

	/**
	 * 新增或编辑商品品牌
	 */
	public function editBrand() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id"),
					"name" => I("post.name"),
					"parentId" => I("post.parentId")
			);
			
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editBrand($params));
		}
	}

	/**
	 * 获得某个品牌的上级品牌全称
	 */
	public function brandParentName() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$gs = new GoodsService();
			$this->ajaxReturn($gs->brandParentName($params));
		}
	}

	/**
	 * 删除商品品牌
	 */
	public function deleteBrand() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$gs = new GoodsService();
			$this->ajaxReturn($gs->deleteBrand($params));
		}
	}

	/**
	 * 某个商品的商品构成
	 */
	public function goodsBOMList() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$gs = new GoodsService();
			$this->ajaxReturn($gs->goodsBOMList($params));
		}
	}

	/**
	 * 新增或编辑商品税控编码
	 */
	public function editGoodsCode() {
		if (IS_POST) {
			$us = new UserService();
			if (I("post.id")) {
				// 编辑
				if (! $us->hasPermission(FIdConst::GOODS_BOM_EDIT)) {
					$this->ajaxReturn($this->noPermission("编辑税控编码"));
					return;
				}
			} else {
				if (! $us->hasPermission(FIdConst::GOODS_BOM_ADD)) {
					$this->ajaxReturn($this->noPermission("新建税控编码"));
					return;
				}
			}
			
			$params = array(
					"goodsId" => I("post.id"),
					"goodsCodeId" => I("post.goodsCodeId"),
					"historyCode" => I("post.history_code"),
					"code" => I("post.code"),
					"defaultCode" => I("post.default")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editGoodsCode($params));
		}
	}

	/**
	 * 子商品字段，查询数据
	 */
	public function queryDataForSubGoods() {
		if (IS_POST) {
			$params = array(
					"queryKey" => I("post.queryKey"),
					"parentGoodsId" => I("post.parentGoodsId")
			);
			
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryDataForSubGoods($params));
		}
	}

	/**
	 * 查询子商品的详细信息
	 */
	public function getGoodsCodeInfo() {
		if (IS_POST) {
			$params = array(
					"GoodsCodeId" => I("post.GoodsCodeId")
			);

			$gs = new GoodsService();
			$this->ajaxReturn($gs->getGoodsCodeInfo($params));
		}
	}

	/**
	 * 删除商品构成中的子商品
	 */
	public function deleteGoodsCode() {
		if (IS_POST) {
			$us = new UserService();
			if (! $us->hasPermission(FIdConst::GOODS_BOM_DELETE)) {
				$this->ajaxReturn($this->noPermission("删除子商品"));
				return;
			}
			
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->deleteGoodsCode($params));
		}
	}
    /**
	 * 删除商品构成中的子商品
	 */
	public function isDefault() {
		if (IS_POST) {
			$us = new UserService();
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->isDefault($params));
		}
	}

	/**
	 * 价格体系 - 主页面
	 */
	public function psIndex() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::PRICE_SYSTEM)) {
			$this->initVar();
			
			$this->assign("title", "价格体系");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Goods/psIndex");
		}
	}

	/**
	 * 价格体系-价格列表
	 */
	public function priceSystemList() {
		if (IS_POST) {
			$gs = new GoodsService();
			$this->ajaxReturn($gs->priceSystemList());
		}
	}

	/**
	 * 新增或编辑价格体系中的价格
	 */
	public function editPriceSystem() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id"),
					"name" => I("post.name"),
					"factor" => I("post.factor")
			);
			$gs = new GoodsService();
			
			$this->ajaxReturn($gs->editPriceSystem($params));
		}
	}

	/**
	 * 删除价格体系中的价格
	 */
	public function deletePriceSystem() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			
			$this->ajaxReturn($gs->deletePriceSystem($params));
		}
	}

	/**
	 * 查询某个商品的所有价格体系里面的价格列表
	 */
	public function goodsPriceSystemList() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$gs = new GoodsService();
			$this->ajaxReturn($gs->goodsPriceSystemList($params));
		}
	}

	/**
	 * 查询某个商品的价格体系中所有价格的值
	 */
	public function goodsPriceSystemInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$gs = new GoodsService();
			$this->ajaxReturn($gs->goodsPriceSystemInfo($params));
		}
	}

	/**
	 * 设置商品价格体系中的价格
	 */
	public function editGoodsPriceSystem() {
		if (IS_POST) {
			$params = array(
					"jsonStr" => I("post.jsonStr")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editGoodsPriceSystem($params));
		}
	}
	public function cateReadOnly(){
        if (IS_POST) {
              $code = I("post.code");
              $code=M("t_goods_category")->where("code = '".$code."'")->find();
            $this->ajaxReturn($code);
        }
    }




}