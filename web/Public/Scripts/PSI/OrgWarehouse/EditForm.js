/**
 * 仓库 - 新增或编辑界面
 */
Ext.define("PSI.OrgWarehouse.EditForm", {
    extend : "PSI.AFX.BaseDialogForm",

    config:{
        permission : null,
        code : true,
    },
    /**
     * 初始化组件
     */
    initComponent : function() {
        var me = this;

        var entity = me.getEntity();

        me.adding = entity.data== null;

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
            text : entity.data== null ? "关闭" : "取消",
            handler : function() {
                me.close();
            },
            scope : me
        };
        buttons.push(btn);

        var t = entity.data== null ? "新增仓库" : "编辑仓库";
        var f = entity.data== null
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
            width : 400,
            height : 300,
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
                height : 90,
                border : 0,
                html : logoHtml
            }, {
                region : "center",
                border : 0,
                id : "PSI_Warehouse_EditForm_editForm",
                xtype : "form",
                layout : {
                    type : "table",
                    columns : 1
                },
                height : "100%",
                bodyPadding : 5,
                defaultType : 'textfield',
                fieldDefaults : {
                    labelWidth : 60,
                    labelAlign : "right",
                    labelSeparator : "",
                    msgTarget : 'side',
                    width : 370,
                    margin : "5"
                },
                items : [{
                    xtype : "hidden",
                    name : "id",
                    value : entity .data ? entity.get("id"): null
                },{
                    id : "PSI_Warehouse_EditForm_hisCode",
                    fieldLabel : "仓库编码",
                    blankText : "没有输入仓库编码",
                    hidden:true,
                    name : "hisCode",
                    value : entity.data? entity.get("code") :null ,
                    listeners : {
                        specialkey : {
                            fn : me.onEditCodeSpecialKey,
                            scope : me
                        }
                    }
                }, {
                    id : "PSI_Warehouse_EditForm_editCode",
                    fieldLabel : "仓库编码",
                    allowBlank : false,
                    blankText : "没有输入仓库编码",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    name : "code",
                    value : entity.data? entity.get("code") :null ,
                    listeners : {
                        specialkey : {
                            fn : me.onEditCodeSpecialKey,
                            scope : me
                        }
                    }
                }, {
                    id : "PSI_Warehouse_EditForm_editName",
                    fieldLabel : "仓库名称",
                    allowBlank : false,
                    blankText : "没有输入仓库名称",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    name : "name",
                    value : entity.data ? entity.get("name") :null ,
                    listeners : {
                        specialkey : {
                            fn : me.onEditNameSpecialKey,
                            scope : me
                        }
                    }
                },{
                    id : "PSI_Warehouse_EditForm_address",
                    fieldLabel : "仓库地址",
                    allowBlank : false,
                    blankText : "没有输入仓库编码",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    name : "address",
                    value : entity.data? entity.get("address") :null ,
                    listeners : {
                        specialkey : {
                            fn : me.onEditCodeSpecialKey,
                            scope : me
                        }
                    }
                }],
                buttons : buttons
            }]
        });

        me.callParent(arguments);

        me.editForm = Ext.getCmp("PSI_Warehouse_EditForm_editForm");

        me.editCode = Ext.getCmp("PSI_Warehouse_EditForm_editCode");
        me.hisCode = Ext.getCmp("PSI_Warehouse_EditForm_hisCode");
        me.editName = Ext.getCmp("PSI_Warehouse_EditForm_editName");
        me.selectOrg = Ext.getCmp("selectOrg");
        me.editAddress = Ext.getCmp("PSI_Warehouse_EditForm_address");
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
            url : me.URL("/Home/OrgWare/editOrgWarehouse"),
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
                    me.editCode.focus();
                });
            }
        };
        f.submit(sf);
    },

    onEditCodeSpecialKey : function(field, e) {
        var me = this;

        if (e.getKey() == e.ENTER) {
            var editName = me.editName;
            editName.focus();
            editName.setValue(editName.getValue());
        }
    },

    onEditNameSpecialKey : function(field, e) {
        var me = this;

        if (e.getKey() == e.ENTER) {
            var f = me.editForm;
            if (f.getForm().isValid()) {
                me.onOK(me.adding);
            }
        }
    },

    onEditSpecialKey : function(field, e) {
        if (e.getKey() === e.ENTER) {
            var me = this;
            var id = field.getId();
            for (var i = 0; i < me.__editorList.length; i++) {
                var editorId = me.__editorList[i];
                if (id === editorId) {
                    var edit = Ext.getCmp(me.__editorList[i + 1]);
                    edit.focus();
                    edit.setValue(edit.getValue());
                }
            }
        }
    },

    clearEdit : function() {
        var me = this;
        me.editCode.focus();

        var editors = [me.editCode, me.editName];
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

        if (me.__lastId) {
            if (me.getParentForm()) {
                me.getParentForm().freshGrid(me.__lastId);
            }
        }
    },

    onWndShow : function() {
        Ext.get(window).on('beforeunload', this.onWindowBeforeUnload);
        var me = this;
        if(me.getCode()){
            var el = me.getEl() || Ext.getBody();
            el.mask(PSI.Const.LOADING);
            Ext.Ajax.request({
                url : PSI.Const.BASE_URL + "Home/OrgWare/code",
                params : {
                },
                method : "POST",
                callback : function(options, success, response) {
                    el.unmask();

                    if (success) {
                        var data = Ext.JSON.decode(response.responseText);
                        Ext.getCmp("PSI_Warehouse_EditForm_editCode").setValue(data.code)
                    }
                }
            });
        }

    },

});