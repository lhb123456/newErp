/**
 * 自定义字段 - 上级商品品牌字段
 */
Ext.define("PSI.Currency.CurrencyEditor", {
			extend : "Ext.form.field.Trigger",
			alias : "widget.PSI_Currency_editor",

			config : {
				parentItem : null
			},

			initComponent : function() {
                var me = this;
                me.__idValue = null;

				this.enableKeyEvents = true;

				this.callParent(arguments);

				this.on("keydown", function(field, e) {
							if (e.getKey() === e.BACKSPACE) {
								e.preventDefault();
								return false;
							}

							if (e.getKey() !== e.ENTER) {
								this.onTriggerClick(e);
							}
						});
			},

			/*onTriggerClick : function(e) {
				var me = this;

				var modelName = "PSIModel_CurrencyEditor";
				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["id", "currency_name"]
						});

				var store = Ext.create("Ext.data.Store", {
							model : modelName,
							proxy : {
								type : "ajax",
								actionMethods : {
									read : "POST"
								},
								url : PSI.Const.BASE_URL
										+ "Home/Currency/allCurrency"
							}
						});

				var lookupGrid = Ext.create("Ext.grid.Panel", {
							cls : "PSI",
							store : store,
							rootVisible : false,
							useArrows : true,
							viewConfig : {
								loadMask : true
							},
							columns : {
								defaults : {
									flex : 1,
									sortable : false,
									menuDisabled : true,
									draggable : false
								},
								items : [{
											xtype : "lookupGridcolumn",
											text : "币种",
											dataIndex : "currency_name"
										}]
							}
						});
                lookupGrid.on("itemdblclick", me.onOK, me);
				me.lookupGrid = lookupGrid;

				var wnd = Ext.create("Ext.window.Window", {
							title : "选择币种",
							modal : true,
							width : 400,
							height : 300,
							layout : "fit",
							items : [{
										region : "center",
										xtype : "panel",
										layout : "fit",
										border : 0,
										items : [lookupGrid]
                           			 }],
							buttons : [{
										text : "没有币种",
										handler : me.onNone,
										scope : me
									}, {
										text : "确定",
										handler : me.onOK,
										scope : me
									}, {
										text : "取消",
										handler : function() {
											wnd.close();
										}
									}]
						});
				me.wnd = wnd;
				wnd.show();
			},*/

			onTriggerClick : function(e) {
                var me = this;

                var modelName = "PSIModel_CurrencyEditor";
                Ext.define(modelName, {
                    extend : "Ext.data.Model",
                    fields : ["id", "currency_name"]
                });

				var store = Ext.create("Ext.data.Store", {
					model : modelName,
					autoLoad : false,
					data : []
				});
				var lookupGrid = Ext.create("Ext.grid.Panel", {
					cls : "PSI",
					columnLines : true,
					border : 0,
					store : store,
                    columns : [{
                        header : "币种",
                        dataIndex : "currency_name",
                        menuDisabled : true,
                        flex : 1

                    }]
				});

				me.lookupGrid = lookupGrid;
				me.lookupGrid.on("itemdblclick", me.onOK, me);

				var wnd = Ext.create("Ext.window.Window", {
                    title : "选择币种",
                    modal : true,
                    width : 400,
                    height : 300,
                    layout : "border",
					items : [{
						region : "center",
						xtype : "panel",
						layout : "fit",
						border : 0,
						items : [lookupGrid]
					}, {
                        xtype : "panel",
                        region : "south",
                        height : 40,
                        layout : "fit",
                        border : 0,
                        items : [{
                            xtype : "form",
                            layout : "form",
                            bodyPadding : 5,
                            items : [{
                                id : "__editCurrency",
                                xtype : "textfield",
                                fieldLabel : "币种",
                                labelWidth : 50,
                                labelAlign : "right",
                                labelSeparator : ""
                            }]
                        }]
                    }],
                    buttons : [{
                        text : "没有币种",
                        handler : me.onNone,
                        scope : me
                    }, {
                        text : "确定",
                        handler : me.onOK,
                        scope : me
                    }, {
                        text : "取消",
                        handler : function() {
                            wnd.close();
                        }
                    }]
				});

				wnd.on("close", function() {
					me.focus();
				});
				me.wnd = wnd;

				var editName = Ext.getCmp("__editCurrency");
				editName.on("change", function() {
					var store = me.lookupGrid.getStore();
					Ext.Ajax.request({
						url : PSI.Const.BASE_URL + "Home/Currency/allCurrency",
						params : {
							queryKey : editName.getValue()
						},
						method : "POST",
						callback : function(opt, success, response) {
							store.removeAll();
							if (success) {
								var data = Ext.JSON
									.decode(response.responseText);
								store.add(data);
								if (data.length > 0) {
									me.lookupGrid.getSelectionModel().select(0);
									editName.focus();
								}
							} else {
								PSI.MsgBox.showInfo("网络错误");
							}
						},
						scope : this
					});

				}, me);

				editName.on("specialkey", function(field, e) {
					if (e.getKey() == e.ENTER) {
						me.onOK();
					} else if (e.getKey() == e.UP) {
						var m = me.lookupGrid.getSelectionModel();
						var store = me.lookupGrid.getStore();
						var index = 0;
						for (var i = 0; i < store.getCount(); i++) {
							if (m.isSelected(i)) {
								index = i;
							}
						}
						index--;
						if (index < 0) {
							index = 0;
						}
						m.select(index);
						e.preventDefault();
						editName.focus();
					} else if (e.getKey() == e.DOWN) {
						var m = me.lookupGrid.getSelectionModel();
						var store = me.lookupGrid.getStore();
						var index = 0;
						for (var i = 0; i < store.getCount(); i++) {
							if (m.isSelected(i)) {
								index = i;
							}
						}
						index++;
						if (index > store.getCount() - 1) {
							index = store.getCount() - 1;
						}
						m.select(index);
						e.preventDefault();
						editName.focus();
					}
				}, me);

				me.wnd.on("show", function() {
					editName.focus();
					editName.fireEvent("change");
				}, me);
				wnd.show();
			},

			onOK : function() {
				var me = this;
				var lookupGrid = me.lookupGrid;
				var item = lookupGrid.getSelectionModel().getSelection();

				if (item === null || item.length !== 1) {
					PSI.MsgBox.showInfo("没有选择币种");

					return;
				}

				var data = item[0].data;
				var parentItem = me.getParentItem();
				if (parentItem) {
					parentItem.setParentBrand(data);
				}
				me.wnd.close();
				me.focus();
			},

			onNone : function() {
				var me = this;
				var parentItem = me.getParentItem();
				if (parentItem) {
					parentItem.setParentBrand({
								id : "",
							});

				}
				me.wnd.close();
				me.focus();
			},

			setIdValue : function(id) {
				this.__idValue = id;
			},

			getIdValue : function() {
				return this.__idValue;
			},

			clearIdValue : function() {
				this.setValue(null);
				this.__idValue = null;
			},
		});