/**
 * 新建发票
 */
Ext.define("PSI.Bank.EditForm", {
    extend : "PSI.AFX.BaseDialogForm",
    config : {
        contract : null,
        bank : null,
        dept_id:null,
        contract_detail :null,
    },

    initComponent : function() {
        var me = this;

        var t = me.getBank == null?"添加银行信息":"编辑银行信息";
        var f = "edit-form-money.png";
        var logoHtml = "<img style='float:left;margin:20px 20px 0px 10px;width:48px;height:48px;' src='"
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
            width : 500,
            height : 450,
            layout : "border",
            defaultFocus : "editActMoney",
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
                border : 0,
                height : 90,
                html : logoHtml
            },{
                region : "center",
                border : 0,
                id : "editForm",
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
                    id :"id",
                    xtype : "hidden",
                    name : "id",
                },{
                    id :"ca_id",
                    xtype : "hidden",
                    name : "ca_id",
                    value : me.getContract()
                        .get("id")
                },{
                     id :"ca_name",
                    fieldLabel : "往来单位名称:",
                    labelWidth : 100,
                    name :"ca_name",
                    xtype : "displayfield",
                    colspan :2,
                    value : me.getContract()
                        .get("ca_name")
                },{
                    fieldLabel : "银行名称:",
                    labelWidth : 80,
                    allowBlank : false,
                    blankText : "没有输入银行名称",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    xtype : "textfield",
                    hideTrigger : true,
                    name : "bank_name",
                    id : "bank_name",
                    listeners : {
                        specialkey : {
                            fn : me.onEditActMoneySpecialKey,
                            scope : me
                        }
                    }
                }, {
                    fieldLabel : "银行账户:",
                    labelWidth : 80,
                    allowBlank : false,
                    blankText : "没有输入银行名称",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    xtype : "textfield",
                    hideTrigger : true,
                    name : "bank_account",
                    id : "bank_account",
                    listeners : {
                        specialkey : {
                            fn : me.onEditActMoneySpecialKey,
                            scope : me
                        }
                    }
                }, {
                    id : "default",
                    xtype : "combo",
                    queryMode : "local",
                    editable : false,
                    valueField : "id",
                    labelWidth : 120,
                    labelAlign : "right",
                    beforeLabelTextTpl : PSI.Const.REQUIRED,
                    labelSeparator : "",
                    fieldLabel : "是否设为默认账户",
                    margin : "5, 0, 0, 0",
                    store : Ext.create("Ext.data.ArrayStore", {
                        fields : ["id", "text"],
                        data : [[0, "是"],[1, "否"]]
                    }),
                },{
                    fieldLabel : "备注:",
                    name : "memo",
                    id : "memo",
                    labelWidth : 80,
                } ],
            }],
            buttons : [{
                text : "保存",
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
    },

    onWindowBeforeUnload : function(e) {
        return (window.event.returnValue = e.returnValue = '确认离开当前页面？');
    },

    onWndClose : function() {
        var me = this;

        Ext.get(window).un('beforeunload', me.onWindowBeforeUnload);
    },

    onWndShow : function() {
        var me = this;
        if(me.getBank() == null){
            var id = null
        }else{
            var id=me.getBank().get("id")
        }
        var el = me.getEl() || Ext.getBody();
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url : PSI.Const.BASE_URL + "Home/Bank/bankDetail",
            params : {
                id : id
            },
            method : "POST",
            callback : function(options, success, response) {
                el.unmask();

                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    console.log(data);
                    if (data.id) {
                        Ext.getCmp("id").setValue(data.id)
                        Ext.getCmp("bank_name").setValue(data.bank_name)
                        Ext.getCmp("bank_account").setValue(data.account)
                        Ext.getCmp("default").setValue(parseInt(data.is_default))
                        Ext. getCmp("memo").setValue(data.memo)
                    }
                }
            }
        });
    },

    onOK : function() {
        var me = this;
        if(!Ext.getCmp("bank_name").getValue()){
            PSI.MsgBox.showInfo("请输入银行名称");
            return;
        }
        if(!Ext.getCmp("bank_account").getValue()){
            PSI.MsgBox.showInfo("请输入银行账户");
            return;
        }
        var data =Ext.getCmp("default").getValue()
        if(data == 0 || data == 1){
            Ext.getBody().mask("正在保存中...");
            Ext.Ajax.request({
                url : PSI.Const.BASE_URL + "Home/Bank/bankAdd",
                method : "POST",
                params : {
                    jsonStr : me.getSaveData(),
                },
                callback : function(options, success, response) {
                    Ext.getBody().unmask();
                    if (success) {
                        var data = Ext.JSON.decode(response.responseText);
                        if (data.success) {
                            PSI.MsgBox.showInfo("成功保存数据", function() {
                                me.close();
                                me.getParentForm().onRvGridSelect();
                            });
                        } else {
                            PSI.MsgBox.showInfo(data.msg);
                        }
                    }
                }
            });
        }else{
            PSI.MsgBox.showInfo("请选择是否设为默认账户");
            return;
        }
    },

    getSaveData : function() {
        var me=this;

        var result = {

            id : Ext.getCmp("id").getValue(),
            caId : Ext.getCmp("ca_id").getValue(),
            caName : Ext.getCmp("ca_name").getValue(),
            bankName : Ext.getCmp("bank_name").getValue(),
            bankAccount : Ext.getCmp("bank_account").getValue(),
            default : Ext.getCmp("default").getValue(),
            memo : Ext.getCmp("memo").getValue(),
        };
        return Ext.JSON.encode(result);
    },
    onEditBizDTSpecialKey : function(field, e) {
        if (e.getKey() == e.ENTER) {
            Ext.getCmp("editActMoney").focus();
        }
    },

    onEditActMoneySpecialKey : function(field, e) {
        if (e.getKey() == e.ENTER) {
            Ext.getCmp("editBizUser").focus();
        }
    },
});