/**
 * 业务设置 - 编辑设置项目
 */
Ext.define("PSI.COCompany.EditForm", {
	extend : "PSI.AFX.BaseDialogForm",

	config : {
		companyId : null
	},

	initComponent : function() {
		var me = this;

		var buttons = [];

		buttons.push({
					text : "保存",
					formBind : true,
					iconCls : "PSI-button-ok",
					handler : function() {
						me.onOK();
					},
					scope : me
				}, {
					text : "取消",
					handler : function() {
						me.close();
					},
					scope : me
				});

		var modelName = "PSIWarehouse";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "name"]
				});

		var storePW = Ext.create("Ext.data.Store", {
					model : modelName,
					autoLoad : false,
					fields : ["id", "name"],
					data : []
				});
		me.__storePW = storePW;
		var storeWS = Ext.create("Ext.data.Store", {
					model : modelName,
					autoLoad : false,
					fields : ["id", "name"],
					data : []
				});
		me.__storeWS = storeWS;

		Ext.apply(me, {
			header : {
				title : me.formatTitle("信用额度评估信息填写"),
				height : 40,
				iconCls : "PSI-button-edit"
			},
			width : 1200,
			height : 760,
			layout : "fit",
			items : [{
				xtype : "tabpanel",
				bodyPadding :{top:20},
				border : 0,
				items : [{
							title : "基本情况",
							border : 0,

							//hidden:true,
						layout : {
							type : "table",
							columns :3
						},

						iconCls : "PSI-fid2008",
						items : [{
									id : "hiddenId",
                            		xtype : "hidden",
									hidden:true
								},{
									id : "companyName",
									fieldLabel : "公司名称",
									labelWidth:65,
									padding:{left:20,bottom:10},
									allowBlank : false,
                            		beforeLabelTextTpl : PSI.Const.REQUIRED,
									xtype : "textfield",
									width:300,
								},{
									id:"companyType",
									fieldLabel : "交易类型",
                            		labelWidth:65,
                            		padding:{left:30,bottom:10},
									xtype: 'checkboxgroup',
									name: 'companyType',
                            		colspan:2,
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									columns: 2,  //在上面定义的宽度上展示3列
									items: [
										{boxLabel: '供应商', name: 'supplier',width:120,inputValue:1},
										{boxLabel: '客户', name: 'customer',width:120,inputValue:2}
									]
								}, {
									id:"assessTimes",
									padding:{left:20,bottom:10},
									labelWidth:65,
									fieldLabel : "评估次数",
									columns: 2,
									allowBlank : false,
									blankText : "必须选值",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									xtype : "radiogroup",
									value:1,
									items: [
										{id:"assessTimes_y",name: 'assessTimes',inputValue: 1,boxLabel: '初次评估',width:120,checked:true},
										{id:"assessTimes_n",name: 'assessTimes',inputValue: 2,boxLabel: '二次评估以上',width:120}
									]

								}, {
									id : "limit",
									xtype : "textfield",
									padding:{left:30,bottom:10},
									labelWidth:100,
									fieldLabel : "原额度(吨/公斤)",
									colspan:2,
									width:350,
								}, {
									id : "companyAssetType",
									padding:{left:20,bottom:10},
									labelWidth:65,
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									fieldLabel : "公司性质",
									xtype : "radiogroup",
									colspan:3,
									columns: 7,
									value:1,
									items: [
										{name: 'companyAssetType',inputValue: 1,boxLabel: '国有独资',width:80,checked:true},
										{name: 'companyAssetType',inputValue: 2,boxLabel: '上市公司',width:100},
										{name: 'companyAssetType',inputValue: 3,boxLabel: '中外合资',width:100},
										{name: 'companyAssetType',inputValue: 4,boxLabel: '外商独资',width:100},
										{name: 'companyAssetType',inputValue: 5,boxLabel: '民营独资',width:100},
										{name: 'companyAssetType',inputValue: 6,boxLabel: '民营股份',width:100},
										{name: 'companyAssetType',inputValue: 7,boxLabel: '其他',width:100}
									]
								},{
									id : "companyAddrType",
									padding:{left:22,bottom:10},
									labelWidth:65,
									fieldLabel : "离岸公司",
									xtype : "radiogroup",
									colspan:1,
									columns: 3,
									value:0,
									items: [
										{name: 'companyAddrType',inputValue: 0,boxLabel: '无',width:80,checked:true},
										{name: 'companyAddrType',inputValue: 1,boxLabel: '离岸公司',width:80},
										{name: 'companyAddrType',inputValue: 2,boxLabel: '在岸公司',width:80}
									]
								},{
									id : "otherCompany",
									labelWidth:60,
									fieldLabel : "公司名称",
									colspan:2,
									xtype : "textfield",
									padding:{left:30,bottom:10},
									width:350,
								},{
									id:"companyTradeType",
									padding:{left:22,bottom:10},
									labelWidth:65,
									fieldLabel : "公司类型",
									xtype: 'checkboxgroup',
									name: 'companyTradeType',
									columns: 2,  //在上面定义的宽度上展示3列
									items: [
										{boxLabel: '制造商', name: 'companyTradeType',inputValue: 1,width:120},
										{boxLabel: '贸易商', name: 'companyTradeType',inputValue: 2,width:120}
									]
								},{
									id : "companyStrength",
									fieldLabel : "行业地位",
									padding:{left:30,bottom:10},
									labelWidth:65,
									width:65,
									xtype : "radiogroup",
                            		colspan:2,
									columns: 3,
									value:1,
									items: [
										{name: 'companyStrength',inputValue: 1,boxLabel: '全球500强',width:100,checked:true},
										{name: 'companyStrength',inputValue: 2,boxLabel: '全国500强',width:100},
										{name: 'companyStrength',inputValue: 3,boxLabel: '行业100强',width:100}
									]
								},{
									id : "registerAddr",
									fieldLabel : "注册地址",
									labelWidth:65,
									padding:{left:20,bottom:10},
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									xtype : "textfield",
									width:300,
								},{
									id : "legalPerson",
									fieldLabel : "法定代表人",
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									padding:{left:30,bottom:10},
                            		colspan:2,
									labelWidth:75,
									xtype : "textfield",
									width:350,
								},{
                            		id : "registerMoneySubscribe",
									fieldLabel : "注册资金： 认缴",
									labelWidth:105,
                            		width:300,
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									xtype : "textfield",
									padding:{left:20,bottom:10},
									colspan:1,
								},{
                            		id : "registerMoneyPaid",
									fieldLabel : "实缴",
                            		width:350,
									labelWidth:30,
									xtype : "textfield",
									padding:{left:30,bottom:10},
									colspan:1,
								},{
									id : "check",
									fieldLabel : "年检情况",
									padding:{left:50,bottom:10},
									labelWidth:65,
									xtype : "radiogroup",
									colspan:2,
									columns: 2,
									value:1,
									items: [
										{name: 'check',inputValue: 1,boxLabel: '正常',width:100,checked:true},
										{name: 'check',inputValue: 0,boxLabel: '异常',width:100}
									]
								}, {
									id : "contact",
									fieldLabel : "主要联系人",
									labelWidth:75,
									padding:{left:20,bottom:10},
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									xtype : "textfield",
									width:300,
								},{
									id : "contactTel",
									fieldLabel : "联系电话",
									padding:{left:30,bottom:10},
									labelWidth:65,
									xtype : "textfield",
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
                            		colspan:2,
									width:350,
								},{
									id : "mainWork",
									fieldLabel : "主营业务",
									labelWidth:65,
									padding:{left:20,bottom:10},
									xtype : "textfield",
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
                            		colspan:3,
									width:1000,
								},{
									id : "operateAddr",
									fieldLabel : "经营地址",
									labelWidth:65,
									padding:{left:20,bottom:10},
									xtype : "textfield",
									width:300,
								},{
									id : "operateArea",
									fieldLabel : "经营面积",
									padding:{left:30,bottom:10},
									labelWidth:70,
									xtype : "textfield",
                            		colspan:2,
									width:350,
								},{
									id : "assetOffice",
									fieldLabel : "固定资产：办公",
									padding:{left:20,bottom:10},
									labelWidth:100,
									xtype : "radiogroup",
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									colspan:1,
									columns: 2,
									value:1,
									items: [
										{name: 'assetOffice',inputValue: 0,boxLabel: '自有',width:100},
										{name: 'assetOffice',inputValue: 1,boxLabel: '租赁',width:100,checked:true}
									]
								},{
									id : "assetWarehouse",
									fieldLabel : "厂房/仓库",
									padding:{left:30,bottom:10},
									labelWidth:70,
									xtype : "radiogroup",
									colspan:1,
									columns: 2,
									value:1,
									items: [
										{name: 'assetWarehouse',inputValue: 0,boxLabel: '自有',width:100},
										{name: 'assetWarehouse',inputValue: 1,boxLabel: '租赁',width:75,checked:true}
									]
								},{
									id : "assetProductline",
									fieldLabel : "生产线",
									padding:{left:50,bottom:10},
									labelWidth:60,
									xtype : "radiogroup",
									colspan:1,
									columns: 2,
									value:1,
									items: [
										{name: 'assetProductline',inputValue: 0,boxLabel: '自有',width:100},
										{name: 'assetProductline',inputValue: 1,boxLabel: '租赁',width:100,checked:true}
									]
								},{
									id : "asset",
									fieldLabel : "总资产",
									labelWidth:50,
									padding:{left:20,bottom:10},
									xtype : "textfield",
									width:300,
								},{
									id : "workTime",
									fieldLabel : "从业时间",
									padding:{left:30,bottom:10},
									labelWidth:65,
									xtype : "radiogroup",
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									colspan:1,
									columns: 3,
									value:1,
									items: [
										{name: 'workTime',inputValue: 1,boxLabel: '0-3年',width:80,checked:true},
										{name: 'workTime',inputValue: 2,boxLabel: '3-5年',width:80},
										{name: 'workTime',inputValue: 3,boxLabel: '5年以上',width:80}
									]
								},{
									id : "employeeNum",
									fieldLabel : "企业人数",
									padding:{left:50,bottom:10},
									labelWidth:60,
									xtype : "radiogroup",
									colspan:1,
									columns: 3,
									value:1,
									items: [
										{name: 'employeeNum',inputValue: 1,boxLabel: '100人以下',width:80},
										{name: 'employeeNum',inputValue: 2,boxLabel: '100-300人',width:80,checked:true},
										{name: 'employeeNum',inputValue: 3,boxLabel: '300人以上',width:80}
									]
								},{
									id:"assureAgreement",
									padding:{left:20,bottom:10},
									labelWidth:65,
									fieldLabel : "担保协议",
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									xtype: 'checkboxgroup',
									name: 'assureAgreement',
                            		colspan: 3,
									columns: 4,  //在上面定义的宽度上展示3列
									items: [
										{boxLabel: '公司担保金额', name: 'assureAgreement',inputValue: 1,width:140},
										{boxLabel: '个人担保金额', name: 'assureAgreement',inputValue: '2',width:140},
                                        {boxLabel: '资产担保金额', name: 'assureAgreement',inputValue: '3',width:140},
                                        {boxLabel: '其他担保金额', name: 'assureAgreement',inputValue: '4',width:140}
									]
								},{
									id:"baseData",
									padding:{left:20,bottom:10},
									labelWidth:65,
									fieldLabel : "基本资料",
									xtype: 'checkboxgroup',
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name: 'baseData',
									colspan: 1,
									columns: 3,  //在上面定义的宽度上展示3列
									items: [
										{boxLabel: '营业执照', name: 'baseData',inputValue: 1,width:100},
										{boxLabel: '财务报表', name: 'baseData',inputValue: 2,width:100},
										{boxLabel: '其他', name: 'baseData',inputValue: 3,width:80}
									]
								},{
									id:"otherData",
									padding:{left:30,bottom:10},
									labelWidth:65,
									fieldLabel : "补充资料",
									xtype: 'checkboxgroup',
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name: 'otherData',
									colspan: 2,
									columns: 2,  //在上面定义的宽度上展示3列
									items: [
										{boxLabel: '资质证书', name: 'otherData',inputValue: 1,width:140},
										{boxLabel: '第三方报告', name: 'otherData',inputValue: 2,width:140}
									]
								}]
					}, {
						title : "业务分析",
						border : 0,
						layout : {
							type : "table",
							columns :2
						},
						iconCls : "PSI-fid2001",
						items : [{
									id : "tradeBreed",
									fieldLabel : "交易品种",
									padding:{left:30,bottom:20},
									labelWidth:65,
									xtype : "textfield",
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									width:500,
								},{
									id : "isTrade",
									fieldLabel : "是否在交易范围内",
									padding:{left:100,bottom:20},
									labelWidth:120,
									xtype : "radiogroup",
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									columns: 2,
									value:1,
									items: [{
										name: 'assetProductline',
										inputValue: 1,
										boxLabel: '是',
										width:100,
                                        checked:true
									}, {
										name: 'assetProductline',
										inputValue: 0,
										boxLabel: '否',
										width:100
									}]
								},{
									id : "tradeReason",
									fieldLabel : "交易理由",
									padding:{left:30,bottom:20},
									labelWidth:60,
                            		colspan: 2,
									xtype : "textfield",
									width:900,
								},{
									id : "position",
									fieldLabel : "客户定位",
									padding:{left:30,bottom:20},
									labelWidth:65,
									xtype : "radiogroup",
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
                            		colspan: 2,
									columns: 4,
									value:1,
									items: [
										{name: 'position',inputValue: 1,boxLabel: '战略供应商/客户',width:150,checked:true},
										{name: 'position',inputValue: 2,boxLabel: '核心供应商/分销大客户',width:180},
										{name: 'position',inputValue: 3,boxLabel: '稳定供应商/分销中小客户',width:180 },
										{name: 'position',inputValue: 4,boxLabel: '行情投机供应商/客户',width:150}
									]
								},{
									id : "planQuantity",
									fieldLabel : "计划业务规模： 计划量(月/年)",
									padding:{left:30,bottom:20},
									labelWidth:185,
									xtype : "textfield",
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									width:500,
								},{
									id : "tradePeriod",
									fieldLabel : "交易周期",
									padding:{left:100,bottom:20},
									labelWidth:65,
									xtype : "textfield",
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									width:300,
								},{
									id : "influence",
									fieldLabel : "行业影响力及口碑",
									padding:{left:30,bottom:20},
									labelWidth:125,
                            		colspan: 2,
									xtype : "textfield",
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									width:900,
								},{
									id : "interflow",
									fieldLabel : "近半年交流简况： 访问次数/时间/访问人",
									padding:{left:30,bottom:20},
									labelWidth:245,
									colspan: 2,
									xtype : "textfield",
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									width:900,
								},]
					}, {
						title : "业务风险披露",
						border : 0,
						layout : {
							type : "table",
							columns :2
						},
						iconCls : "PSI-fid2002",
						items : [{
									id:"risk",
									padding:{left:30,bottom:20},
									labelWidth:65,
									fieldLabel : "风险披露",
									xtype: 'checkboxgroup',
									allowBlank : false,
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name: 'risk',
									colspan: 2,
									columns: 10,  //在上面定义的宽度上展示3列
									items: [
										{boxLabel: '信用违约', name: 'risk',inputValue: 1,width:80},
										{boxLabel: '盈利差', name: 'risk',inputValue: 2,width:80},
										{boxLabel: '高负债', name: 'risk',inputValue: 3,width:80},
										{boxLabel: '融资差', name: 'risk',inputValue: 4,width:80},
                                        {boxLabel: '高库存', name: 'risk',inputValue: 5,width:80},
                                        {boxLabel: '地域环境', name: 'risk',inputValue: 6,width:80},
                                        {boxLabel: '对外担保', name: 'risk',inputValue: 7,width:80},
                                        {boxLabel: '安全责任', name: 'risk',inputValue: 8,width:80},
                                        {boxLabel: '管理差', name: 'risk',inputValue: 9,width:80},
                                        {boxLabel: '工商税务', name: 'risk',inputValue: 10,width:80},
                                        {boxLabel: '法务诉讼', name: 'risk',inputValue: 11,width:80},
                                        {boxLabel: '政策法规', name: 'risk',inputValue: 12,width:80},
                                        {boxLabel: '负责人有不良习惯', name: 'risk',inputValue: 13,width:120},
                                        {boxLabel: '其他', name: 'risk',inputValue: 14,width:80}
									]
								},{
									id : "riskDescribe",
									fieldLabel : "风险描述",
									padding:{left:30,bottom:20},
									labelWidth:60,
									colspan: 2,
									xtype : "textfield",
									width:900,
								},{
									id : "riskKeepaway",
									fieldLabel : "最大风险及防范",
									padding:{left:30,bottom:20},
									labelWidth:100,
									colspan: 2,
									xtype : "textfield",
									width:900,
								}]
					}],
				buttons : buttons
			}],
			listeners : {
				close : {
					fn : me.onWndClose,
					scope : me
				},
				show : {
					fn : me.onWndShow,
					scope : me
				}
			}
		});

		me.callParent(arguments);
	},

	getSaveData : function() {
		var me = this;

		var result = {
			id:Ext.getCmp("hiddenId").getValue(),
			companyId : me.getCompanyId(),
			companyName:Ext.getCmp("companyName").getValue(),
			companyType:me.getCheckBoxGroup("companyType"),
			limit:Ext.getCmp("limit").getValue(),
            companyAssetType:Ext.getCmp("companyAssetType").getValue().companyAssetType,
            companyAddrType:Ext.getCmp("companyAddrType").getValue().companyAddrType,
            otherCompany:Ext.getCmp("otherCompany").getValue(),
            companyTradeType:me.getCheckBoxGroup("companyTradeType"),
            companyStrength:Ext.getCmp("companyStrength").getValue().companyStrength,
            registerAddr:Ext.getCmp("registerAddr").getValue(),
            legalPerson:Ext.getCmp("legalPerson").getValue(),
            registerMoneySubscribe:Ext.getCmp("registerMoneySubscribe").getValue(),
            registerMoneyPaid:Ext.getCmp("registerMoneyPaid").getValue(),
            check:Ext.getCmp("check").getValue().check,
            contact:Ext.getCmp("contact").getValue(),
            contactTel:Ext.getCmp("contactTel").getValue(),
            mainWork:Ext.getCmp("mainWork").getValue(),
            operateArea:Ext.getCmp("operateArea").getValue(),
            operateAddr:Ext.getCmp("operateAddr").getValue(),
            assetOffice:Ext.getCmp("assetOffice").getValue().assetOffice,
            assetWarehouse:Ext.getCmp("assetWarehouse").getValue().assetWarehouse,
            assetProductline:Ext.getCmp("assetProductline").getValue().assetProductline,
            asset:Ext.getCmp("asset").getValue(),
            workTime:Ext.getCmp("workTime").getValue().workTime,
            employeeNum:Ext.getCmp("employeeNum").getValue().employeeNum,
            assureAgreement:me.getCheckBoxGroup("assureAgreement"),
            baseData:me.getCheckBoxGroup("baseData"),
            otherData:me.getCheckBoxGroup("otherData"),
            tradeBreed:Ext.getCmp("tradeBreed").getValue(),
            isTrade:Ext.getCmp("isTrade").getValue().isTrade,
            tradeReason:Ext.getCmp("tradeReason").getValue(),
            position:Ext.getCmp("position").getValue().position,
            planQuantity:Ext.getCmp("planQuantity").getValue(),
            tradePeriod:Ext.getCmp("tradePeriod").getValue(),
            influence:Ext.getCmp("influence").getValue(),
            interflow:Ext.getCmp("interflow").getValue(),
            risk:me.getCheckBoxGroup("risk"),
            riskDescribe:Ext.getCmp("riskDescribe").getValue(),
            riskKeepaway:Ext.getCmp("riskKeepaway").getValue(),
		};

		return Ext.JSON.encode(result);
	},

	getCheckBoxGroup:function(idName){
		var check=Ext.getCmp(idName).items;
		var values=[];

        for (var i = 0; i < check.length; i++) {
            if (check.items[i].checked) {
                values.push(check.items[i].inputValue);
            }
        }

        return values;
	},



	onOK : function(thenAdd) {
		var me = this;

        //console.log(me.getSaveData());return;

		Ext.getBody().mask("正在保存中...");
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/COCompany/editCreditAssess",
					method : "POST",
					params :{
						jsonStr: me.getSaveData()
					},
					callback : function(options, success, response) {
						Ext.getBody().unmask();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							if (data.success) {
								me.__saved = true;
								PSI.MsgBox.showInfo("成功保存数据", function() {
											me.close();
										});
							} else {
								PSI.MsgBox.showInfo(data.msg);
							}
						}
					}
				});
	},

	onWindowBeforeUnload : function(e) {
		return (window.event.returnValue = e.returnValue = '确认离开当前页面？');
	},

	onWndClose : function() {
		var me = this;

		Ext.get(window).un('beforeunload', me.onWindowBeforeUnload);

		if (me.__saved) {
			me.getParentForm().refreshGrid();
		}
	},

	onWndShow : function() {
		var me = this;
		me.__saved = false;

		Ext.get(window).on('beforeunload', me.onWindowBeforeUnload);

		var el = me.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL
							+ "Home/BizConfig/allConfigsWithExtData",
					params : {
						companyId : me.getCompanyId()
					},
					method : "POST",
					callback : function(options, success, response) {
						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							me.__storePW.add(data.extData.warehouse);
							me.__storeWS.add(data.extData.warehouse);

							for (var i = 0; i < data.dataList.length; i++) {
								var item = data.dataList[i];
								var editName = Ext.getCmp("editName" + item.id);
								if (editName) {
									editName.setValue(item.name);
								}
								var editValue = Ext.getCmp("editValue"
										+ item.id);
								if (editValue) {
									editValue.setValue(item.value);
								}
							}
						} else {
							PSI.MsgBox.showInfo("网络错误");
						}
						el.unmask();
					}
				});
	}
});