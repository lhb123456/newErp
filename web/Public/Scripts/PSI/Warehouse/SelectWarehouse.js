/**
 * 自定义字段 - 组织机构字段,用数据域过滤
 */
Ext.define("PSI.Warehouse.SelectWarehouse", {
    extend : "Ext.form.field.Trigger",
    alias : "widget.psi_selectWarehouse",
    config : {
        callbackFunc : null,
        callbackObj:null
    },
    initComponent : function() {
        var me = this;

        me.__idValue = null;

        me.enableKeyEvents = true;

        me.callParent(arguments);

        me.on("keydown", function(field, e) {
            if (e.getKey() == e.BACKSPACE) {
                field.setValue(null);
                me.setIdValue(null);
                e.preventDefault();
                return false;
            }

            if (e.getKey() !== e.ENTER) {
                this.onTriggerClick(e);
            }
        });
    },

    onTriggerClick : function(e) {
        var me = this;

        var modelName = "PSIOrgModel_OrgWithDataOrgField";
        Ext.define(modelName, {
            extend : "Ext.data.Model",
            fields : ["id", "code","name","address"]
        });

        var orgStore = Ext.create("Ext.data.Store", {
            model : modelName,
            autoLoad : false,
            data : []
        });

        var orgTree = Ext.create("Ext.grid.Panel", {
            cls : "PSI",
            store : orgStore,
            columns : [{
                header : "仓库编码",
                dataIndex : "code",
                width:100,
                menuDisabled : true,
                sortable : false
            },{
                header : "仓库名称",
                dataIndex : "name",
                flex : 1,
                menuDisabled : true,
                sortable : false
            },{
                header : "仓库地址",
                dataIndex : "address",
                flex : 1,
                menuDisabled : true,
                sortable : false
            }]
        });
        orgTree.on("itemdblclick", this.onOK, this);
        this.tree = orgTree;
        var wnd = Ext.create("Ext.window.Window", {
            title : "选择仓库",
            modal : true,
            width : 800,
            height : 550,
            layout : "border",
            items : [{
                region : "center",
                xtype : "panel",
                layout : "fit",
                border : 0,
                items : [orgTree]
            }, {
                xtype : "panel",
                region : "south",
                height : 90,
                layout : "fit",
                border : 0,
                items : [{
                    xtype : "form",
                    layout : "form",
                    bodyPadding : 5,
                    items : [{
                        id : "warehouse",
                        xtype : "textfield",
                        fieldLabel : "仓库",
                        labelWidth : 50,
                        labelAlign : "right",
                        labelSeparator : ""
                    }, {
                        xtype : "displayfield",
                        fieldLabel : " ",
                        value : "输入仓库名称可以过滤查询",
                        labelWidth : 50,
                        labelAlign : "right",
                        labelSeparator : ""
                    }]
                }]
            }],
            buttons : [{
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
        this.wnd = wnd;
        wnd.show();
        var editName = Ext.getCmp("warehouse");
        editName.on("change", function() {
            me.refreshGrid();
        })
        me.refreshGrid();
    },

    refreshGrid : function() {
        var me = this;
        var grid = me.tree;

        var el = grid.getEl() || Ext.getBody();
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url : PSI.Const.BASE_URL
                + "Home/OrgWare/selectWarehouse",
            params : {
                data : Ext.getCmp("warehouse").getValue()
            },
            method : "POST",
            callback : function(options, success, response) {
                var store = grid.getStore();

                store.removeAll();

                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    if(data){
                        store.add(data);
                    }
                }

                el.unmask();
            }
        });
    },

    onOK : function() {
        var me = this;

        var tree = me.tree;
        var item = tree.getSelectionModel().getSelection();

        if (item === null || item.length !== 1) {
            PSI.MsgBox.showInfo("没有选择仓库");

            return;
        }

        var data = item[0];

        me.setIdValue(data.get("id"));
        me.setValue(data.get("name"));
        me.wnd.close();
        me.focus();

        var func = me.getCallbackFunc();
        if (func) {
            func(data.getData(),me.getCallbackObj());
        }
    },

    setIdValue : function(id) {
        this.__idValue = id;
    },

    getIdValue : function() {
        return this.__idValue;
    }
});