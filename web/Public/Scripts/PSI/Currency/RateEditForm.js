/**
 * 新增或编辑商品品牌
 */
Ext.define("PSI.Currency.RateEditForm", {
	extend : "PSI.AFX.BaseDialogForm",

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;
		var entity = me.getEntity();

		var t = entity == null ? "新增货币汇率" : "编辑货币汇率";
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
			width : 300,
			height : 300,
			layout : "border",
			items : [{
						region : "north",
						border : 0,
						height : 90,
						html : logoHtml
					}, {
						region : "center",
						border : 0,
						id : "PSI_Currency_RateEditForm_editForm",
						xtype : "form",
						layout : {
							type : "table",
							columns : 1
						},
						height : "100%",
						bodyPadding : 5,
						defaultType : 'textfield',
						fieldDefaults : {
							labelWidth : 50,
							labelAlign : "right",
							labelSeparator : "",
							msgTarget : 'side'
						},
						items : [{
									xtype : "hidden",
									name : "id",
									value : entity === null ? null : entity
											.get("id")
								},  {
									id : "PSI_Currency_RateEditForm_editCurrency",
									xtype : "PSI_Currency_editor",
									parentItem : me,
									fieldLabel : "交易币种",
                            		beforeLabelTextTpl : PSI.Const.REQUIRED,
									labelWidth : 60,
									listeners : {
										specialkey : {
											fn : me.onEditCurrencySpecialKey,
											scope : me
										}
									},
									width : 270
								}, {
									id : "PSI_Currency_RateEditForm_editCurrencyId",
									xtype : "hidden",
									name : "trade_currency",
									value : entity === null ? null : entity.get("trade_currency"),
								},{
									id : "PSI_Currency_RateEditForm_editCurrency2",
									xtype : "PSI_Currency_editor2",
									parentItem : me,
									fieldLabel : "兑换币种",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									labelWidth : 60,
									listeners : {
										specialkey : {
											fn : me.onEditCurrencySpecialKey,
											scope : me
										}
									},
									width :270
								}, {
									id : "PSI_Currency_RateEditForm_editCurrencyId2",
									xtype : "hidden",
									name : "change_currency",
									value : entity === null ? null : entity.get("change_currency")
								}, {
									id : "PSI_Currency_RateEditForm_editRate",
									fieldLabel : "汇率",
									labelWidth : 60,
									allowBlank : false,
									blankText : "没有输入汇率",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name : "rate",
									value : entity === null ? null : entity
										.get("rate"),
									listeners : {
										specialkey : {
											fn : me.onEditNameSpecialKey,
											scope : me
										}
									},
									width : 270
								}, {
									id : "editEffectiveDT",
									fieldLabel : "有效时间",
									labelWidth : 60,
									xtype : "datefield",
									format : "Y-m",
									allowBlank : false,
									blankText : "没有输入汇率",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name : "effective_time",
									value : entity === null ? new Date(): entity
										.get("effective_time"),
									listeners : {
										specialkey : {
											fn : me.onEditNameSpecialKey,
											scope : me
										}
									},
									width : 270
								}],
						buttons : [{
									text : "确定",
									formBind : true,
									iconCls : "PSI-button-ok",
									handler : me.onOK,
									scope : me
								}, {
									text : "取消",
									handler : function() {
										PSI.MsgBox.confirm("请确认是否取消操作?",
												function() {
													me.close();
												});
									},
									scope : me
								}]
					}],
			listeners : {
				show : {
					fn : me.onEditFormShow,
					scope : me
				},
				close : {
					fn : me.onWndClose,
					scope : me
				}
			}
		});

		me.callParent(arguments);

		me.editForm = Ext.getCmp("PSI_Currency_RateEditForm_editForm");

		me.EditCurrency = Ext
				.getCmp("PSI_Currency_RateEditForm_editCurrency");
		me.EditCurrencyId = Ext
				.getCmp("PSI_Currency_RateEditForm_editCurrencyId");
        me.EditCurrency2 = Ext
            	.getCmp("PSI_Currency_RateEditForm_editCurrency2");
        me.EditCurrencyId2 = Ext
            	.getCmp("PSI_Currency_RateEditForm_editCurrencyId2");
        me.EditEffective_time = Ext
            .getCmp("editEffectiveDT");

        if(entity){
            me.EditCurrency.setValue(entity.get("trade_currency_name"));
            me.EditCurrency2.setValue(entity.get("change_currency_name"));
        }
	},


	onWindowBeforeUnload : function(e) {
		return (window.event.returnValue = e.returnValue = '确认离开当前页面？');
	},

	onWndClose : function() {
		var me = this;

		Ext.get(window).un('beforeunload', me.onWindowBeforeUnload);
	},

	onEditFormShow : function() {
		var me = this;

		Ext.get(window).on('beforeunload', me.onWindowBeforeUnload);

        me.EditCurrency.focus();

		var entity = me.getEntity();
		if (entity === null) {
			return;
		}
		me.getEl().mask("数据加载中...");

		Ext.Ajax.request({
					url : me.URL("/Home/Currency/oneRate"),
					method : "POST",
					params : {
						id : entity.get("id")
					},
					callback : function(options, success, response) {
						me.getEl().unmask();

						if (success) {
							var data=Ext.JSON.decode(response.responseText);
							me.EditCurrency.setValue(data.trade_currency_name);
							me.EditCurrencyId.setValue(data.trade_currency);
                            me.EditCurrency2.setValue(data.change_currency);
                            me.EditCurrencyId2.setValue(data.change_currency_name);
						}
					}
				});
	},

	setParentBrand : function(data) {
		var me = this;

		me.EditCurrency.setValue(data.currency_name);
		me.EditCurrencyId.setValue(data.id);
	},

    setParentBrand2 : function(data) {
        var me = this;

        me.EditCurrency2.setValue(data.currency_name);
        me.EditCurrencyId2.setValue(data.id);
    },

	onOK : function() {
		var me = this;
		var f = me.editForm;
		var el = f.getEl();
		el.mask("数据保存中...");
		f.submit({
					url : me.URL("/Home/Currency/editRate"),
					method : "POST",
					success : function(form, action) {
						el.unmask();
						me.close();
						if (me.getParentForm()) {
							me.getParentForm().refreshGrid();
						}
					},
					failure : function(form, action) {
						el.unmask();
						PSI.MsgBox.showInfo(action.result.msg, function() {
                            me.EditCurrency.focus();
								});
					}
				});
	},

	onEditNameSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() == e.ENTER) {
			me.EditCurrency.focus();
		}
	},

	onEditCurrencySpecialKey : function(field, e) {
		var me = this;
		if (e.getKey() == e.ENTER) {
			if (me.editForm.getForm().isValid()) {
				me.onOK();
			}
		}
	}
});