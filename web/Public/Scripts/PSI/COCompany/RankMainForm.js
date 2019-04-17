/**
 * 等级评分 - 主界面
 * 
 * @author 李静波
 */
Ext.define("PSI.COCompany.RankMainForm", {
	extend : "PSI.AFX.BaseMainExForm",

	config : {
		permission : null
	},

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;

		Ext.apply(me, {
			tbar : me.getToolbarCmp(),
			items : [{
						id : "panelQueryCmp",
						region : "north",
						height : 50,
						layout : "fit",
						border : 0,
						header : false,
						collapsible : true,
						collapseMode : "mini",
						layout : {
							type : "table",
							columns : 4
						},
						items : me.getQueryCmp()
					}, {
						region : "center",
						layout : "border",
						border : 0,
						items : [{
									region : "north",
									height : "40%",
									split : true,
									layout : "fit",
									border : 0,
									items : [me.getMainGrid()]
								}, {
									region : "center",
									layout : "fit",
									border : 0,
									xtype : "tabpanel",
									items : [me.getDetailGrid(), me.getPWGrid()]
								}]
					}]
		});

		me.callParent(arguments);

		me.refreshMainGrid();
	},

	/**
	 * 工具栏
	 */
	getToolbarCmp : function() {
		var me = this;
		return [{
					text : "新建等级评分",
					scope : me,
					handler : me.onAddAssess,
					hidden : me.getPermission().add == "0",
					id : "buttonAdd"
				}, {
					hidden : me.getPermission().add == "0",
					xtype : "tbseparator"
				}, {
					text : "编辑等级评分",
					scope : me,
					handler : me.onEditAssess,
					hidden : me.getPermission().edit == "0",
					id : "buttonEdit"
				}, {
					hidden : me.getPermission().edit == "0",
					xtype : "tbseparator"
				}, {
					text : "删除等级评分",
					scope : me,
					handler : me.onDeleteAssess,
					hidden : me.getPermission().del == "0",
					id : "buttonDelete"
				}, {
					xtype : "tbseparator",
					hidden : me.getPermission().del == "0",
					id : "tbseparator1"
				}, {
					text : "提交",
					scope : me,
					handler : me.onCommit,
					hidden : me.getPermission().commit == "0",
					id : "buttonCommit"
				}, /*{
					text : "取消提交",
					hidden:true,
					scope : me,
					handler : me.onCancelConfirm,
					hidden : me.getPermission().confirm == "0",
					id : "buttonCancelConfirm"
				}, */{
					xtype : "tbseparator",
            		hidden:true,
					hidden : me.getPermission().commit == "0",
					id : "tbseparator2"
				}, "-", {
					text : "关闭",
					handler : function() {
						me.closeWindow();
					}
				}];
	},

	/**
	 * 查询条件
	 */
	getQueryCmp : function() {
		var me = this;
		return [ {
            id : "tableStatus",
            xtype : "combo",
            queryMode : "local",
            editable : false,
            valueField : "id",
            labelWidth : 60,
            labelAlign : "right",
            labelSeparator : "",
            fieldLabel : "状态",
            margin : "5, 20, 0, 10",
            store : Ext.create("Ext.data.ArrayStore", {
                fields : ["id", "text"],
                data : [[0, "已删除"], [1,"未提交"],[2, "已提交"]]
            }),
            value :1
        }, {
			id : "editCompanyName",
			labelWidth : 80,
			width:300,
			labelAlign : "right",
			labelSeparator : "",
			fieldLabel : "往来单位名称",
			margin : "5, 20, 0, 10",
			xtype : "textfield"
		},{
			xtype : "container",
			items : [{
						xtype : "button",
						text : "查询",
						width : 100,
						height : 26,
						margin : "5 0 0 10",
						handler : me.onQuery,
						scope : me
					}, {
						xtype : "button",
						text : "清空查询条件",
						width : 100,
						height : 26,
						margin : "5, 0, 0, 10",
						handler : me.onClearQuery,
						scope : me
					}]
		}, {
			xtype : "container",
			items : [{
						xtype : "button",
						iconCls : "PSI-button-hide",
						text : "隐藏查询条件栏",
						width : 130,
						height : 26,
						margin : "5 0 0 10",
						handler : function() {
							Ext.getCmp("panelQueryCmp").collapse();
						},
						scope : me
					}]
		}];
	},

	/**
	 * 等级评分列表
	 */
	getMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSIPOBill";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "companyName", "assessTimes","companyType", "limit",
                        	"companyAssetType","companyAddrType", "otherCompany", "companyTradeType",
							"companyStrength", "registerAddr", "legalPerson","contact",
                        	"contactTel", "mainWork","tableStatus", "status"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : [],
					pageSize : 20,
					proxy : {
						type : "ajax",
						actionMethods : {
							read : "POST"
						},
						url : me.URL("Home/COCompany/creditAssessList"),
						reader : {
							root : 'dataList',
							totalProperty : 'totalCount'
						}
					}
				});
		store.on("beforeload", function() {
					store.proxy.extraParams = me.getQueryParam();
				});
		store.on("load", function(e, records, successful) {
					if (successful) {
						me.gotoMainGridRecord(me.__lastId);
					}
				});

		me.__mainGrid = Ext.create("Ext.grid.Panel", {
			cls : "PSI",
			viewConfig : {
				enableTextSelection : true
			},
			border : 1,
			columnLines : true,
			columns : {
				defaults : {
					menuDisabled : true,
					sortable : false
				},
				items : [{
							xtype : "rownumberer",
							width : 50
						},  {
							header : "状态",
							dataIndex : "tableStatus",
							width : 100,
							renderer:function (value) {
								var str="";
								if(value==0){
									str="已删除";
								}else if(value>0&&value<4000){
									str="未提交";
								}else if(value==4000){
									str="已提交";
								}
								return str;
							}
						},{
							header : "往来单位名称",
							dataIndex : "companyName",
							width : 110
						}, {
							header : "交易类型",
							dataIndex : "companyType",
                            width : 120,
                            renderer:function (value) {
							    var str="";
                                for(var i=0;i<value.length;i++){
                                    if(value[i]==1){
                                        str+="供应商";
                                    }
                                    if(value[i]==2){
                                        if(str){
                                            str+="/客户";
                                        }else{
                                            str+="客户";
                                        }
                                    }
                                }

                                return str;
                            }
						}, {
							header : "原额度(吨/公斤)",
							dataIndex : "limit",
                            width : 120,
                            align:"right"
						}, {
							header : "法定代表人",
							dataIndex : "legalPerson"
						}, {
							header : "注册地址",
							dataIndex : "registerAddr",
                    		width : 300
						}, {
							header : "联系人",
							dataIndex : "contact"
						}, {
							header : "联系电话",
							dataIndex : "contactTel",
                            width : 120,
						}, {
							header : "公司性质",
							dataIndex : "companyAssetType",
                            width : 120,
                            renderer:function (value) {
                                var str="";

                                if(value==1){
                                    str="国有独资";
                                }else if(value==2){
                                    str="上市公司";
                                }else if(value==3){
                                    str="中外合资";
                                }else if(value==4){
                                    str="外商独资";
                                }else if(value==5){
                                    str="民营独资";
                                }else if(value==6){
                                    str="民营股份";
                                }else if(value==7){
                                    str="其他";
                                }

                                return str;
                            }
						}, {
							header : "公司类型",
							dataIndex : "companyTradeType",
                            width : 120,
                            renderer:function (value) {
                                var str="";
                                for(var i=0;i<value.length;i++){
                                    if(value[i]==1){
                                        str+="制造商";
                                    }
                                    if(value[i]==2){
                                        if(str){
                                            str+="/贸易商";
                                        }else{
                                            str+="贸易商";
                                        }
                                    }
                                }

                                return str;
                            }
						}, {
							header : "行业地位",
							dataIndex : "companyStrength",
                            width : 120,
                            renderer:function (value) {
                                var str="";

                                if(value==1){
                                    str="全球500强";
                                }else if(value==2){
                                    str="全国500强";
                                }else if(value==3){
                                    str="行业500强";
                                }
                                return str;
                            }
						}, {
							header : "主营业务",
							dataIndex : "mainWork",
							width : 350
						}]
			},
			store : store,
			bbar : ["->", {
						id : "pagingToobar",
						xtype : "pagingtoolbar",
						border : 0,
						store : store
					}, "-", {
						xtype : "displayfield",
						value : "每页显示"
					}, {
						id : "comboCountPerPage",
						xtype : "combobox",
						editable : false,
						width : 60,
						store : Ext.create("Ext.data.ArrayStore", {
									fields : ["text"],
									data : [["20"], ["50"], ["100"], ["300"],
											["1000"]]
								}),
						value : 20,
						listeners : {
							change : {
								fn : function() {
									store.pageSize = Ext
											.getCmp("comboCountPerPage")
											.getValue();
									store.currentPage = 1;
									Ext.getCmp("pagingToobar").doRefresh();
								},
								scope : me
							}
						}
					}, {
						xtype : "displayfield",
						value : "条记录"
					}],
			listeners : {
				select : {
					fn : me.onMainGridSelect,
					scope : me
				},
				itemdblclick : {
					fn : !me.getPermission().edit
							? me.onEditAssess
							: Ext.emptyFn,
					scope : me
				}
			}
		});

		return me.__mainGrid;
	},

	/**
	 * 等级评分明细记录
	 */
	getDetailGrid : function() {
		var me = this;
		if (me.__detailGrid) {
			return me.__detailGrid;
		}

		var modelName = "PSIPOBillDetail";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "goodsCode", "goodsName", "goodsSpec",
							"unitName", "goodsCount", "goodsMoney",
							"goodsPrice", "taxRate", "tax", "moneyWithTax",
							"pwCount", "leftCount", "memo"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : []
				});

		me.__detailGrid = Ext.create("Ext.grid.Panel", {
					cls : "PSI",
					title : "等级评分明细",
					viewConfig : {
						enableTextSelection : true
					},
					columnLines : true,
					columns : {
						defaults : {
							menuDisabled : true,
							sortable : false
						},
						items : [Ext.create("Ext.grid.RowNumberer", {
											text : "序号",
											width : 40
										}), {
									header : "商品编码",
									dataIndex : "goodsCode",
									width : 120
								}, {
									header : "商品名称",
									dataIndex : "goodsName",
									width : 200
								}, {
									header : "规格型号",
									dataIndex : "goodsSpec",
									width : 200
								}, {
									header : "采购数量",
									dataIndex : "goodsCount",
									align : "right"
								}, {
									header : "入库数量",
									dataIndex : "pwCount",
									align : "right"
								}, {
									header : "未入库数量",
									dataIndex : "leftCount",
									align : "right",
									renderer : function(value) {
										if (value > 0) {
											return "<span style='color:red'>"
													+ value + "</span>";
										} else {
											return value;
										}
									}
								}, {
									header : "单位",
									dataIndex : "unitName",
									width : 60
								}, {
									header : "采购单价",
									dataIndex : "goodsPrice",
									align : "right",
									xtype : "numbercolumn",
									width : 150
								}, {
									header : "采购金额",
									dataIndex : "goodsMoney",
									align : "right",
									xtype : "numbercolumn",
									width : 150
								}, {
									header : "税率(%)",
									dataIndex : "taxRate",
									align : "right",
									xtype : "numbercolumn",
									format : "0"
								}, {
									header : "税金",
									dataIndex : "tax",
									align : "right",
									xtype : "numbercolumn",
									width : 150
								}, {
									header : "价税合计",
									dataIndex : "moneyWithTax",
									align : "right",
									xtype : "numbercolumn",
									width : 150
								}, {
									header : "备注",
									dataIndex : "memo",
									width : 120
								}]
					},
					store : store
				});

		return me.__detailGrid;
	},

	/**
	 * 刷新等级评分列表
	 */
	refreshMainGrid : function(id) {
		var me = this;

		Ext.getCmp("pagingToobar").doRefresh();

		me.__lastId = id;
	},

	/**
	 * 新增等级评分
	 */
	onAddAssess : function() {
		var me = this;

		var form = Ext.create("PSI.COCompany.SelectCreditAssess", {
					parentForm : me,
					showAddGoodsButton : me.getPermission().showAddGoodsButton
				});
		form.show();
	},

	onAddRankAssess:function (id) {
        var me = this;

<<<<<<< HEAD
        var form = Ext.create("PSI.COCompany.EditForm", {
=======
        var form = Ext.create("PSI.COCompany.EditRankAssess", {
>>>>>>> 974fc5fcbb9103e9ccd6ca62f35a3af2c9ee3e0a
            parentForm : me,
			creditAssessId:id
        });
        form.show();
    },

	/**
	 * 编辑等级评分
	 */
    onEditAssess : function() {
		var me = this;
		
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("没有选择要编辑的等级评分");
			return;
		}
		var creditAssess = item[0];

		var form = Ext.create("PSI.COCompany.EditForm", {
					parentForm : me,
					entity : creditAssess,
					showAddGoodsButton : me.getPermission().showAddGoodsButton
				});
		form.show();
	},

	/**
	 * 删除等级评分
	 */
	onDeleteAssess : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("请选择要删除的等级评分");
			return;
		}

		var assess = item[0];

		/*if (assess.get("billStatus") > 0) {
			me.showInfo("当前等级评分已经审核，不能删除");
			return;
		}*/


		var info = "请确认是否删除对[<span style='color:red'>"+assess.get("companyName")+"</span>]的等级评分 ";
		var funcConfirm = function() {
			var el = Ext.getBody();
			el.mask("正在删除中...");
			var r = {
				url : me.URL("Home/COCompany/deleteAssess"),
				params : {
					id : assess.get("id"),
					companyName:assess.get("companyName")
				},
				callback : function(options, success, response) {
					el.unmask();

					if (success) {
						var data = me.decodeJSON(response.responseText);
						if (data.success) {
							me.showInfo("成功完成删除操作", function() {
										me.refreshMainGrid(assess.get("id"));
									});
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
	},

	onMainGridSelect : function() {
		var me = this;

		me.getDetailGrid().setTitle("等级评分操作明细");
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			Ext.getCmp("buttonEdit").setDisabled(true);
			Ext.getCmp("buttonDelete").setDisabled(true);
			Ext.getCmp("buttonCommit").setDisabled(true);


			return;
		}
		var assess = item[0];
		var tableStatus = assess.get("tableStatus");

		var buttonEdit = Ext.getCmp("buttonEdit");
		buttonEdit.setDisabled(false);
		if (tableStatus==0||tableStatus==4000||me.getPermission().creditEdit==0) {
			buttonEdit.setText("查看等级评分");
		} else {
			buttonEdit.setText("编辑等级评分");
		}
		console.log()
		if(tableStatus!=3000){
            Ext.getCmp("buttonCommit").setDisabled(true);
		}else{
            Ext.getCmp("buttonCommit").setDisabled(false);
		}

		me.refreshDetailGrid();
		me.refreshPWGrid();
	},

	/**
	 * 刷新等级评分明细记录
	 */
	refreshDetailGrid : function(id) {
		var me = this;
		me.getDetailGrid().setTitle("等级评分明细");
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var bill = item[0];

		var grid = me.getDetailGrid();
		grid.setTitle("单号: " + bill.get("ref") + " 供应商: "
				+ bill.get("supplierName"));
		var el = grid.getEl();
		el.mask(PSI.Const.LOADING);

		var r = {
			url : me.URL("Home/Purchase/poBillDetailList"),
			params : {
				id : bill.get("id")
			},
			callback : function(options, success, response) {
				var store = grid.getStore();

				store.removeAll();

				if (success) {
					var data = me.decodeJSON(response.responseText);
					store.add(data);

					if (store.getCount() > 0) {
						if (id) {
							var r = store.findExact("id", id);
							if (r != -1) {
								grid.getSelectionModel().select(r);
							}
						}
					}
				}

				el.unmask();
			}
		};
		me.ajax(r);
	},

	/**
	 * 审核等级评分
	 */
	onCommit : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("没有选择要提交的等级评分");
			return;
		}
		var assess = item[0];

		if (assess.get("tableStatus") !=3000) {
			me.showInfo("当前等级评分不符合审核条件，不能提交");
			return;
		}


		var info = "请确认是否提交往来单位: <span style='color:red'>" + assess.get("companyName")
				+ "</span> 的等级评分?";
		var id = assess.get("id");

		var funcConfirm = function() {
			var el = Ext.getBody();
			el.mask("正在提交中...");
			var r = {
				url : me.URL("Home/COCompany/commitAssess"),
				params : {
					id : id,
					companyName:assess.get("companyName")
				},
				callback : function(options, success, response) {
					el.unmask();

					if (success) {
						var data = me.decodeJSON(response.responseText);
						if (data.success) {
							me.showInfo("成功完成提交操作", function() {
										me.refreshMainGrid(id);
									});
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
	},

	/**
	 * 取消审核
	 */
	onCancelConfirm : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("没有选择要取消审核的等级评分");
			return;
		}
		var bill = item[0];

		if (bill.get("billStatus") == 0) {
			me.showInfo("当前等级评分还没有审核，无法取消审核");
			return;
		}

		var info = "请确认是否取消审核单号为 <span style='color:red'>" + bill.get("ref")
				+ "</span> 的等级评分?";
		var id = bill.get("id");
		var funcConfirm = function() {
			var el = Ext.getBody();
			el.mask("正在提交中...");
			var r = {
				url : me.URL("Home/Purchase/cancelConfirmPOBill"),
				params : {
					id : id
				},
				callback : function(options, success, response) {
					el.unmask();

					if (success) {
						var data = me.decodeJSON(response.responseText);
						if (data.success) {
							me.showInfo("成功完成取消审核操作", function() {
										me.refreshMainGrid(id);
									});
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
	},

	gotoMainGridRecord : function(id) {
		var me = this;
		var grid = me.getMainGrid();
		grid.getSelectionModel().deselectAll();
		var store = grid.getStore();
		if (id) {
			var r = store.findExact("id", id);
			if (r != -1) {
				grid.getSelectionModel().select(r);
			} else {
				grid.getSelectionModel().select(0);
			}
		} else {
			grid.getSelectionModel().select(0);
		}
	},

	/**
	 * 查询
	 */
	onQuery : function() {
		var me = this;

		me.getMainGrid().getStore().currentPage = 1;
		me.refreshMainGrid();
	},

	/**
	 * 清除查询条件
	 */
	onClearQuery : function() {
		var me = this;

		Ext.getCmp("editCompanyName").setValue(null);

		me.onQuery();
	},

	getQueryParam : function() {
		var me = this;

		var result = {
			tableStatus:Ext.getCmp("tableStatus").getValue()
		};

		var companyName = Ext.getCmp("editCompanyName").getValue();
		if (companyName) {
			result.companyName = companyName;
		}


		return result;
	},



	getPWGrid : function() {
		var me = this;
		if (me.__pwGrid) {
			return me.__pwGrid;
		}
		var modelName = "PSIPOBill_PWBill";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "ref", "bizDate", "supplierName",
							"warehouseName", "inputUserName", "bizUserName",
							"billStatus", "amount", "dateCreated",
							"paymentType"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : []
				});

		me.__pwGrid = Ext.create("Ext.grid.Panel", {
			cls : "PSI",
			title : "等级评分入库详情",
			viewConfig : {
				enableTextSelection : true
			},
			columnLines : true,
			columns : {
				defaults : {
					menuDisabled : true,
					sortable : false
				},
				items : [{
							xtype : "rownumberer",
							width : 50
						}, {
							header : "状态",
							dataIndex : "billStatus",
							width : 60,
							renderer : function(value) {
								if (value == "待入库") {
									return "<span style='color:red'>" + value
											+ "</span>";
								} else if (value == "已退货") {
									return "<span style='color:blue'>" + value
											+ "</span>";
								} else {
									return value;
								}
							}
						}, {
							header : "入库单号",
							dataIndex : "ref",
							width : 110,
							renderer : function(value, md, record) {
								return "<a href='"
										+ PSI.Const.BASE_URL
										+ "Home/Bill/viewIndex?fid=2027&refType=采购入库&ref="
										+ encodeURIComponent(record.get("ref"))
										+ "' target='_blank'>" + value + "</a>";
							}
						}, {
							header : "业务日期",
							dataIndex : "bizDate"
						}, {
							header : "供应商",
							dataIndex : "supplierName",
							width : 300
						}, {
							header : "采购金额",
							dataIndex : "amount",
							align : "right",
							xtype : "numbercolumn",
							width : 150
						}, {
							header : "付款方式",
							dataIndex : "paymentType",
							width : 100,
							renderer : function(value) {
								if (value == 0) {
									return "记应付账款";
								} else if (value == 1) {
									return "现金付款";
								} else if (value == 2) {
									return "预付款";
								} else {
									return "";
								}
							}
						}, {
							header : "入库仓库",
							dataIndex : "warehouseName"
						}, {
							header : "业务员",
							dataIndex : "bizUserName"
						}, {
							header : "制单人",
							dataIndex : "inputUserName"
						}, {
							header : "制单时间",
							dataIndex : "dateCreated",
							width : 140
						}]
			},
			store : store
		});

		return me.__pwGrid;
	},

	refreshPWGrid : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var bill = item[0];

		var grid = me.getPWGrid();
		var el = grid.getEl();
		if (el) {
			el.mask(PSI.Const.LOADING);
		}

		var r = {
			url : me.URL("Home/Purchase/poBillPWBillList"),
			params : {
				id : bill.get("id")
			},
			callback : function(options, success, response) {
				var store = grid.getStore();

				store.removeAll();

				if (success) {
					var data = me.decodeJSON(response.responseText);
					store.add(data);
				}

				if (el) {
					el.unmask();
				}
			}
		};
		me.ajax(r);
	},


});