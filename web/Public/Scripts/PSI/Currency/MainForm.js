/**
 * 币种 - 主界面
 */
Ext.define("PSI.Currency.MainForm", {
	extend : "PSI.AFX.BaseOneGridMainForm",

	config : {
		pAdd : null,
		pEdit : null,
		pDelete : null,
		pEditDataOrg : null,
		pInitInv : null
	},

	/**
	 * 重载父类方法
	 */
	afxGetToolbarCmp : function() {
		var me = this;

		var result = [{
					text : "新增币种",
					disabled : me.getPAdd() == "0",
					handler : me.onAddWarehouse,
					scope : me
				}, {
					text : "编辑币种",
					disabled : me.getPEdit() == "0",
					handler : me.onEditWarehouse,
					scope : me
				}, {
					text : "删除币种",
					disabled : me.getPDelete() == "0",
					handler : me.onDeleteWarehouse,
					scope : me
				}];
		result.push("-", {
					text : "关闭",
					handler : function() {
						me.closeWindow();
					}
				});

		return result;
	},

	/**
	 * 重载父类方法
	 */
	afxGetRefreshGridURL : function() {
		return "Home/Currency/allCurrency";
	},

	/**
	 * 重载父类方法
	 */
	afxGetMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSI_Currency_MainForm_PSICurrency";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "currency_name", "currency_symbol", "inited", "dataOrg"]
				});

		me.__mainGrid = Ext.create("Ext.grid.Panel", {
					cls : "PSI",
					border : 0,
					viewConfig : {
						enableTextSelection : true
					},
					columnLines : true,
					columns : [{
								xtype : "rownumberer",
								width : 40
							}, {
								header : "币种名称",
								dataIndex : "currency_name",
								menuDisabled : true,
								sortable : false,
								width : 300
							}, {
								header : "币种符号",
								dataIndex : "currency_symbol",
								menuDisabled : true,
								sortable : false,
								width : 300
							}],
					store : Ext.create("Ext.data.Store", {
								model : modelName,
								autoLoad : false,
								data : []
							}),
					listeners : {
						itemdblclick : {
							fn : me.onEditWarehouse,
							scope : me
						}
					}
				});

		return me.__mainGrid;
	},

	/**
	 * 新增币种
	 */
	onAddWarehouse : function() {
		var me = this;

		var form = Ext.create("PSI.Currency.EditForm", {
					parentForm : me
				});

		form.show();
	},

	/**
	 * 编辑币种
	 */
	onEditWarehouse : function() {
		var me = this;

		if (me.getPEdit() == "0") {
			return;
		}

		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("请选择要编辑的币种");
			return;
		}

		var warehouse = item[0];

		var form = Ext.create("PSI.Currency.EditForm", {
					parentForm : me,
					entity : warehouse
				});

		form.show();
	},

	/**
	 * 删除币种
	 */
	onDeleteWarehouse : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("请选择要删除的币种");
			return;
		}
		var currency = item[0];
		var info = "请确认是否删除币种 <span style='color:red'>" + currency.get("currency_name")
				+ "</span> ?";

		var preIndex = me.getPreIndexInMainGrid(currency.get("id"));
		var funcConfirm = function() {
			var el = Ext.getBody();
			el.mask(PSI.Const.LOADING);
			var r = {
				url : me.URL("Home/Currency/deleteCurrency"),
				params : {
					id : currency.get("id")
				},
				method : "POST",
				callback : function(options, success, response) {
					el.unmask();
					if (success) {
						var data = me.decodeJSON(response.responseText);
						if (data.success) {
							me.tip("成功完成删除操作");
							me.freshGrid(preIndex);
						} else {
							me.showInfo(data.msg);
						}
					} else {
						me.showInfo("网络错误");
					}
				}
			};

			me.ajax(r);
		};

		me.confirm(info, funcConfirm);
	}
});