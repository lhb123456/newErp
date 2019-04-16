/**
 * 选择已提交的信用评估的往来单位
 */
Ext.define("PSI.COCompany.SelectCreditAssess", {
    extend : "PSI.AFX.BaseDialogForm",

    config:{
        perantForm:null
    },

    initComponent : function() {
        var me = this;

        Ext.apply(me, {
            title : "选择往来单位进行等级评分",
            width : 1050,
            height : 600,
            layout : "border",
            items : [{
                region : "center",
                border : 0,
                bodyPadding : 10,
                layout : "border",
                items : [{
                    region : "north",
                    height : "100%",
                    split : true,
                    layout : "fit",
                    items : [me.getBillGrid()]
                }]
            }, {
                region : "north",
                border : 0,
                layout : {
                    type : "table",
                    columns : 5
                },
                height : 100,
                bodyPadding : 10,
                items : [{
                    html : "<h2>选择往来单位</h2>",
                    border : 0,
                    colspan : 5
                }, {
                    id : "company",
                    xtype : "textfield",
                    labelWidth : 60,
                    labelAlign : "right",
                    labelSeparator : "",
                    fieldLabel : "往来单位",
                    width : 400,
                }, {
                    id:"bnt",
                    xtype : "container",
                    items : [{
                        xtype : "button",
                        text : "查询",
                        width : 60,
                        margin : "0 0 0 10",
                        iconCls : "PSI-button-refresh",
                        handler : me.onQuery,
                        scope : me
                    }, {
                        xtype : "button",
                        text : "清空查询条件",
                        margin : "0, 0, 0, 10",
                        handler : me.onClearQuery,
                        scope : me
                    }]
                }]
            }],
            listeners : {
                show : {
                    fn : me.onWndShow,
                    scope : me
                }
            },
            buttons : [{
                text : "确认",
                iconCls : "PSI-button-ok",
                formBind : true,
                handler : me.onOK,
                scope : me
            }, {
                text : "取消",
                handler : function() {
                    me.close();
                },
                scope : me
            }]
        });

        me.callParent(arguments);
        this.onQuery();
    },

    onWndShow : function() {
        var me = this;
    },

    onOK : function() {
        var me = this;

        var item = me.getBillGrid().getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("请选择往来单位");
            return;
        }
        var bill = item[0];
        var id=bill.get("id");



        //var data=[{"type":type,"dealMemo":dealMemo,"hedgeId":hedgeId}]

        me.close();
        me.getParentForm().onAddRankAssess(id);
    },

    getBillGrid : function() {
        var me = this;

        if (me.__BillGrid) {
            return me.__BillGrid;
        }

        var modelName = "PSI_POSelectForm";
        Ext.define(modelName, {
            extend : "Ext.data.Model",
            fields : ["id","tableStatus","companyName","companyAssetType","legalPerson","registerAddr",
                        "companyType"]
        });
        var storeWSBill = Ext.create("Ext.data.Store", {
            autoLoad : false,
            model : modelName,
            data : [],
            pageSize : 10,
            proxy : {
                type : "ajax",
                actionMethods : {
                    read : "POST"
                },
                url : PSI.Const.BASE_URL + "Home/COCompany/selectAssessCompany",
                reader : {
                    root : 'dataList',
                    totalProperty : 'totalCount'
                }
            }
        });

        storeWSBill.on("beforeload", function() {
            storeWSBill.proxy.extraParams = me.getQueryParam();
        });

        me.__BillGrid = Ext.create("Ext.grid.Panel", {
            cls : "PSI",
            columnLines : true,
            columns : [Ext.create("Ext.grid.RowNumberer", {
                text : "序号",
                width : 50
            }), {
                header : "往来单位名称",
                dataIndex : "companyName",
                align : "center",
                menuDisabled : true,
                width : 300

            },{
                header : "公司性质",
                align : "center",
                dataIndex : "companyAssetType",
                width : 100,
                menuDisabled : true,
                renderer:function (value) {
                    var str="";

                    if(value==1){
                        str="国有独资";
                    }else if(value==2){
                        str="上市公司";
                    }else if(value==3){
                        str="中外合资";
                    }else if(value==4){
                        str="外商独资";
                    }else if(value==5){
                        str="民营独资";
                    }else if(value==6){
                        str="民营股份";
                    }else if(value==7){
                        str="其他";
                    }

                    return str;
                }

            },{
                header : "法定代表人",
                align : "center",
                width : 100,
                dataIndex : "legalPerson",
                menuDisabled : true,

            },{
                header : "注册地址",
                dataIndex : "registerAddr",
                align : "center",
                menuDisabled : true,
                width : 300
            },{
                header : "交易类型",
                dataIndex : "companyType",
                align : "center",
                width : 120,
                menuDisabled : true,
                renderer:function (value) {
                    var str="";
                    for(var i=0;i<value.length;i++){
                        if(value[i]==1){
                            str+="制造商";
                        }
                        if(value[i]==2){
                            if(str){
                                str+="/贸易商";
                            }else{
                                str+="贸易商";
                            }
                        }
                    }

                    return str;
                }
            },{
                header : "详细信息",
                dataIndex : "dealDate",
                align : "center",
                menuDisabled : true,
                renderer:function (value,md,record) {

                        var id=record.get("id");
                        var tableStatus=record.get("tableStatus");
                        var permission=Ext.JSON.encode(me.getParentForm().getPermission())

                        return "<a class='deal' href='javascript:;' onclick='deal("+permission+",\""+id+"\",\""+tableStatus+"\")'>查看</a>";

                }
            }],
            listeners : {
                itemdblclick : {
                    fn : me.onOK,
                    scope : me
                }
            },
            store : storeWSBill,
            bbar : [{
                id : "srbill_selectform_pagingToobar",
                xtype : "pagingtoolbar",
                border : 0,
                store : storeWSBill
            }, "-", {
                xtype : "displayfield",
                value : "每页显示"
            }, {
                id : "srbill_selectform_comboCountPerPage",
                xtype : "combobox",
                editable : false,
                width : 60,
                store : Ext.create("Ext.data.ArrayStore", {
                    fields : ["text"],
                    data : [["20"], ["50"], ["100"], ["300"],
                        ["1000"]]
                }),
                value : 20,
                listeners : {
                    change : {
                        fn : function() {
                            storeWSBill.pageSize = Ext
                                .getCmp("srbill_selectform_comboCountPerPage")
                                .getValue();
                            storeWSBill.currentPage = 1;
                            Ext
                                .getCmp("srbill_selectform_pagingToobar")
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

        return me.__BillGrid;
    },

    onQuery : function() {
        var me=this;

        Ext.getCmp("srbill_selectform_pagingToobar").doRefresh();

    },



    getQueryParam : function() {
        var me=this;

        var result = {};

        var company = Ext.getCmp("company").getValue();

        if (company) {
            result.company = company;
        }

        return result;
    },

    onClearQuery : function() {
        Ext.getCmp("company").setValue(null);

        this.onQuery();
    }


});


function deal(permission,id,tableStatus) {
    var me=this;

   var creditInfo={
       id:id,
       tableStatus:tableStatus
   };
    //console.log(permission.add)
    //console.log(id)
    var form = Ext.create("PSI.COCompany.EditForm", {
        permission:permission,
        credit:creditInfo
    });
    form.show();
}