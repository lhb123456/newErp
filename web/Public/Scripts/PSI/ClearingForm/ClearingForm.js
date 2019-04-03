/**
 * 供应商自定义字段
 */
Ext.define("PSI.ClearingForm.ClearingForm", {
    extend : "Ext.form.field.Trigger",
    alias : "widget.psi_clearingForm",

    config : {
        callbackFunc : null,
        callbackScope : null,
        showAddButton : false
    },

    initComponent : function() {
        var me = this;

        me.__idValue = null;

        me.enableKeyEvents = true;

        me.callParent(arguments);

        me.on("keydown", function(field, e) {
            if (me.readOnly) {
                return;
            }

            if (e.getKey() == e.BACKSPACE) {
                field.setValue(null);
                me.setIdValue(null);
                e.preventDefault();
                return false;
            }

            if (e.getKey() != e.ENTER && !e.isSpecialKey(e.getKey())) {
                me.onTriggerClick(e);
            }
        });
    },

    onTriggerClick : function(e) {
        var me = this;
        var modelName = "PSISupplierField";
        Ext.define(modelName, {
            extend : "Ext.data.Model",
            fields : ["id","clearing","memo"]
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
                xtype : "rownumberer"
            },{
                header : "结算方式",
                dataIndex : "clearing",
                align : 'center',
                menuDisabled : true,
                flex : 1
            },{
                header : "备注 ",
                dataIndex : "memo",
                align : 'center',
                menuDisabled : true,
                flex : 1
            }]
        });
        me.lookupGrid = lookupGrid;
        me.lookupGrid.on("itemdblclick", me.onOK, me);

        var wnd = Ext.create("Ext.window.Window", {
            title : "选择 - 结算方式",
            modal : true,
            width : 600,
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
                height : 0,
                layout : "fit",
                border : 0,
                items : [{
                    xtype : "form",
                    layout : "form",
                    bodyPadding : 5,
                    items : [{
                        id : "__editSupplier",
                        xtype : "textfield",
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

        wnd.on("close", function() {
            me.focus();
        });
        me.wnd = wnd;
        var obj=me.getCallbackScope();
        var editName = Ext.getCmp("__editSupplier");
        editName.on("change", function() {
            var store = me.lookupGrid.getStore();
            Ext.Ajax.request({
                url : PSI.Const.BASE_URL + "Home/ClearingForm/ClearList",
                params : {
                    queryKey : editName.getValue(),
                    companyId:obj.company_id==null?null:obj.company_id
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

    // private
    onOK : function() {
        var me = this;
        var grid = me.lookupGrid;
        var item = grid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            return;
        }

        var data = item[0].getData();

        me.wnd.close();
        me.focus();
        me.setValue(data.clearing);
        me.focus();

        me.setIdValue(data.id);

        var func = me.getCallbackFunc();
        if (func) {
            func(data, me.getCallbackScope());
        }
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

    onAdd : function() {
        var form = Ext.create("PSI.Supplier.SupplierEditForm");
        form.show();
    }
});


