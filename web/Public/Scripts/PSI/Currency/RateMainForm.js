/**
 * 商品品牌 - 主界面
 * 
 * @author 
 */
Ext.define("PSI.Currency.RateMainForm", {
			extend : "PSI.AFX.BaseOneGridMainForm",
    config : {
        pAdd : null,
        pEdit : null,
        pDelete : null
    },
			/**
			 * 重载父类方法
			 */
			afxGetToolbarCmp : function() {
				var me = this;
				return [{
							text : "新增货币汇率",
                    		disabled : me.getPAdd() == "0",
							handler : me.onAddRate,
							scope : me
						}, {
							text : "编辑货币汇率",
                    		disabled : me.getPEdit() == "0",
							handler : me.onEditRate,
							scope : me
						}, {
							text : "删除货币汇率",
                    		disabled : me.getPDelete() == "0",
							handler : me.onDeleteRate,
							scope : me
						}, "-", {
							text : "刷新",
							handler : me.onRefreshGrid,
							scope : me
						}, "-", {
							text : "关闭",
							handler : function() {
								me.closeWindow();
							}
						}];
			},

    
				/**
				 * 重载父类方法
				 */
				afxGetRefreshGridURL : function() {
					return "Home/Currency/allRates";
				},

				/**
				 * 重载父类方法
				 */
				afxGetMainGrid : function() {
					var me = this;
					if (me.__mainGrid) {
						return me.__mainGrid;
					}

					var modelName = "PSICurrencyRate";
					Ext.define(modelName, {
						extend : "Ext.data.Model",
                        fields : ["id","trade_currency_name","trade_currency","change_currency_name", "change_currency",
                            "rate","effective_time"]
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
                            header : "序号",
							width : 40
						}, {
							header : "交易币种",
							dataIndex : "trade_currency_name",
							menuDisabled : true,
							sortable : false,
							width : 300
						}, {
							header : "兑换币种",
							dataIndex : "change_currency_name",
							menuDisabled : true,
							sortable : false,
							width : 90,
						}, {
							header : "汇率",
							dataIndex : "rate",
							menuDisabled : true,
							sortable : false,
							width : 150
						},{
                            header : "有效时间",
                            dataIndex : "effective_time",
                            menuDisabled : true,
                            sortable : false,
                            width : 90,
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
			 /** 重载父类方法
			 *!/
			afxRefreshGrid : function(id) {
				var me = this;
				var store = me.getMainGrid().getStore();
				store.load();
			},*/

			/**
			 * 新增汇率
			 */
			onAddRate : function() {
				var me = this;
				var form = Ext.create("PSI.Currency.RateEditForm", {
							parentForm : me
						});
				form.show();
			},

			/**
			 * 编辑汇率
			 */
			onEditRate : function() {
				var me = this;
				var item = me.getMainGrid().getSelectionModel().getSelection();
				if (item == null || item.length != 1) {
					PSI.MsgBox.showInfo("请选择要编辑的商品品牌");
					return;
				}

				var brand = item[0];

				var form = Ext.create("PSI.Currency.RateEditForm", {
							parentForm : me,
							entity : brand
						});

				form.show();
			},

			/**
			 * 删除汇率
			 */
			onDeleteRate : function() {
				var me = this;
				var item = me.getMainGrid().getSelectionModel().getSelection();
				if (item == null || item.length != 1) {
					PSI.MsgBox.showInfo("请选择要删除的货币汇率");
					return;
				}

				var brand = item[0];
				var info = "请确认是否删除货币汇率<span style='color:red'>"
						"</span>";
				var confimFunc = function() {
					var el = Ext.getBody();
					el.mask("正在删除中...");
					var r = {
						url : me.URL("Home/Currency/deleteRate"),
						method : "POST",
						params : {
							id : brand.get("id")
						},
						callback : function(options, success, response) {
							el.unmask();

							if (success) {
								var data = Ext.JSON
										.decode(response.responseText);
								if (data.success) {
									PSI.MsgBox.tip("成功完成删除操作")
									me.refreshGrid();
								} else {
									PSI.MsgBox.showInfo(data.msg);
								}
							} else {
								PSI.MsgBox.showInfo("网络错误", function() {
											window.location.reload();
										});
							}
						}
					};
					Ext.Ajax.request(r);
				};
				PSI.MsgBox.confirm(info, confimFunc);
			},

			onRefreshGrid : function() {
				var me = this;
				me.refreshGrid();
			}
		});