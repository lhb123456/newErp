/**
 * 新建或编辑现货价格
 */
Ext.define("PSI.TaxCode.EditForm", {
    extend : "PSI.AFX.BaseDialogForm",
        config:{

        },
    /**
     * 初始化组件
     */
    initComponent : function() {
        var me = this;
        var entity = me.getEntity();

        var modelName = "PSIGoodsUnit";
        Ext.define(modelName, {
            extend : "Ext.data.Model",
            fields : ["id", "name"]
        });

        var unitStore = Ext.create("Ext.data.Store", {
            model : modelName,
            autoLoad : false,
            data : []
        });
        me.unitStore = unitStore;

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
            text : entity == null ? "关闭" : "取消",
            handler : function() {
                me.close();
            },
            scope : me
        });

        var selectedCategory = null;
        var defaultCategoryId = null;

        var t = entity == null ? "新增税务编码" : "编辑税务编码";
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
            width : 460,
            height : 470,
            layout : "border",
            items : [{
                region : "north",
                border : 0,
                height : 90,
                html : logoHtml
            }, {
                region : "center",
                border : 0,
                id : "PSI_Goods_GoodsEditForm_editForm",
                xtype : "form",
                layout : {
                    type : "table",
                    columns : 2
                },
                height : "100%",
                bodyPadding : 5,
                defaultType : 'textfield',
                fieldDefaults : {
                    labelWidth : 70,
                    labelAlign : "right",
                    labelSeparator : "",
                    msgTarget : 'side'
                },
                items : [{
                    xtype : "hidden",
                    name : "id",
                    value : entity == null ? null : entity
                        .get("id")
                }, {
                    id : "code_one",
                    fieldLabel : "篇",
                    allowBlank : false,
                    blankText : "没有输入篇",
                    value : entity == null ? null : entity
                        .get("code_one"),
                    name : "code_one",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    listeners : {
                        blur :function() {
                            me.change()
                        },
                        specialkey : {
                            fn : me.onEditSpecialKey,
                            scope : me
                        }
                    }
                }, {
                    id : "code_two",
                    fieldLabel : "类",
                    width : 205,
                    allowBlank : false,
                    blankText : "没有输入类",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    name : "code_two",
                    value : entity == null ? null : entity
                        .get("code_two"),
                    listeners : {
                        blur :function() {
                            me.change()
                        },
                        specialkey : {
                            fn : me.onEditSpecialKey,
                            scope : me
                        }
                    }
                },{
                    id : "code_three",
                    fieldLabel : "章",
                    allowBlank : false,
                    blankText : "没有输入章",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    name : "code_three",
                    value : entity == null ? null : entity
                        .get("code_three"),
                    listeners : {
                        blur :function() {
                            me.change()
                        },
                        specialkey : {
                            fn : me.onEditSpecialKey,
                            scope : me
                        }
                    }
                }, {
                    id : "code_four",
                    fieldLabel : "节",
                    width : 205,
                    allowBlank : false,
                    blankText : "没有输入节",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    name : "code_four",
                    value : entity == null ? null : entity
                        .get("code_four"),
                    listeners : {
                        blur :function() {
                            me.change()
                        },
                        specialkey : {
                            fn : me.onEditSpecialKey,
                            scope : me
                        }
                    }
                },{
                    id : "code_five",
                    fieldLabel : "条",
                    allowBlank : false,
                    blankText : "没有输入条",
                    name : "code_five",
                    value : entity == null ? null : entity
                        .get("code_five"),
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    listeners : {
                        blur :function() {
                            me.change()
                        },
                        specialkey : {
                            fn : me.onEditSpecialKey,
                            scope : me
                        }
                    }
                }, {
                    id : "code_six",
                    fieldLabel : "款",
                    width : 205,
                    allowBlank : false,
                    blankText : "没有输入款",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    name : "code_six",
                    value : entity == null ? null : entity
                        .get("code_six"),
                    listeners : {
                        blur :function() {
                            me.change()
                        },
                        specialkey : {
                            fn : me.onEditSpecialKey,
                            scope : me
                        }
                    }
                }, {
                    id : "code_seven",
                    fieldLabel : "项",
                    allowBlank : false,
                    blankText : "没有输入项",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    name : "code_seven",
                    value : entity == null ? null : entity
                        .get("code_seven"),
                    listeners : {
                        blur :function() {
                            me.change()
                        },
                        specialkey : {
                            fn : me.onEditSpecialKey,
                            scope : me
                        }
                    }
                }, {
                    id : "code_eight",
                    fieldLabel : "目",
                    width : 205,
                    allowBlank : false,
                    blankText : "没有输入目",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    name : "code_eight",
                    value : entity == null ? null : entity
                        .get("code_eight"),
                    listeners : {
                        blur :function() {
                            me.change()
                        },
                        specialkey : {
                            fn : me.onEditSpecialKey,
                            scope : me
                        }
                    }
                }, {
                    id : "code_nine",
                    fieldLabel : "子目",
                    allowBlank : false,
                    blankText : "没有输入子目",
                    name : "code_nine",
                    value : entity == null ? null : entity
                        .get("code_nine"),
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    listeners : {
                        blur :function() {
                            me.change()
                        },
                        specialkey : {
                            fn : me.onEditSpecialKey,
                            scope : me
                        }
                    }
                }, {
                    id : "code_ten",
                    fieldLabel : "细目",
                    width : 205,
                    allowBlank : false,
                    blankText : "没有输入细目",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    name : "code_ten",
                    value : entity == null ? null : entity
                        .get("code_ten"),
                    listeners : {
                        blur :function() {
                            me.change()
                        },
                        specialkey : {
                            fn : me.onEditSpecialKey,
                            scope : me
                        }
                    }
                },{
                    xtype : "hidden",
                    name : "merge_code_history",
                    value : entity == null ? null : entity
                        .get("merge_code")
                }, {
                    fieldLabel : "合并编码",
                    name : "merge_code",
                    id : "mergeCode",
                    allowBlank : false,
                    blankText : "没有输入合并编码",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    value : entity == null ? null : entity
                        .get("merge_code"),
                    listeners : {
                        specialkey : {
                            fn : me.onLastEditSpecialKey,
                            scope : me
                        }
                    },
                    colspan : 2,
                    width : 430
                }, {
                    id : "goods_name",
                    fieldLabel : "货物和劳务名称",
                    blankText : "没有输入计量单位",
                    labelWidth : 100,
                    colspan : 2,
                    width : 430,
                    name : "goods_name",
                    value : entity == null ? null : entity
                        .get("name"),
                    listeners : {
                        specialkey : {
                            fn : me.onEditSpecialKey,
                            scope : me
                        }
                    }
                }, {
                    id : "goods_cate_name",
                    fieldLabel : "商品和服务分类简称",
                    labelWidth : 120,
                    name : "goods_cate_name",
                    value : entity == null ? null : entity
                        .get("cate_name"),
                    colspan : 2,
                    width : 430,
                    listeners : {
                        specialkey : {
                            fn : me.onEditSpecialKey,
                            scope : me
                        }
                    }
                },{
                    fieldLabel : "说明",
                    name : "memo",
                    id : "memo",
                    value : entity == null ? null : entity
                        .get("memo"),
                    listeners : {
                        specialkey : {
                            fn : me.onLastEditSpecialKey,
                            scope : me
                        }
                    },
                    colspan : 2,
                    width : 430
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

        me.editForm = Ext.getCmp("PSI_Goods_GoodsEditForm_editForm");
    },

    onWindowBeforeUnload : function(e) {
        return (window.event.returnValue = e.returnValue = '确认离开当前页面？');
    },

    onWndShow : function() {
        var me = this;

        Ext.get(window).on('beforeunload', me.onWindowBeforeUnload);
        var el = me.getEl();
        var unitStore = me.unitStore;
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url : me.URL("/Home/Goods/goodsInfo"),
            params : {
                id : me.adding ? null : me.getEntity().get("id"),
            },
            method : "POST",
            callback : function(options, success, response) {
                unitStore.removeAll();

                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    if (data.units) {
                        unitStore.add(data.units);
                    }

                    if (!me.adding) {
                        // 编辑商品信息
                    } else {

                    }
                }

                el.unmask();
            }
        });
    },

    onOK : function(thenAdd) {
        var me = this;

        var f = me.editForm;
        var el = f.getEl();
        el.mask(PSI.Const.SAVING);
        f.submit({
            url : me.URL("/Home/TaxCode/addTaxCode"),
            method : "POST",
            success : function(form, action) {
                el.unmask();
                me.__lastId = action.result.id;
                if (me.getParentForm()) {
                    me.getParentForm().__lastId = me.__lastId;
                }

                PSI.MsgBox.tip("数据保存成功");
                me.focus();
                if (thenAdd) {
                    me.clearEdit();
                } else {
                    me.close();
                    me.getParentForm().refreshMainGrid(action.result.id);
                }
            },
            failure : function(form, action) {
                el.unmask();
                PSI.MsgBox.showInfo(action.result.msg);
            }
        });
    },

    onEditSpecialKey : function(field, e) {

    },

    onLastEditSpecialKey : function(field, e) {
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

        var editors = [me.editCode, me.editName, me.editSpec, me.editSalePrice,
            me.editPurchasePrice, me.editBarCode, me.editMemo];
        for (var i = 0; i < editors.length; i++) {
            var edit = editors[i];
            edit.setValue(null);
            edit.clearInvalid();
        }
    },

    onWndClose : function() {
        var me = this;

        Ext.get(window).un('beforeunload', me.onWindowBeforeUnload);
    },
    change :function (){
        var code_one=Ext.getCmp("code_one").getValue()
        var code_two=Ext.getCmp("code_two").getValue()
        var code_three=Ext.getCmp("code_three").getValue()
        var code_four=Ext.getCmp("code_four").getValue()
        var code_five=Ext.getCmp("code_five").getValue()
        var code_six=Ext.getCmp("code_six").getValue()
        var code_seven=Ext.getCmp("code_seven").getValue()
        var code_eight=Ext.getCmp("code_eight").getValue()
        var code_nine=Ext.getCmp("code_nine").getValue()
        var code_ten=Ext.getCmp("code_ten").getValue()
        var merge_code=code_one+code_two+code_three+code_four+code_five+code_six+code_seven+code_eight+
            code_nine+code_ten
        Ext.getCmp("mergeCode").setValue(merge_code)
    },


});