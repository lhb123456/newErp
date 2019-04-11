/**
 * 往来单位 - 新增或编辑界面
 */
Ext.define("PSI.COCompany.COCompanyEditForm", {
	extend : "PSI.AFX.BaseDialogForm",

	initComponent : function() {
		var me = this;
		var entity = me.getEntity();
		me.adding = entity == null;
		var buttons = [];
		if (!entity) {
			buttons.push({
						text : "保存并继续新增",
						formBind : true,
						handler : function() {
							me.onOK(true);
						},
						scope : me
					});
		}

		buttons.push({
					text : "保存",
					formBind : true,
					iconCls : "PSI-button-ok",
					handler : function() {
						me.onOK(false);
					},
					scope : me
				}, {
					text : me.adding ? "关闭" : "取消",
					handler : function() {
						me.close();
					},
					scope : me
				});

		var categoryStore = null;
		if (me.getParentForm()) {
			categoryStore = me.getParentForm().categoryGrid.getStore();
		}

		var t = entity == null ? "新增往来单位" : "编辑往来单位";
		var f = entity == null
				? "edit-form-create.png"
				: "edit-form-update.png";
		var logoHtml = "<img style='float:left;margin:10px 20px 0px 10px;width:48px;height:48px;' src='"
				+ PSI.Const.BASE_URL
				+ "Public/Images/"
				+ f
				+ "'></img>"
				+ "<h2 style='color:#196d83'>"
				+ t
				+ "</h2>"
				+ "<p style='color:#196d83'>标记 <span style='color:red;font-weight:bold'>*</span>的是必须录入数据的字段</p>";

		Ext.apply(me, {
			header : {
				title : me.formatTitle(PSI.Const.PROD_NAME),
				height : 40
			},
			width : 550,
			height : 650,
			layout : "border",
			items : [{
						region : "north",
						border : 0,
						height : 90,
						html : logoHtml
					}, {
						region : "center",
						border : 0,
						id : "PSI_COCompany_COCompanyEditForm_editForm",
						xtype : "form",
						layout : {
							type : "table",
							columns : 2
						},
						height : "100%",
						bodyPadding : 5,
						defaultType : 'textfield',
						fieldDefaults : {
							labelWidth : 100,
							labelAlign : "right",
							labelSeparator : "",
							msgTarget : 'side'
						},
						items : [{
									id:"hiddenId",
									xtype : "hidden",
									name : "id",
									value : entity == null ? null : entity
											.get("id")
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editCategory",
									xtype : "combo",
									fieldLabel : "分类",
									allowBlank : false,
									blankText : "没有输入往来单位分类",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									valueField : "id",
									displayField : "name",
									store : categoryStore,
									queryMode : "local",
									editable : false,
									value : categoryStore != null
											? categoryStore.getAt(0).get("id")
											: null,
									name : "categoryId",
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editCode",
									fieldLabel : "编码",
									allowBlank : false,
									readOnly:true,
									blankText : "没有输入往来单位编码",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name : "code",
									value : entity == null ? null : entity
											.get("code"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								},{
									id:"companyType",
									xtype: 'checkboxgroup',
									name: 'companyType',
                            		width : 350,
                            		colspan : 2,
									columns: 2,  //在上面定义的宽度上展示3列
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									fieldLabel: '往来单位类型:',
									items: [
										{boxLabel: '供应商', name: 'supplier'},
										{boxLabel: '客户', name: 'customer'}
									]
								}, {
									id : "editRank",
									xtype : "textfield",
									fieldLabel : "往来单位等级",
									allowBlank : false,
									blankText : "没有输入往来单位等级",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name: "rank",
									value : entity == null ? null : entity
										.get("rank"),
									queryMode : "local",
									editable : false,
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									},
									callbackFunc:me.__selectRank,
									callbackObj:me
								},{
									id:"rankId",
									xtype : "hidden",
									name : "rankId",
									value : entity == null ? null : entity
										.get("rankId"),
								},{
									id : "limitCount",
									fieldLabel : "销售限制(吨)",
									name : "limitCount",
									value : entity == null ? 0 : entity
										.get("limitCount"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										},
                                        blur:function () {
                                            if(isNaN(this.getValue())||this.getValue()<0){
                                                me.showInfo("数量必须为大于零的数值");
                                                this.setValue();
                                            }
                                        }
									}
								},{
									id : "PSI_COCompany_COCompanyEditForm_editName",
									fieldLabel : "往来单位名称",
									allowBlank : false,
									blankText : "没有输入往来单位名称",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name : "name",
									value : entity == null ? null : entity
											.get("name"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									},
									width : 510,
									colspan : 2
								},{
									id : "PSI_COCompany_COCompanyEditForm_editAnotherName",
									fieldLabel : "往来单位别名",
									name : "anotherName",
									value : entity == null ? null : entity
										.get("anotherName"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									},
									width : 510,
									colspan : 2
								}, {
									id : "addressInvoice",
									fieldLabel : "开票地址",
									name : "addressInvoice",
									value : entity == null ? null : entity
										.get("addressInvoice"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									},
								}, {
									id : "invoiceTel",
									fieldLabel : "开票电话",
									name : "invoiceTel",
									/*value : entity == null ? null : entity
										.get("addressInvoice"),*/
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									},
								},{
									id : "PSI_COCompany_COCompanyEditForm_editBankName",
									fieldLabel : "开户行",
									name : "bankName",
									value : entity == null ? null : entity
										.get("bankName"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editBankAccount",
									fieldLabel : "开户行账号",
									name : "bankAccount",
									value : entity == null ? null : entity
										.get("bankAccount"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editTax",
									fieldLabel : "税号",
									name : "tax",
									value : entity == null ? null : entity
										.get("tax"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editFax",
									fieldLabel : "传真",
									name : "fax",
									value : entity == null ? null : entity
										.get("fax"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								},{
									id : "legalPerson",
									fieldLabel : "法人",
									name : "legalPerson",
									value : entity == null ? null : entity
										.get("legalPerson"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								},{
									id : "registerMoney",
									fieldLabel : "注册资金",
									name : "registerMoney",
									value : entity == null ? null : entity
										.get("registerMoney"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								},{
									id : "companyIntro",
									fieldLabel : "简介",
									name : "companyIntro",
									value : entity == null ? null : entity
										.get("companyIntro"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									},
									width : 510,
									colspan : 2
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editAddress",
									fieldLabel : "联系地址",
									name : "address",
									value : entity == null ? null : entity
											.get("address"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									},
									width : 510,
									colspan : 2
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editContact",
									fieldLabel : "联系人",
									name : "contact",
									value : entity == null ? null : entity
											.get("contact"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editMobile",
									fieldLabel : "手机",
									name : "mobile",
									value : entity == null ? null : entity
											.get("mobile"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editTel",
									fieldLabel : "固话",
									name : "tel",
									value : entity == null ? null : entity
											.get("tel"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editQQ",
									fieldLabel : "QQ",
									name : "qq",
									value : entity == null ? null : entity
											.get("qq"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editContact02",
									fieldLabel : "备用联系人",
									hidden:true,
									name : "contact02",
									value : entity == null ? null : entity
											.get("contact02"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editMobile02",
									fieldLabel : "备用联系人手机",
                            		hidden:true,
									name : "mobile02",
									value : entity == null ? null : entity
											.get("mobile02"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editTel02",
									fieldLabel : "备用联系人固话",
                            		hidden:true,
									name : "tel02",
									value : entity == null ? null : entity
											.get("tel02"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editQQ02",
									fieldLabel : "备用联系人QQ",
                            		hidden:true,
									name : "qq02",
									value : entity == null ? null : entity
											.get("qq02"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editAddressReceipt",
									fieldLabel : "邮寄地址",
									name : "addressReceipt",
									value : entity == null ? null : entity
											.get("addressReceipt"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									},
									width : 510,
									colspan : 2
								},  {
									id : "PSI_COCompany_COCompanyEditForm_editInitReceivables",
									fieldLabel : "应收期初余额",
									name : "initReceivables",
									hidden:true,
									xtype : "numberfield",
									hideTrigger : true,
									value : entity == null ? null : entity
											.get("initReceivables"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editInitReceivablesDT",
									fieldLabel : "余额日期",
                            		hidden:true,
									name : "initReceivablesDT",
									xtype : "datefield",
									format : "Y-m-d",
									value : entity == null ? null : entity
											.get("initReceivablesDT"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editWarehouse",
									xtype : "psi_warehousefield",
									fieldLabel : "销售出库仓库",
                            		hidden:true,
									value : null,
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									},
									width : 510,
									colspan : 2
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editWarehouseId",
									xtype : "hiddenfield",
									name : "warehouseId"
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editMemo",
									fieldLabel : "备注",
									name : "memo",
									value : entity == null ? null : entity
											.get("memo"),
									listeners : {
										specialkey : {
											fn : me.onEditLastSpecialKey,
											scope : me
										}
									},
									width : 510,
									colspan : 2
								}, {
									id : "PSI_COCompany_COCompanyEditForm_editRecordStatus",
									xtype : "combo",
									queryMode : "local",
									editable : false,
									valueField : "id",
									fieldLabel : "状态",
									name : "recordStatus",
									store : Ext.create("Ext.data.ArrayStore", {
												fields : ["id", "text"],
												data : [[1000, "启用"], [0, "停用"]]
											}),
									value : 1000
								}],
						buttons : buttons
					}],
			listeners : {
				show : {
					fn : me.onWndShow,
					scope : me
				},
				close : {
					fn : me.onWndClose,
					scope : me
				}
			}
		});

		me.callParent(arguments);

		me.editForm = Ext.getCmp("PSI_COCompany_COCompanyEditForm_editForm");
		me.editCategory = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editCategory");
		me.editCode = Ext.getCmp("PSI_COCompany_COCompanyEditForm_editCode");
		me.editName = Ext.getCmp("PSI_COCompany_COCompanyEditForm_editName");
		me.editAnotherName = Ext.getCmp("PSI_COCompany_COCompanyEditForm_editAnotherName");
		me.editAddressInvoice = Ext.getCmp("addressInvoice");
		me.editInvoiceTel = Ext.getCmp("invoiceTel");
		me.editAddress = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editAddress");
		me.editContact = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editContact");
		me.editMobile = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editMobile");
		me.editTel = Ext.getCmp("PSI_COCompany_COCompanyEditForm_editTel");
		me.editQQ = Ext.getCmp("PSI_COCompany_COCompanyEditForm_editQQ");
		me.editContact02 = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editContact02");
		me.editMobile02 = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editMobile02");
		me.editTel02 = Ext.getCmp("PSI_COCompany_COCompanyEditForm_editTel02");
		me.editQQ02 = Ext.getCmp("PSI_COCompany_COCompanyEditForm_editQQ02");
		me.editAddressReceipt = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editAddressReceipt");
		me.editBankName = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editBankName");
		me.editBankAccount = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editBankAccount");
		me.editTax = Ext.getCmp("PSI_COCompany_COCompanyEditForm_editTax");
		me.editFax = Ext.getCmp("PSI_COCompany_COCompanyEditForm_editFax");
		me.editInitReceivables = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editInitReceivables");
		me.editInitReceivablesDT = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editInitReceivablesDT");
		me.editWarehouse = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editWarehouse");
		me.editWarehouseId = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editWarehouseId");
		me.editMemo = Ext.getCmp("PSI_COCompany_COCompanyEditForm_editMemo");
		me.editRecordStatus = Ext
				.getCmp("PSI_COCompany_COCompanyEditForm_editRecordStatus");

		me.__editorList = [me.editCategory, me.editCode, me.editName,
				me.editAddress, me.editContact01, me.editMobile01,
				me.editTel01, me.editQQ01, me.editContact02, me.editMobile02,
				me.editTel02, me.editQQ02, me.editAddressReceipt,
				me.editBankName, me.editBankAccount, me.editTax, me.editFax,
				me.editInitReceivables, me.editInitReceivablesDT,
				me.editWarehouse, me.editNote];
	},

	onWindowBeforeUnload : function(e) {
		return (window.event.returnValue = e.returnValue = '确认离开当前页面？');
	},

	onWndShow : function() {
		var me = this;

		Ext.get(window).on('beforeunload', me.onWindowBeforeUnload);

		if (!me.adding) {
			// 编辑往来单位资料
			var el = me.getEl();
			el.mask(PSI.Const.LOADING);
			Ext.Ajax.request({
						url : me.URL("/Home/COCompany/cocompanyInfo"),
						params : {
							id : me.getEntity().get("id")
						},
						method : "POST",
						callback : function(options, success, response) {
							if (success) {

								var data = Ext.JSON.decode(response.responseText);
								me.editCategory.setValue(data.categoryId);
								me.editCode.setValue(data.code);
								me.editName.setValue(data.name);
                                me.editAnotherName.setValue(data.anotherName);
								me.editAddress.setValue(data.address);
                                Ext.getCmp("rankId").setValue(data.rankId);
                                Ext.getCmp("editRank").setValue(data.rank);
                                Ext.getCmp("limitCount").setValue(data.limiltCount);
                                me.editAddressInvoice.setValue(data.addressInvoice);
                                me.editInvoiceTel.setValue(data.invoiceTel);
                                me.editBankName.setValue(data.bankName);
                                me.editBankAccount.setValue(data.bankAccount);
                                me.editAddressReceipt.setValue(data.addressReceipt);
								me.editContact.setValue(data.contact);
								me.editMobile.setValue(data.mobile);
								me.editTel.setValue(data.tel);
								me.editQQ.setValue(data.qq);
								me.editTax.setValue(data.tax);
								me.editFax.setValue(data.fax);
								me.editMemo.setValue(data.memo);
                                me.editRecordStatus.setValue(parseInt(data.status));

                                Ext.getCmp("companyType").items.each(function(item) {

								  if(item.name=="customer"&&data.is_customer==1){
									item.setValue(true);
								  }
								  if(item.name=="supplier"&&data.is_supplier==1){
								  	item.setValue(true);
								  }

                                });

							}

							el.unmask();
						}
					});
		} else {
			// 新建往来单位资料
			me.getNewCode();
			if (me.getParentForm()) {
				var grid = me.getParentForm().categoryGrid;
				var item = grid.getSelectionModel().getSelection();
				if (item == null || item.length != 1) {
					return;
				}

				me.editCategory.setValue(item[0].get("id"));
			} else {
				// 在其他界面中调用新增往来单位资料
				var modelName = "PSICOCompanyCategory_COCompanyEditForm";
				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["id", "code", "name", {
										name : "cnt",
										type : "int"
									}]
						});
				var store = Ext.create("Ext.data.Store", {
							model : modelName,
							autoLoad : false,
							data : []
						});
				me.editCategory.bindStore(store);
				var el = Ext.getBody();
				el.mask(PSI.Const.LOADING);
				Ext.Ajax.request({
							url : me.URL("/Home/COCompany/categoryList"),
							method : "POST",
							callback : function(options, success, response) {
								store.removeAll();

								if (success) {
									var data = Ext.JSON
											.decode(response.responseText);
									store.add(data);
									if (store.getCount() > 0) {
										var id = store.getAt(0).get("id");
										me.editCategory.setValue(id);
									}
								}

								el.unmask();
							}
						});
			}
		}

		var editCode = me.editCode;
		editCode.focus();
		editCode.setValue(editCode.getValue());
	},

	getNewCode:function () {
		var me=this;

        var el = Ext.getBody();
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url : me.URL("/Home/COCompany/getCompanyNewCode"),
            method : "POST",
            callback : function(options, success, response) {
                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    me.editCode.setValue(data.code)
                }

                el.unmask();
            }
        });
    },

	onWndClose : function() {
		var me = this;

		Ext.get(window).un('beforeunload', me.onWindowBeforeUnload);

		if (me.__lastId) {
			if (me.getParentForm()) {
				me.getParentForm().freshCOCompanyGrid(me.__lastId);
			}
		}
	},

	onOK : function(thenAdd) {
		var me = this;

		me.editWarehouseId.setValue(me.editWarehouse.getIdValue());

        Ext.getBody().mask("正在保存中...");
        Ext.Ajax.request({
            url : PSI.Const.BASE_URL + "/Home/COCompany/editCOCompany",
            method : "POST",
            params : {
                jsonStr : me.getSaveData()
            },
            callback : function(options, success, response) {
                Ext.getBody().unmask();

                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    if (data.success) {
                        PSI.MsgBox.showInfo("成功保存数据", function() {
                            me.close();

                            me.getParentForm().freshCOCompanyGrid(data.id);
                        });
                    } else {
                        PSI.MsgBox.showInfo(data.msg);
                    }
                }
            }
        });
	},

    getSaveData : function() {
        var me = this;

        // 2，收集复选框的值
        var companyType = [];
        var items = Ext.getCmp("companyType").items;
        for (var i = 0; i < items.length; i++) {
            if (items.items[i].checked) {
                companyType.push(items.items[i].name);
            }
        }

        var result = {
            id : Ext.getCmp("hiddenId").getValue(),
            categoryId:me.editCategory.getValue(),
			code:me.editCode.getValue(),
			name:me.editName.getValue(),
			anotherName:me.editAnotherName.getValue(),
			address:me.editAddress.getValue(),
			companyType:companyType,
			rankId:/*Ext.getCmp("rankId").getValue()*/1,
			limitCount:Ext.getCmp("limitCount").getValue(),
			addressInvoice:me.editAddressInvoice.getValue(),
			invoiceTel:me.editInvoiceTel.getValue(),
			bankName:me.editBankName.getValue(),
			bankAccount:me.editBankAccount.getValue(),
			tax:me.editTax.getValue(),
			fax:me.editFax.getValue(),
            legalPerson:Ext.getCmp("legalPerson").getValue(),
            registerMoney:Ext.getCmp("registerMoney").getValue(),
            companyIntro:Ext.getCmp("companyIntro").getValue(),
			addressReceipt:me.editAddressReceipt.getValue(),
			contact:me.editContact.getValue(),
			tel:me.editTel.getValue(),
			mobile:me.editMobile.getValue(),
			qq:me.editQQ.getValue(),
			memo:me.editMemo.getValue(),
			status:me.editRecordStatus.getValue()
        };

        return Ext.JSON.encode(result);
    },

	onEditSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() === e.ENTER) {
			var id = field.getId();
			for (var i = 0; i < me.__editorList.length; i++) {
				var editor = me.__editorList[i];
				if (id === editor.getId()) {
					var edit = me.__editorList[i + 1];
					edit.focus();
					edit.setValue(edit.getValue());
				}
			}
		}
	},

	onEditLastSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() == e.ENTER) {
			var f = me.editForm;
			if (f.getForm().isValid()) {
				me.onOK(me.adding);
			}
		}
	},

	clearEdit : function() {
		var me = this;

		me.editCode.focus();

		var editors = [me.editCode, me.editName, me.editAddress,
				me.editContact01, me.editMobile01, me.editTel01, me.editQQ01,
				me.editContact02, me.editMobile02, me.editTel02, me.editQQ02,
				me.editAddressReceipt, me.editBankName, me.editBankAccount,
				me.editTax, me.editFax, me.editNote, me.editInitReceivables,
				me.editInitReceivablesDT];
		for (var i = 0; i < editors.length; i++) {
			var edit = editors[i];
			if (edit) {
				edit.setValue(null);
				edit.clearInvalid();
			}
		}
	}
});