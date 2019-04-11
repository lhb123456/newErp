/**
 * 银行信息管理界面
 *
 */
Ext.define("PSI.Bank.MainForm", {
    extend : "PSI.AFX.BaseMainExForm",
    config:{
        permission:null
    },
    initComponent : function() {
        var me = this;

        Ext.define("PSICACategory", {
            extend : "Ext.data.Model",
            fields : ["id", "name"]
        });

        Ext.apply(me, {

            items : [{
                id : "panelQueryCmp",
                region : "north",
                height : 45,
                layout : "fit",
                border : 0,
                header : false,
                collapsible : true,
                collapseMode : "mini",
                layout : {
                    type : "table",
                    columns : 5
                },
                items : me.getQueryCmp()
            }, {
                region : "center",
                layout : "border",
                border : 0,
                items : [{
                    region : "center",
                    layout : "fit",
                    height : "40%",
                    border : 0,
                    items : [me.getRvGrid()]
                }, {
                    region : "south",
                    layout : "border",
                    border : 0,
                    split : true,
                    height : "50%",
                    items : [{
                        region : "east",
                        layout : "fit",
                        border : 0,
                        width : "100%",
                        split : true,
                        items : [me.getRvRecordGrid()]
                    }]
                }]
            }],
        });

        me.callParent(arguments);
        me.onQuery()
    },

    /**
     * 查询条件
     */
    getQueryCmp : function() {
        var me = this;
        return [{
            id : "status",
            xtype : "combo",
            queryMode : "local",
            editable : false,
            valueField : "id",
            labelWidth : 60,
            labelAlign : "right",
            labelSeparator : "",
            fieldLabel : "状态",
            margin : "5, 0, 0, 0",
            store : Ext.create("Ext.data.ArrayStore", {
                fields : ["id", "text"],
                data : [[0, "客户"], [1000, "供应商"],[2000, "组织机构"]]
            }),
            value :0
        },{
            id : "name",
            labelWidth : 80,
            labelAlign : "right",
            labelSeparator : "",
            fieldLabel : "往来单位名称",
            margin : "5, 0, 0, 0",
            xtype : "textfield"
        }, {
            id : "code",
            labelWidth : 80,
            labelAlign : "right",
            labelSeparator : "",
            fieldLabel : "往来单位编码",
            margin : "5, 0, 0, 0",
            xtype : "textfield"
        }, {
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

    getRvGrid : function() {
        var me = this;
        if (me.__rvGrid) {
            return me.__rvGrid;
        }

        Ext.define("PSIRv", {
            extend : "Ext.data.Model",
            fields : ["id","ca_code","ca_name","supplier","customer"]
        });

        var store = Ext.create("Ext.data.Store", {
            model : "PSIRv",
            pageSize : 20,
            proxy : {
                type : "ajax",
                actionMethods : {
                    read : "POST"
                },
                url : PSI.Const.BASE_URL + "Home/Bank/bankList",
                reader : {
                    root : 'dataList',
                    totalProperty : 'totalCount'
                }
            },
            autoLoad : false,
            data : []
        });
        store.on("beforeload", function() {
            Ext.apply(store.proxy.extraParams, {

                status : Ext.getCmp("status").getValue(),
                name : Ext.getCmp("name").getValue(),
                code : Ext.getCmp("code").getValue(),
            });
        });

        me.__rvGrid = Ext.create("Ext.grid.Panel", {
            cls : "PSI",
            viewConfig : {
                enableTextSelection : true
            },
            bbar : ["->", {
                xtype : "pagingtoolbar",
                border : 0,
                store : store
            }],
            columnLines : true,
            columns : [{
                xtype : "rownumberer",
                width :50,
            }, {
                header : "往来单位编码",
                dataIndex : "ca_code",
                width : 100,
                menuDisabled : true,
                sortable : false
            }, {
                header : "往来单位名称",
                dataIndex : "ca_name",
                menuDisabled : true,
                sortable : false,
                width : 200
            },{
                header : "是否是供应商",
                dataIndex : "supplier",
                width:100,
                menuDisabled : true,
                sortable : false,
                renderer : function(value,md,record) {
                    if (value == 0) {
                        return "<span style='color:red'>是</span>";
                    } else if (value == 1) {
                        return "否";
                    } else {
                        return "";
                    }
                }
            }, {
                header : "是否是客户",
                dataIndex : "customer",
                width:100,
                menuDisabled : true,
                sortable : false,
                renderer : function(value,md,record) {
                    if (value == 0) {
                        return "<span style='color:red'>是</span>";
                    } else if (value == 1) {
                        return "否";
                    } else {
                        return "";
                    }
                }
            }],
            store : store,
            listeners : {
                show :{
                    fn: me.onWinshow,
                    scope :me
                },
                select : {
                    fn : me.onRvGridSelect,
                    scope : me
                }
            }
        });

        return me.__rvGrid;
    },

    getRvParam : function() {
        var item = this.getRvGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            return null;
        }
        var rv = item[0];
        return rv.get("id");
    },
    /**
     * 清除查询条件
     */
    onClearQuery : function() {
        var me = this;

        Ext.getCmp("status").setValue(0);
        Ext.getCmp("name").setValue(null);
        Ext.getCmp("code").setValue(null);

        me.onQuery();
    },
    onRvGridSelect : function() {
        var me = this;

        this.getRvRecordGrid().getStore().removeAll();
        this.getRvRecordGrid().setTitle(me.formatGridHeaderTitle("银行信息"));

        this.getRvRecordGrid().getStore().loadPage(1);
    },
    onWinshow:function(){
        me.getRvGrid()
    },

    getRvRecordGrid : function() {
        var me = this;
        if (me.__rvRecordGrid) {
            return me.__rvRecordGrid;
        }

        Ext.define("PSIRvRecord", {
            extend : "Ext.data.Model",
            fields : ["id", "bank_name" ,"bank_account","status","memo"]
        });

        var store = Ext.create("Ext.data.Store", {
            model : "PSIRvRecord",
            pageSize : 20,
            proxy : {
                type : "ajax",
                actionMethods : {
                    read : "POST"
                },
                url : PSI.Const.BASE_URL + "Home/Bank/bankInfo",
                reader : {
                    root : 'dataList',
                    totalProperty : 'totalCount'
                }
            },
            autoLoad : false,
            data : []
        });

        store.on("beforeload", function() {
            Ext.apply(store.proxy.extraParams, {
                caId : me.getRvParam()
            });
        });

        me.__rvRecordGrid = Ext.create("Ext.grid.Panel", {
            cls : "PSI",
            viewConfig : {
                enableTextSelection : true
            },
            header : {
                height : 30,
                title : me.formatGridHeaderTitle("银行信息")
            },
            tbar : [{
                text : "添加银行信息",
                iconCls : "PSI-button-add",
                margin : "0 0 0 0",
                hidden : me.getPermission().add == "0",
                handler : me.onAddBank,
                scope : me
            }, "-",{
                text : "编辑银行信息",
                iconCls : "PSI-button-add",
                margin : "0 0 0 0",
                hidden : me.getPermission().add == "0",
                handler : me.onEditBank,
                scope : me
            }, "-",{
                text : "删除银行信息",
                iconCls : "PSI-button-delete",
                hidden : me.getPermission().delete == "0",
                handler : me.deleteBank,
                scope : me
            },  "-",{
                text : "设为默认账户",
                iconCls : "PSI-button-default",
                hidden : me.getPermission().default == "0",
                handler : me.default,
                scope : me
            }, "-", {
                text : "关闭",
                handler : function() {
                    me.closeWindow();
                }
            }],
            bbar : ["->", {
                xtype : "pagingtoolbar",
                border : 0,
                store : store
            }],
            columnLines : true,
            columns : [{
                xtype : "rownumberer",
                width :50,
            },{
                header : "id",
                dataIndex : "id",
                hidden :true,
                menuDisabled : true,
                sortable : false,
                width : 60
            },{
                header : "银行名称",
                dataIndex : "bank_name",
                menuDisabled : true,
                sortable : false,
                width : 200
            }, {
                header : "银行账号",
                dataIndex : "bank_account",
                menuDisabled : true,
                sortable : false,
                width : 250
            },{
                header : "是否是默认账号",
                dataIndex : "status",
                menuDisabled : true,
                sortable : false,
                width : 100,
                renderer : function(value) {
                    if (value == 0) {
                        return "<span style='color:red'>是</span>";
                    }  else if (value == 1) {
                        return "<span style='color:green'>否</span>";
                    }else {
                        return "";
                    }
                }
            },{
                header : "备注",
                dataIndex : "memo",
                menuDisabled : true,
                sortable : false,
                width : 250
            },],
            store : store
        });

        return me.__rvRecordGrid;
    },
    onQuery : function() {
        var me = this;
        me.getRvRecordGrid().getStore().removeAll();
        me.getRvRecordGrid().setTitle(me.formatGridHeaderTitle("银行信息"));

        me.getRvGrid().getStore().loadPage(1);
    },

    onAddBank : function() {
        var me = this;
        var item = me.getRvGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择往来单位");
            return;
        }


        var contract = item[0] ;

        var form = Ext.create("PSI.Bank.EditForm", {
            parentForm : me,
            contract : contract
        })
        form.show();
    },

    onEditBank : function() {
        var me = this;
        var item = me.getRvRecordGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择银行信息");
            return;
        }
        var bank = item[0] ;
        var item = me.getRvGrid().getSelectionModel().getSelection();

        var contract = item[0] ;

        var form = Ext.create("PSI.Bank.EditForm", {
            parentForm : me,
            contract : contract,
            bank:bank
        })
        form.show();
    },

    invoiceCorrespondence :function(){
        var me =this;
        var form = Ext.create("PSI.Invoice.InvoiceCorres", {
            parentForm : me,
        })
        form.show();
    },
    deleteBank : function(){
        var me =this;
        var item = me.getRvRecordGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择要删除的银行信息");
            return;
        }
        var items= item[0]
        PSI.MsgBox.confirm("确认删除此银行信息",function () {
            Ext.Ajax.request({
                url : PSI.Const.BASE_URL + "Home/Bank/deleteBank",
                method : "POST",
                params : {
                    id:items.get("id"),
                },
                callback : function(options, success, response) {
                    if (success) {
                        var data = Ext.JSON.decode(response.responseText);
                        if (data.success) {
                            PSI.MsgBox.showInfo("删除成功", function() {
                                me.getRvRecordGrid().getStore().loadPage(1);
                            });
                        } else {
                            PSI.MsgBox.showInfo("网络错误");
                        }
                    }
                }

            });
        })
    },

    default : function(){
            var me =this;
            var item = me.getRvRecordGrid().getSelectionModel().getSelection();
            if (item == null || item.length != 1) {
                PSI.MsgBox.showInfo("请选择要设置的银行信息");
                return;
            }
            var items= item[0]
            PSI.MsgBox.confirm("是否设置此信息为默认信息",function () {
                Ext.Ajax.request({
                    url : PSI.Const.BASE_URL + "Home/Bank/isDefault",
                    method : "POST",
                    params : {
                        id:items.get("id"),
                    },
                    callback : function(options, success, response) {
                        if (success) {
                            var data = Ext.JSON.decode(response.responseText);
                            if (data.success) {
                                PSI.MsgBox.showInfo("设置成功", function() {
                                    me.getRvRecordGrid().getStore().loadPage(1);
                                });
                            } else {
                                PSI.MsgBox.showInfo("网络错误");
                            }
                        }
                    }

                });
            })
        },


});