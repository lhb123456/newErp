/**
 * 商品构成 - 新增或编辑界面
 */
Ext.define("PSI.Goods.GoodsBOMEditForm", {
	extend : "PSI.AFX.BaseDialogForm",

	config : {
		goods : null
	},

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;

		var goods = me.getGoods();

		var entity = me.getEntity();

		me.adding = entity == null;

		var buttons = [];
		if (!entity) {
			var btn = {
				text : "保存并继续新增",
				formBind : true,
				handler : function() {
					me.onOK(true);
				},
				scope : me
			};

			buttons.push(btn);
		}

		var btn = {
			text : "保存",
			formBind : true,
			iconCls : "PSI-button-ok",
			handler : function() {
				me.onOK(false);
			},
			scope : me
		};
		buttons.push(btn);

		var btn = {
			text : entity == null ? "关闭" : "取消",
			handler : function() {
				me.close();
			},
			scope : me
		};
		buttons.push(btn);

		var t = entity == null ? "新增税控编码" : "编辑税控编码";
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
				+ "<p style='color:#196d83'>标记 <span style='color:red;font-weight:bold'>*</span>的是必须录入数据的字段</p>";;

		Ext.apply(me, {
			header : {
				title : me.formatTitle(PSI.Const.PROD_NAME),
				height : 40
			},
			width : 520,
			height : 480,
			layout : "border",
			listeners : {
				show : {
					fn : me.onWndShow,
					scope : me
				},
				close : {
					fn : me.onWndClose,
					scope : me
				}
			},
			items : [{
						region : "north",
						border : 0,
						height : 90,
						html : logoHtml
					}, {
						region : "center",
						border : 0,
						id : "PSI_Goods_GoodsBOMEditForm_editForm",
						xtype : "form",
						layout : {
							type : "table",
							columns : 1
						},
						height : "100%",
						bodyPadding : 5,
						defaultType : 'textfield',
						fieldDefaults : {
							labelAlign : "right",
							labelSeparator : "",
							msgTarget : 'side',
							margin : "5"
						},
						items : [{
									xtype : "hidden",
									name : "id",
									value : goods.get("id")
								},{
									id:"goodsCodeId",
									xtype : "hidden",
									name : "goodsCodeId",
                            		fieldLabel : "编辑时税编码的id",
									value : goods.get("id")
								}, {
									fieldLabel : "商品编码",
									width : 470,
									readOnly : true,
									value : goods.get("code")
								}, {
									fieldLabel : "品名",
									width : 470,
									readOnly : true,
									value : goods.get("name")
								}, {
									fieldLabel : "规格型号",
									readOnly : true,
									width : 470,
									value : goods.get("spec")
								}, {
                            fieldLabel : "商品单位",
                            readOnly : true,
                            width : 470,
                            value : goods.get("unitName")
                        }, {
                            id : "history_code",
                            labelAlign : "right",
                            labelSeparator : "",
							name:"history_code",
							hidden:true,
                            width : 470,
                            fieldLabel : "旧的税控编码",
                            margin : "5, 0, 0, 0",
                            xtype : "textfield"
                        },{
                            id : "code",
                            labelAlign : "right",
                            labelSeparator : "",
                            width : 470,
                            allowBlank : false,
                            name:"code",
                            beforeLabelTextTpl : PSI.Const.REQUIRED,
                            fieldLabel : "税控编码",
                            margin : "5, 0, 0, 0",
                            xtype : "textfield"
                        },{
                            id : "default",
                            xtype : "combo",
                            queryMode : "local",
                            editable : false,
                            valueField : "id",
                            labelWidth : 100,
                            name:"default",
                            width : 470,
                            labelAlign : "right",
                            labelSeparator : "",
                            margin : "5, 0, 0, 0",
                            fieldLabel : "是否设为默认",
                            store : Ext.create("Ext.data.ArrayStore", {
                                fields : ["id", "text"],
                                data : [[0, "否"], [1, "是"]]
                            }),
                            value :1
                        }],
						buttons : buttons
					}]
		});

		me.callParent(arguments);

		me.editForm = Ext.getCmp("PSI_Goods_GoodsBOMEditForm_editForm");
		me.history_code = Ext.getCmp("history_code");

		me.code = Ext
				.getCmp("code");
		me.default = Ext
				.getCmp("default");
	},
	/**
	 * 保存
	 */
	onOK : function(thenAdd) {
		var me = this;
		var f = me.editForm;
		var el = f.getEl();
		el.mask(PSI.Const.SAVING);
		var sf = {
			url : me.URL("/Home/Goods/editGoodsCode"),
			method : "POST",
			success : function(form, action) {
				me.__lastId = action.result.id;
				el.unmask();

				PSI.MsgBox.tip("数据保存成功");
				me.focus();
				if (thenAdd) {
					me.clearEdit();
				} else {
					me.close();
				}
			},
			failure : function(form, action) {
				el.unmask();
				PSI.MsgBox.showInfo(action.result.msg, function() {
							me.history_code.focus();
						});
			}
		};
		f.submit(sf);
	},

	onEditCodeSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() == e.ENTER) {
			var edit = me.editSubGoodsCount;
			edit.focus();
			edit.setValue(edit.getValue());
		}
	},

	onEditCountSpecialKey : function(field, e) {
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
		me.editSubGoodsCode.focus();

		var editors = [me.editSubGoodsId, me.editSubGoodsCode,
				me.editSubGoodsName, me.editSubGoodsSpec, me.editSubGoodsCount,
				me.editSubGoodsUnitName];
		for (var i = 0; i < editors.length; i++) {
			var edit = editors[i];
			edit.setValue(null);
			edit.clearInvalid();
		}
	},

	onWindowBeforeUnload : function(e) {
		return (window.event.returnValue = e.returnValue = '确认离开当前页面？');
	},

	onWndClose : function() {
		var me = this;

		Ext.get(window).un('beforeunload', me.onWindowBeforeUnload);

		if (me.getParentForm()) {
			me.getParentForm().refreshGoodsBOM();
		}
	},

	onWndShow : function() {
		var me = this;

		Ext.get(window).on('beforeunload', me.onWindowBeforeUnload);

		var subGoods = me.getEntity();
		if (!subGoods) {
			// 新增子商品

			var editCode = me.history_code;
			editCode.focus();
			editCode.setValue(editCode.getValue());

			return;
		}

		// 编辑子商品
		var r = {
			url : me.URL("Home/Goods/getGoodsCodeInfo"),
			params : {
				GoodsCodeId : subGoods.get("id")
			},
			callback : function(options, success, response) {
				if (success) {
					var data = me.decodeJSON(response.responseText);
						Ext.getCmp("history_code").setValue(data.tax_code)
						Ext.getCmp("code").setValue(data.tax_code)
						Ext.getCmp("goodsCodeId").setValue(data.id)
					if(data.default_code == 0){
                        Ext.getCmp("default").setValue(0)
					}
				} else {
					me.showInfo("网络错误");
				}
			}
		};
		me.ajax(r);
	},

	__setGoodsInfo : function(goods) {
		var me = this;
		if (goods) {
			me.editSubGoodsId.setValue(goods.get("id"));
			me.editSubGoodsName.setValue(goods.get("name"));
			me.editSubGoodsSpec.setValue(goods.get("spec"));
			me.editSubGoodsUnitName.setValue(goods.get("unitName"));
		} else {
			me.editSubGoodsId.setValue(null);
			me.editSubGoodsName.setValue(null);
			me.editSubGoodsSpec.setValue(null);
			me.editSubGoodsUnitName.setValue(null);
		}
	}
});