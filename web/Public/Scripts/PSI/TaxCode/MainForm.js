/**
 * 现货价格- 主界面
 */
Ext.define("PSI.TaxCode.MainForm", {
    extend : "PSI.AFX.BaseMainExForm",

    config : {
        pAdd : null,
        pEdit : null,
        pDelete : null,
        pEditDataOrg : null,
        pInitInv : null
    },

    initComponent : function() {
        var me = this;

        Ext.apply(me, {
            tbar : me.getToolbarCmp(),
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
                    region : "north",
                    split : true,
                    height : '100%',
                    layout : "fit",
                    border : 0,
                    items : [me.getMainGrid()]
                }]
            }]
        });

        me.callParent(arguments);

        me.refreshMainGrid();
    },

    getToolbarCmp : function() {
        var me = this;
        return [{
            text : "新增税务编码",
            disabled : me.getPAdd() == "0",
            handler : me.onAddTaxCode,
            scope : me
        }, "-",{
            text : "编辑税务编码",
            disabled : me.getPAdd() == "0",
            handler : me.onEditTaxCode,
            scope : me
        }, "-", {
            text : "关闭",
            handler : function() {
                me.closeWindow();
            }
        }];
    },

    getQueryCmp : function() {
        var me = this;
        return [{
            id : "merge_code",
            labelWidth : 60,
            labelAlign : "right",
            labelSeparator : "",
            fieldLabel : "合并编码",
            margin : "5, 0, 0, 0",
            xtype : "textfield",
            listeners : {
                specialkey : {
                    fn : me.onQueryEditSpecialKey,
                    scope : me
                }
            }
        },{
            id : "name",
            labelWidth : 60,
            labelAlign : "right",
            labelSeparator : "",
            fieldLabel : "货物名称",
            margin : "5, 0, 0, 0",
            xtype : "textfield",
            listeners : {
                specialkey : {
                    fn : me.onQueryEditSpecialKey,
                    scope : me
                }
            }
        },{
            id : "cate_name",
            labelWidth : 100,
            labelAlign : "right",
            labelSeparator : "",
            fieldLabel : "商品和服务简称",
            margin : "5, 0, 0, 0",
            xtype : "textfield",
            listeners : {
                specialkey : {
                    fn : me.onQueryEditSpecialKey,
                    scope : me
                }
            }
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



    getMainGrid : function() {
        var me = this;
        if (me.__mainGrid) {
            return me.__mainGrid;
        }
        var modelName = "PSI_Price_MainForm_PSIPrice";
        Ext.define(modelName, {
            extend : "Ext.data.Model",
            fields : ["id","code_one","code_two","code_three","code_four","code_five","code_six","code_seven",
				"code_eight","code_nine","code_ten","merge_code","name","cate_name","memo",]
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
                url : PSI.Const.BASE_URL + "Home/TaxCode/taxCodeList",
                reader : {
                    root : 'dataList',
                    totalProperty : 'totalCount'
                }
            }
        });
        store.on("beforeload", function() {
            store.proxy.extraParams = me.getQueryParam();
        });
        store.on("load", function(e, records, successful) {
            if (successful) {
                me.getMainGrid(me.__lastId);
            }
        });

        me.__mainGrid = Ext.create("Ext.grid.Panel", {
            cls : "PSI",
            viewConfig : {
                enableTextSelection : true
            },
            border : 1,
            columnLines : true,
            columns : [{
                xtype : "rownumberer",
                width : 40
            }, {
                header : "篇",
                dataIndex : "code_one",
                align : 'center',
                menuDisabled : true,
                sortable : false,
                width : 50
            }, {
                header : "类",
                dataIndex : "code_two",
                menuDisabled : true,
                align : 'center',
                sortable : false,
                width : 50
            }, {
                header : "章",
                dataIndex : "code_three",
                menuDisabled : true,
                align : 'center',
                sortable : false,
                width : 50
            }, {
                header : "节",
                dataIndex : "code_four",
                menuDisabled : true,
                align : 'center',
                sortable : false,
                width : 50
            }, {
                header : "条",
                dataIndex : "code_five",
                menuDisabled : true,
                align : 'center',
                sortable : false,
                width : 50
            }, {
                header : "款",
                dataIndex : "code_six",
                menuDisabled : true,
                align : 'center',
                sortable : false,
                width : 50
            }, {
                header : "项",
                dataIndex : "code_seven",
                menuDisabled : true,
                align : 'center',
                sortable : false,
                width : 50
            }, {
                header : "目",
                dataIndex : "code_eight",
                menuDisabled : true,
                align : 'center',
                sortable : false,
                width : 50
            }, {
                header : "子目",
                dataIndex : "code_nine",
                menuDisabled : true,
                align : 'center',
                sortable : false,
                width : 50
            }, {
                header : "细目",
                dataIndex : "code_ten",
                menuDisabled : true,
                align : 'center',
                sortable : false,
                width : 50
            },  {
                header : "合并编码",
                dataIndex : "merge_code",
                menuDisabled : true,
                align : 'center',
                sortable : false,
                width : 150
            },  {
                header : "货物和劳务名称",
                dataIndex : "name",
                menuDisabled : true,
                align : 'center',
                sortable : false,
                width : 200
            },  {
                header : "商品和服务分类简称",
                dataIndex : "cate_name",
                menuDisabled : true,
                align : 'center',
                sortable : false,
                width : 200
            }, {
                header : "说明",
                dataIndex : "memo",
                menuDisabled : true,
                align : 'center',
                sortable : false,
                width : 300
            }],
            store : store,
            bbar : ["->", {
                id : "pagingToobar",
                xtype : "pagingtoolbar",
                border : 0,
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
                            Ext.getCmp("pagingToobar")
                                .doRefresh();
                        },
                        scope : me
                    }
                }
            }, {
                xtype : "displayfield",
                value : "条记录"
            }]
        });

        return me.__mainGrid;
    },

    refreshMainGrid : function(id) {
        var me = this;
        var store=me.getMainGrid().getStore();
        store.removeAll();

        Ext.getCmp("pagingToobar").doRefresh();
        this.__lastId = id;
    },

    onQuery : function() {
        var me = this;

        me.getMainGrid().getStore().currentPage = 1;
        me.refreshMainGrid();
    },

    onClearQuery : function() {
        var me = this;


        Ext.getCmp("merge_code").setValue(null);
        Ext.getCmp("cate_name").setValue(null);
        Ext.getCmp("name").setValue(null);

        me.onQuery();
    },

    /**
     * 新增现货价格
     */
    onAddTaxCode : function() {
        var me = this;

        var form = Ext.create("PSI.TaxCode.EditForm", {
            parentForm : me,
            edit:false
        });

        form.show();
    },

    /**
     * 编辑税务编码
     */
    onEditTaxCode : function() {
        var me = this;

        var item = me.getMainGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            me.showInfo("请选择要编辑的税控编码");
            return;
        }

        var code = item[0];
        var form = Ext.create("PSI.TaxCode.EditForm", {
            parentForm : me,
            entity:code,
        });

        form.show();
    },

    getQueryParam : function() {
        var me = this;

        var result = {
            cate_name : Ext.getCmp("cate_name").getValue()
        };

        var merge_code = Ext.getCmp("merge_code").getValue();
        if (merge_code) {
            result.code = merge_code;
        }

        var name = Ext.getCmp("name").getValue();
        if (name) {
            result.name = name;
        }

        return result;
    }


});