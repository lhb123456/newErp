/**
 * 仓库 - 新增或编辑界面
 */
Ext.define("PSI.Warehouse.EditForm", {
    extend : "PSI.AFX.BaseDialogForm",

    config:{
        permission : null,
        idList : [],
        orgId:null
    },
    /**
     * 初始化组件
     */
    title : "选择公司仓库",
    width : 1200,
    height : 600,
    layout : "border",

    initComponent : function() {
        var me = this;

        Ext.apply(me, {
            padding : 5,
            items : [{
                region : "center",
                layout : "border",
                bodyStyle : "background-color:white",
                border : 0,
                items : [{
                    region : "north",
                    layout : "border",
                    height : "50%",
                    border : 1,
                    cls : "PSI",
                    margin : 5,
                    header : {
                        height : 30,
                        title : me
                            .formatGridHeaderTitle("所有可以选择的仓库")
                    },
                    items : [{
                        region : "north",
                        border : 0,
                        height : "100%",
                        layout : "fit",
                        bodyPadding : 5,
                        items : [me.getPermissionGrid()]
                    }]
                }, {
                    region : "center",
                    layout : "fit",
                    cls : "PSI",
                    border : 0,
                    items : [me.getSelectedGrid()]
                }]
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
                    me.close();
                },
                scope : me
            }],
            listeners : {
                show : me.onWndShow
            }
        });

        me.callParent(arguments);
    },

    onWndShow : function() {
        var me = this;
        var idList = me.getIdList();
        var store = me.getPermissionGrid().getStore();

        var el = me.getEl() || Ext.getBody();
        el.mask("数据加载中...");
        var r = {
            url : me.URL("Home/OrgWare/OrgWare"),
            callback : function(options, success, response) {
                store.removeAll();

                if (success) {
                    var data = me.decodeJSON(response.responseText);
                    store.add(data);

                    if (data.length > 0) {
                        me.getPermissionGrid().getSelectionModel().select(0);
                    }
                }

                el.unmask();
            }
        };

        me.ajax(r);
    },

    onOK : function() {
        var me = this;
        var grid = me.getSelectedGrid();

        if (grid.getStore().getCount() == 0) {
            PSI.MsgBox.showInfo("没有选择仓库");
            return;
        }

        var items = [];
        for (var i = 0; i < grid.getStore().getCount(); i++) {
            var item = grid.getStore().getAt(i);
            items.push({
                id : item.get("id"),
                name : item.get("name")
            });
        }
        var orgId=me.getOrgId()
        Ext.MessageBox.confirm("提示", "是否标记建账", function (btn) {
            var text = btn;
            Ext.getBody().mask("正在保存中...");
            Ext.Ajax.request({
                url: PSI.Const.BASE_URL + "Home/Warehouse/editWarehouse",
                method: "POST",
                params: {
                    items: Ext.JSON.encode(items),
                    orgId: orgId,
                    text: text,
                },
                callback: function (options, success, response) {
                    Ext.getBody().unmask();

                    if (success) {
                        var data = Ext.JSON.decode(response.responseText);
                        if (data.success) {
                            PSI.MsgBox.showInfo("成功保存数据", function () {
                                me.close();
                                me.getParentForm().freshCategoryGrid(orgId)
                            });
                        } else {
                            PSI.MsgBox.showInfo(data.msg);
                        }
                    }
                }
            });
        })
    },

    onSelectDataOrg : function() {
        var me = this;
        var form = Ext.create("PSI.Permission.SelectDataOrgForm", {
            parentForm : me
        });
        form.show();
    },

    setDataOrgList : function(fullNameList, dataOrgList) {
        Ext.getCmp("editDataOrg").setValue(fullNameList);
        Ext.getCmp("editDataOrgIdList").setValue(dataOrgList);
    },

    /**
     * 所有可以选择的权限的Grid
     */
    getPermissionGrid : function() {
        var me = this;
        if (me.__permissionGrid) {
            return me.__permissionGrid;
        }

        var modelName = "PSIPermission_SelectPermissionForm";
        Ext.define(modelName, {
            extend : "Ext.data.Model",
            fields : ["id", "code","name", "number","address"]
        });

        var store = Ext.create("Ext.data.Store", {
            model : modelName,
            autoLoad : false,
            data : []
        });

        me.__permissionGrid = Ext.create("Ext.grid.Panel", {
            cls : "PSI",
            store : store,
            columnLines : true,
            bbar : [{
                text : "全部添加",
                handler : me.addAllPermission,
                scope : me,
                iconCls : "PSI-button-add-detail"
            }],
            columns : [{
                header : "仓库编码",
                dataIndex : "code",
                width : 100,
                menuDisabled : true
            }, {
                header : "仓库名称",
                dataIndex : "name",
                width : 200,
                menuDisabled : true
            }, {
                header : "库号",
                dataIndex : "number",
                width : 200,
                menuDisabled : true
            }, {
                header : "仓库地址",
                dataIndex : "address",
                width : 300,
                menuDisabled : true
            }, {
                header : "",
                align : "center",
                menuDisabled : true,
                draggable : false,
                width : 30,
                xtype : "actioncolumn",
                items : [{
                    icon : PSI.Const.BASE_URL
                        + "Public/Images/icons/add.png",
                    handler : me.onAddPermission,
                    scope : me
                }]
            }]
        });

        return me.__permissionGrid;
    },

    onAddPermission : function(grid, row) {
        var item = grid.getStore().getAt(row);
        var me = this;
        var store = me.getSelectedGrid().getStore();
        if (store.findExact("id", item.get("id")) == -1) {
            store.add({
                id : item.get("id"),
                name : item.get("name")
            });
        }
    },

    /**
     * 最终用户选择权限的Grid
     */
    getSelectedGrid : function() {
        var me = this;
        if (me.__selectedGrid) {
            return me.__selectedGrid;
        }

        var modelName = "PSISelectedPermission_SelectPermissionForm";
        Ext.define(modelName, {
            extend : "Ext.data.Model",
            fields : ["id", "name"]
        });

        var store = Ext.create("Ext.data.Store", {
            model : modelName,
            autoLoad : false,
            data : []
        });

        me.__selectedGrid = Ext.create("Ext.grid.Panel", {
            header : {
                height : 30,
                title : me.formatGridHeaderTitle("已经选择的权限")
            },
            padding : 5,
            store : store,
            columns : [{
                header : "权限名称",
                dataIndex : "name",
                flex : 1,
                menuDisabled : true,
                draggable : false
            }, {
                header : "",
                align : "center",
                menuDisabled : true,
                draggable : false,
                width : 40,
                xtype : "actioncolumn",
                items : [{
                    icon : PSI.Const.BASE_URL
                        + "Public/Images/icons/delete.png",
                    handler : function(grid, row) {
                        grid.getStore().removeAt(row);
                    },
                    scope : me
                }]
            }]
        });

        return me.__selectedGrid;
    },

    addAllPermission : function() {
        var me = this;
        var store = me.getPermissionGrid().getStore();

        var selectStore = me.getSelectedGrid().getStore();

        var cnt = store.getCount();

        var d = [];

        for (var i = 0; i < cnt; i++) {
            var item = store.getAt(i);

            if (selectStore.findExact("id", item.get("id")) == -1) {
                d.push({
                    id : item.get("id"),
                    name : item.get("name")
                });
            }
        }

        selectStore.add(d);

        me.getSelectedGrid().focus();
    }
    /*initComponent : function() {
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
                    id : "selectOrg",
                    labelWidth : 60,
                    labelAlign : "right",
                    labelSeparator : "",
                    fieldLabel : "公司",
                    xtype : "psi_selectorgfield",
                    colspan : 2,
                    width : 300,
                    readOnly: me.permission  == "0",
                    disabled : me.permission == "0",
                    labelStyle:"Opacity:1.0",
                    fieldStyle:"Opacity:1.0",
                    allowBlank : false,
                    blankText : "没有输入公司",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    value :entity.data?entity.get("company_name") : entity.company_name,
                    listeners : {
                        specialkey : {
                            fn : me.onEditSpecialKey,
                            scope : me
                        }
                    },
                    callbackFunc : me.__selectOrg,
                    callbackObj:me
                },{
                    id:"company_id",
                    xtype : "hidden",
                    name : "company_id",
                    value :entity.data ? entity.get("company_id") :entity.company_id ,
                },{
                    id:"data_org",
                    xtype : "hidden",
                    name : "data_org",
                    value :entity.data ? entity.get("data_org") :entity.data_org ,
                },{
                    id : "PSI_Warehouse_EditForm_editName",
                    fieldLabel : "仓库名称",
                    allowBlank : false,
                    xtype : "psi_selectWarehouse",
                    width : 300,
                    blankText : "没有输入仓库名称",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    name : "name",
                    value : entity.data ? entity.get("name") :null ,
                    listeners : {
                        specialkey : {
                            fn : me.onEditNameSpecialKey,
                            scope : me
                        }
                    },
                    callbackFunc : me.__selectWare,
                    callbackObj:me
                },{
                    id:"warehouse_id",
                    xtype : "hidden",
                    name : "warehouse_id",
                    value :entity.data ? entity.get("warehouse_id") :entity.warehouse_id ,
                }],
                buttons : buttons
            }]
        });

        me.callParent(arguments);

        me.editForm = Ext.getCmp("PSI_Warehouse_EditForm_editForm");

        //me.editCode = Ext.getCmp("PSI_Warehouse_EditForm_editCode");
        me.editName = Ext.getCmp("PSI_Warehouse_EditForm_editName");
        me.selectOrg = Ext.getCmp("selectOrg");
        me.company_id = Ext.getCmp("company_id");
    },

    /!**
     * 保存
     *!/
    onOK : function(thenAdd) {
        var me = this;
        var f = me.editForm;
        var el = f.getEl();
        el.mask(PSI.Const.SAVING);
        var sf = {
            url : me.URL("/Home/Warehouse/editWarehouse"),
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
                    //me.editCode.focus();
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
        var me = this;

        Ext.get(window).on('beforeunload', me.onWindowBeforeUnload);

    },

    __selectOrg:function (data,obj) {
        var me=obj;
        if(data.id){
            Ext.getCmp("company_id").setValue(data.id);
            Ext.getCmp("data_org").setValue(data.data_org);
        }
    },
    __selectWare:function (data,obj) {
        var me=obj;
        if(data.id){
            Ext.getCmp("warehouse_id").setValue(data.id);
        }
    }*/
});