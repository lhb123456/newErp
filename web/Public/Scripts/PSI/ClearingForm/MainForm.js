/**
 * 结算方式 - 主界面
 */
Ext.define("PSI.ClearingForm.MainForm", {
    extend : "PSI.AFX.BaseOneGridMainForm",

    config : {
        pAdd : null,
        pEdit : null,
        pDelete : null,
        pEditDataOrg : null,
        pInitInv : null
    },

    /**
     * 重载父类方法
     */
    afxGetToolbarCmp : function() {
        var me = this;

        var result = [{
            text : "新增结算方式",
            handler : me.onAddWarehouse,
            scope : me
        }, {
            text : "编辑结算方式",
            handler : me.onEditWarehouse,
            scope : me
        }, {
            text : "删除结算方式",
            handler : me.onDeleteWarehouse,
            scope : me
        }];
        result.push("-", {
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
        return "Home/ClearingForm/ClearingList";
    },

    /**
     * 重载父类方法
     */
    afxGetMainGrid : function() {
        var me = this;
        if (me.__mainGrid) {
            return me.__mainGrid;
        }

        var modelName = "PSI_Currency_MainForm_PSICurrency";
        Ext.define(modelName, {
            extend : "Ext.data.Model",
            fields : ["id", "clearing", "memo", "status", "dataOrg"]
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
                header : "结算方式",
                dataIndex : "clearing",
                menuDisabled : true,
                sortable : false,
                width : 100
            }, {
                header : "状态",
                dataIndex : "status",
                width:100,
                menuDisabled : true,
                sortable : false,
                renderer : function(value) {
                    if (value == 0) {
                        return "启用";
                    } else if (value == 1) {
                        return "<span style='color:red'>禁用</span>";
                    } else {
                        return "";
                    }
                }
            },{
                header : "备注",
                dataIndex : "memo",
                menuDisabled : true,
                sortable : false,
                width : 300
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
     * 新增结算方式
     */
    onAddWarehouse : function() {
        var me = this;

        var form = Ext.create("PSI.ClearingForm.EditForm", {
            parentForm : me
        });

        form.show();
    },

    /**
     * 编辑结算方式
     */
    onEditWarehouse : function() {
        var me = this;

        if (me.getPEdit() == "0") {
            return;
        }

        var item = me.getMainGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            me.showInfo("请选择要编辑的结算方式");
            return;
        }

        var warehouse = item[0];

        var form = Ext.create("PSI.ClearingForm.EditForm", {
            parentForm : me,
            entity : warehouse
        });

        form.show();
    },

    /**
     * 删除结算方式
     */
    onDeleteWarehouse : function() {
        var me = this;
        var item = me.getMainGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            me.showInfo("请选择要删除的结算方式");
            return;
        }
        var currency = item[0];
        var info = "请确认是否删除结算方式 <span style='color:red'>" + currency.get("clearing")
            + "</span> ?";

        var preIndex = me.getPreIndexInMainGrid(currency.get("id"));
        var funcConfirm = function() {
            var el = Ext.getBody();
            el.mask(PSI.Const.LOADING);
            var r = {
                url : me.URL("Home/ClearingForm/del"),
                params : {
                    id : currency.get("id")
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
    }
});