/**
 * 仓库 - 主界面
 */
Ext.define("PSI.Warehouse.MainForm", {
    extend : "PSI.AFX.BaseMainExForm",
    border : 0,

    config : {
        pAdd : null,
        pEdit : null,
        pDelete : null,
        pEditDataOrg : null,
        pInitInv : null
    },

    /**
     * 初始化组件
     */
    initComponent : function() {
        var me = this;

        Ext.apply(me, {
            tbar : me.getToolbarCmp(),
            layout : "border",
            items : [{
                id : "panelQueryCmp",
                region : "north",
                height : 45,
                border : 0,
                collapsible : true,
                collapseMode : "mini",
                header : false,
                layout : {
                    type : "table",
                    columns : 4
                },
                items : me.getQueryCmp()
            }, {
                region : "center",
                layout : "border",
                border : 0,
                items : [{
                    region : "center",
                    xtype : "panel",
                    layout : "fit",
                    border : 0,
                    items : [me.getMainGrid()]
                }, {
                    id : "panelCategory",
                    xtype : "panel",
                    region : "west",
                    layout : "fit",
                    width : 400,
                    split : true,
                    collapsible : true,
                    header : false,
                    border : 0,
                    items : [me.getOrgGrid()]
                }]
            }]
        });

        me.callParent(arguments);

        me.categoryGrid = me.getOrgGrid();
        me.customerGrid = me.getMainGrid();

        me.freshCategoryGrid();
    },

    getToolbarCmp : function() {
        var me = this;

        return [{
            text : "新增公司仓库",
            disabled : me.getPAdd() == "0",
            handler : me.onAddWarehouse,
            scope : me
        },{
            text : "删除公司仓库",
            disabled : me.getPDelete() == "0",
            handler : me.onDeleteWarehouse,
            scope : me
        },"-", {
            text : "打开库存建账模块",
            handler : function() {
                window.open(me
                    .URL("Home/MainMenu/navigateTo/fid/2000"));
            }
        }];
    },
    /*新增公司仓库*/
	onAddWarehouse : function() {
		var me = this;
        var item = me.getOrgGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            me.showInfo("请选择公司");
            return;
        }
        var data = item[0];
		var form = Ext.create("PSI.Warehouse.EditForm", {
			parentForm : me,
            orgId:data.get("id")
		});

		form.show();
	},
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
            xtype : "container",
            items : [{
                xtype : "button",
                text : "查询",
                width : 100,
                height : 26,
                margin : "5, 0, 0, 20",
                handler : me.onQuery,
                scope : me
            }, {
                xtype : "button",
                text : "清空查询条件",
                width : 100,
                height : 26,
                margin : "5, 0, 0, 15",
                handler : me.onClearQuery,
                scope : me
            }, {
                xtype : "button",
                text : "隐藏查询条件栏",
                width : 130,
                height : 26,
                iconCls : "PSI-button-hide",
                margin : "5 0 0 10",
                handler : function() {
                    Ext.getCmp("panelQueryCmp").collapse();
                },
                scope : me
            }]
        }];
    },

    getOrgGrid : function() {
        var me = this;

        if (me.__categoryGrid) {
            return me.__categoryGrid;
        }

        var modelName = "PSICustomerCategory";

        Ext.define(modelName, {
            extend : "Ext.data.Model",
            fields : ["id", "fullName", {
                name : "cnt",
                type : "int"
            }, "count_limit","count"]
        });

        me.__categoryGrid = Ext.create("Ext.grid.Panel", {
            cls : "PSI",
            viewConfig : {
                enableTextSelection : true
            },
            title : "公司",
            tools : [{
                type : "close",
                handler : function() {
                    Ext.getCmp("panelCategory").collapse();
                }
            }],
            features : [{
                ftype : "summary"
            }],
            columnLines : true,
            columns : [{
                xtype : "rownumberer",
                width : 40
            }, {
                header : "公司名称",
                dataIndex : "fullName",
                width : 260,
                menuDisabled : true,
                sortable : false
            },{
                header : "仓库数量",
                dataIndex : "count",
                width : 80,
                menuDisabled : true,
                sortable : false,
                //summaryType : "sum",
                align : "right"
            }],
            store : Ext.create("Ext.data.Store", {
                model : modelName,
                autoLoad : false,
                data : []
            }),
            listeners : {
                select : {
                    fn : me.onCategoryGridSelect,
                    scope : me
                },
                itemdblclick : {
                    fn : me.onEditCategory,
                    scope : me
                }
            }
        });

        return me.__categoryGrid;
    },

    getMainGrid : function() {
        var me = this;
        if (me.__mainGrid) {
            return me.__mainGrid;
        }

        var modelName = "PSICustomer";

        Ext.define(modelName, {
            extend : "Ext.data.Model",
            fields : ["id", "code", "name","address","number"]
        });

        var store = Ext.create("Ext.data.Store", {
            autoLoad : false,
            model : modelName,
            data : [],
            pageSize : 20,
            proxy : {
                type : "ajax",
                actionMethods : {
                    read : "POST"
                },
                url : me.URL("Home/OrgWare/wareList"),
                reader : {
                    root : 'customerList',
                    totalProperty : 'totalCount'
                }
            },
            listeners : {
                beforeload : {
                    fn : function() {
                        store.proxy.extraParams = me.getQueryParam();
                    },
                    scope : me
                },
                load : {
                    fn : function(e, records, successful) {
                        if (successful) {
                            me.refreshCategoryCount();
                            me.gotoCustomerGridRecord(me.__lastId);
                        }
                    },
                    scope : me
                }
            }
        });

        me.__mainGrid = Ext.create("Ext.grid.Panel", {
            cls : "PSI",
            viewConfig : {
                enableTextSelection : true
            },
            title : "客户列表",
            columnLines : true,
            columns : [Ext.create("Ext.grid.RowNumberer", {
                text : "序号",
                width : 40
            }), {
                header : "仓库编码",
                dataIndex : "code",
                menuDisabled : true,
                sortable : false
            }, {
                header : "仓库名称",
                dataIndex : "name",
                menuDisabled : true,
                sortable : false,
                width : 200
            }, {
                header : "仓库库号",
                dataIndex : "number",
                menuDisabled : true,
                sortable : false,
                width : 80
            },{
                header : "仓库地址",
                dataIndex : "address",
                menuDisabled : true,
                sortable : false,
                width : 300
            }],
            store : store,
            bbar : ["->", {
                id : "pagingToolbar",
                border : 0,
                xtype : "pagingtoolbar",
                store : store
            }, "-", {
                xtype : "displayfield",
                value : "每页显示"
            }, {
                id : "comboCountPerPage",
                xtype : "combobox",
                editable : false,
                width : 60,
                store : Ext.create("Ext.data.ArrayStore", {
                    fields : ["text"],
                    data : [["20"], ["50"], ["100"],
                        ["300"], ["1000"]]
                }),
                value : 20,
                listeners : {
                    change : {
                        fn : function() {
                            store.pageSize = Ext
                                .getCmp("comboCountPerPage")
                                .getValue();
                            store.currentPage = 1;
                            Ext.getCmp("pagingToolbar")
                                .doRefresh();
                        },
                        scope : me
                    }
                }
            }, {
                xtype : "displayfield",
                value : "条记录"
            }],
        });

        return me.__mainGrid;
    },

    addSupplier : function() {
        var me = this;
        var item = me.getOrgGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            me.showInfo("请选择客户分类");
            return;
        }

        var category = item[0];
        var form = Ext.create("PSI.Customer.CustomerEditForm", {
            parentForm : me,
            category : category,
            selectOrg :me.getSelectOrg()
        });

        form.show();
    },
    /**
     * 刷新公司信息
     */
    freshCategoryGrid : function(id) {
        var me = this;
        var grid = me.getOrgGrid();
        var el = grid.getEl() || Ext.getBody();
        el.mask(PSI.Const.LOADING);
        me.ajax({
            url : me.URL("Home/OrgWare/orgList"),
            params : me.getQueryParam(),
            callback : function(options, success, response) {
                var store = grid.getStore();

                store.removeAll();

                if (success) {
                    var data = me.decodeJSON(response.responseText);
                    store.add(data);
                    if (store.getCount() > 0) {
                        if (id) {
                            var r = store.findExact("id", id);
                            if (r != -1) {
                                grid.getSelectionModel().select(r);
                            }
                        } else {
                            grid.getSelectionModel().select(0);
                        }
                    }
                }

                el.unmask();
            }
        });
    },

    /**
     * 刷新客户资料Grid
     */
    freshCustomerGrid : function(id) {
        var me = this;

        var item = me.getOrgGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            var grid = me.getMainGrid();
            grid.setTitle("仓库列表");
            return;
        }
        var ware = item[0];

        var grid = me.getMainGrid();
        grid.setTitle("属于 [" + ware.get("fullName") + "] 公司的仓库");

        me.__lastId = id;
        Ext.getCmp("pagingToolbar").doRefresh()
    },

    onCategoryGridSelect : function() {
        var me = this;
        me.getMainGrid().getStore().currentPage = 1;
        me.freshCustomerGrid();
    },
    /**
     * 删除公司仓库
     */
    onDeleteWarehouse : function() {
        var me = this;

        var item1 = me.getOrgGrid().getSelectionModel().getSelection();

        if (item1 == null || item1.length != 1) {
            me.showInfo("请先选择公司");
            return;
        }
        var org = item1[0];

        var item = me.getMainGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            me.showInfo("请选择要删除的仓库");
            return;
        }

        var ware = item[0];

        var store = me.getMainGrid().getStore();
        var index = store.findExact("id", ware.get("id"));
        index--;
        var preIndex = null;
        var preItem = store.getAt(index);
        if (preItem) {
            preIndex = preItem.get("id");
        }

        var info = "请确认是否删除仓库: <span style='color:red'>" + ware.get("name")
            + "</span>";
            var funcConfirm = function() {
                var el = Ext.getBody();
                el.mask("正在删除中...");

                var r = {
                    url : me.URL("Home/OrgWare/deleteOrgWare"),
                    params : {
                        wareId : ware.get("id"),
                        orgId:org.get("id"),
                    },
                    callback : function(options, success, response) {
                        el.unmask();

                        if (success) {
                            var data = me.decodeJSON(response.responseText);
                            if (data.success) {
                                me.tip("成功完成删除操作");
                                me.freshCategoryGrid(org.get("id"));
                            } else {
                                me.showInfo(data.msg);
                            }
                        }
                    }

                };

                me.ajax(r);
            };
            me.confirm(info, funcConfirm);
    },

    gotoCustomerGridRecord : function(id) {
        var me = this;
        var grid = me.getMainGrid();
        var store = grid.getStore();
        if (id) {
            var r = store.findExact("id", id);
            if (r != -1) {
                grid.getSelectionModel().select(r);
            } else {
                grid.getSelectionModel().select(0);
            }
        } else {
            grid.getSelectionModel().select(0);
        }
    },

    refreshCategoryCount : function() {
        var me = this;
        var item = me.getOrgGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            return;
        }

        var category = item[0];
        category.set("cnt", me.getMainGrid().getStore().getTotalCount());
        me.getOrgGrid().getStore().commitChanges();
    },

    onLastQueryEditSpecialKey : function(field, e) {
        if (e.getKey() === e.ENTER) {
            this.onQuery();
        }
    },

    getQueryParam : function() {
        var me = this;
        var item = me.getOrgGrid().getSelectionModel().getSelection();
        var orgId;
        if (item == null || item.length != 1) {
            categoryId = null;
        } else {
            orgId = item[0].get("id");
        }

        var result = {
            orgId : orgId
        };
        var code = Ext.getCmp("code").getValue();
        if (code) {
            result.code = code;
        }

        var name = Ext.getCmp("name").getValue();
        if (name) {
            result.name = name;
        }
        return result;
    },

    /**
     * 查询
     */
    onQuery : function() {
        this.freshCategoryGrid();
    },

    /**
     * 清除查询条件
     */
    onClearQuery : function() {
        var me = this;
        var code = Ext.getCmp("code").setValue();
        var name = Ext.getCmp("name").setValue();

        me.onQuery();
    }
});