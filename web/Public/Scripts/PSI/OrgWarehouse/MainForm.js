/**
 * 仓库 - 主界面
 */
Ext.define("PSI.OrgWarehouse.MainForm", {
    extend : "PSI.AFX.BaseOneGridMainForm",

    config : {
        pAdd : null,
        pEdit : null,
        pDelete : null,
        pEditDataOrg : null,
        pInitInv : null,
        selectOrg:null,
        companyId:null,
        companyName:null
    },

    /**
     * 重载父类方法
     */
    afxGetToolbarCmp : function() {
        var me = this;

        var result = [{
            text : "新增仓库",
            disabled : me.getPAdd() == "0",
            handler : me.onAddWarehouse,
            scope : me
        }, {
            text : "编辑仓库",
            disabled : me.getPEdit() == "0",
            handler : me.onEditWarehouse,
            scope : me
        }, {
            text : "删除仓库",
            disabled : me.getPDelete() == "0",
            handler : me.onDeleteWarehouse,
            scope : me
        }];

        if (me.getPInitInv() == "1") {
            result.push("-", {
                text : "打开库存建账模块",
                handler : function() {
                    window.open(me
                        .URL("Home/MainMenu/navigateTo/fid/2000"));
                }
            });
        }

        result.push( "-", {
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
        return "Home/OrgWare/OrgWare";
    },

    /**
     * 重载父类方法
     */
    afxGetMainGrid : function() {
        var me = this;
        if (me.__mainGrid) {
            return me.__mainGrid;
        }

        var modelName = "PSI_Warehouse_MainForm_PSIWarehouse";
        Ext.define(modelName, {
            extend : "Ext.data.Model",
            fields : ["id", "code", "name","number", "address"]
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
                header : "仓库编码",
                dataIndex : "code",
                menuDisabled : true,
                sortable : false,
                width : 100
            }, {
                header : "仓库名称",
                dataIndex : "name",
                menuDisabled : true,
                sortable : false,
                width : 300
            }, {
                header : "仓库库号",
                dataIndex : "number",
                menuDisabled : true,
                sortable : false,
                width : 300
            },{
                header : "仓库地址",
                dataIndex : "address",
                menuDisabled : true,
                sortable : false,
                width : 300
            },],
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
     * 查询条件
     */
    getQueryCmp : function() {
        var me = this;
        return [{
            id : "code",
            labelWidth : 60,
            labelAlign : "right",
            labelSeparator : "",
            fieldLabel : "仓库编码",
            colspan:1,
            margin : "5, 0, 0, 0",
            xtype : "textfield"
        },{
            id : "name",
            labelWidth : 60,
            labelAlign : "right",
            labelSeparator : "",
            fieldLabel : "仓库名称",
            colspan:1,
            margin : "5, 0, 0, 0",
            xtype : "textfield"
        },{
            id : "address",
            labelWidth : 60,
            labelAlign : "right",
            labelSeparator : "",
            fieldLabel : "仓库地址",
            colspan:1,
            margin : "5, 0, 0, 0",
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
     * 重载父类方法
     */
    afxGetRefreshGridParams :function(){
        var me = this;

        var result = {

        };

        var code = Ext.getCmp("code").getValue();
        if (code) {
            result.code = code;
        }

        var name = Ext.getCmp("name").getValue();
        if (name) {
            result.name = name;
        }
        var address = Ext.getCmp("address").getValue();
        if (address) {
            result.address = address;
        }
        return result
    },
    //清除查询条件
    onClearQuery:function(){
        var me = this
        Ext.getCmp("code").setValue()
        Ext.getCmp("name").setValue()
        Ext.getCmp("address").setValue()
        me.onQuery()
    },
    onQuery : function() {
        var me = this;
        me.afxRefreshGrid();
    },
    /**
     * 新增仓库
     */
    onAddWarehouse : function() {
        var me = this;
        var userCompany={
            "company_id":me.getCompanyId(),
            "company_name":me.getCompanyName()
        };
        var form = Ext.create("PSI.OrgWarehouse.EditForm", {
            parentForm : me,
            entity:userCompany,
            permission:me.getSelectOrg()
        });
        form.show();
    },
    /**
     * 编辑仓库
     */
    onEditWarehouse : function() {
        var me = this;

        if (me.getPEdit() == "0") {
            return;
        }
        var item = me.getMainGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            me.showInfo("请选择要编辑的仓库");
            return;
        }
        var warehouse = item[0];
        var form = Ext.create("PSI.OrgWarehouse.EditForm", {
            parentForm : me,
            code : false,
            entity : warehouse,
            permission:me.getSelectOrg()
        });

        form.show();
    },

    /**
     * 删除仓库
     */
    onDeleteWarehouse : function() {
        var me = this;
        var item = me.getMainGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            me.showInfo("请选择要删除的仓库");
            return;
        }

        var warehouse = item[0];
        console.log(warehouse)
        var info = "请确认是否删除仓库 <span style='color:red'>" + warehouse.get("name")
            + "</span> ?";
        var preIndex = me.getPreIndexInMainGrid(warehouse.get("id"));
        var funcConfirm = function() {
            var el = Ext.getBody();
            el.mask(PSI.Const.LOADING);
            var r = {
                url : me.URL("Home/OrgWare/deleteOrgWarehouse"),
                params : {
                    id : warehouse.get("id")
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
    },

});