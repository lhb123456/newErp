/**
 * 信用评估信息填写
 */
Ext.define("PSI.COCompany.EditRankAssess", {
	extend : "PSI.AFX.BaseDialogForm",

	config : {
		companyId : null,
        credit:null,
		permission:null
	},

	initComponent : function() {
		var me = this;
        this.adding = me.getEntity() == null;

		var buttons = [];

		if(me.getCredit()==null){
            var permission=me.getParentForm().getPermission();
		}else{
            var permission=me.getPermission();
		}


		buttons.push({
					id:"commitButton",
					hidden:true,
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
										{boxLabel: '供应商',id:"companyType1", name: 'supplier',width:120,inputValue:1},
										{boxLabel: '客户',id:"companyType2", name: 'customer',width:120,inputValue:2}
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
										{id:"assessTimes1",name: 'assessTimes',inputValue: 1,boxLabel: '初次评估',width:120,checked:true,readOnly:true},
										{id:"assessTimes2",name: 'assessTimes',inputValue: 2,boxLabel: '二次评估以上',width:120,readOnly:true}
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
										{name: 'companyAssetType',id:'companyAssetType1',inputValue: 1,boxLabel: '国有独资',width:80,checked:true},
										{name: 'companyAssetType',id:'companyAssetType2',inputValue: 2,boxLabel: '上市公司',width:100},
										{name: 'companyAssetType',id:'companyAssetType3',inputValue: 3,boxLabel: '中外合资',width:100},
										{name: 'companyAssetType',id:'companyAssetType4',inputValue: 4,boxLabel: '外商独资',width:100},
										{name: 'companyAssetType',id:'companyAssetType5',inputValue: 5,boxLabel: '民营独资',width:100},
										{name: 'companyAssetType',id:'companyAssetType6',inputValue: 6,boxLabel: '民营股份',width:100},
										{name: 'companyAssetType',id:'companyAssetType7',inputValue: 7,boxLabel: '其他',width:100}
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
										{name: 'companyAddrType',id:'companyAddrType0',inputValue: 0,boxLabel: '无',width:80,checked:true},
										{name: 'companyAddrType',id:'companyAddrType1',inputValue: 1,boxLabel: '离岸公司',width:80},
										{name: 'companyAddrType',id:'companyAddrType2',inputValue: 2,boxLabel: '在岸公司',width:80}
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
										{boxLabel: '制造商', name: 'companyTradeType',id:'companyTradeType1',inputValue: 1,width:120},
										{boxLabel: '贸易商', name: 'companyTradeType',id:'companyTradeType2',inputValue: 2,width:120}
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
										{name: 'companyStrength',id:'companyStrength1',inputValue: 1,boxLabel: '全球500强',width:100,checked:true},
										{name: 'companyStrength',id:'companyStrength2',inputValue: 2,boxLabel: '全国500强',width:100},
										{name: 'companyStrength',id:'companyStrength3',inputValue: 3,boxLabel: '行业100强',width:100}
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
										{name: 'check',id:'check1',inputValue: 1,boxLabel: '正常',width:100,checked:true},
										{name: 'check',id:'check0',inputValue: 0,boxLabel: '异常',width:100}
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
										{name: 'assetOffice',id:'assetOffice0',inputValue: 0,boxLabel: '自有',width:100},
										{name: 'assetOffice',id:'assetOffice1',inputValue: 1,boxLabel: '租赁',width:100,checked:true}
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
										{name: 'assetWarehouse',id:'assetWarehouse0',inputValue: 0,boxLabel: '自有',width:100},
										{name: 'assetWarehouse',id:'assetWarehouse1',inputValue: 1,boxLabel: '租赁',width:75,checked:true}
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
										{name: 'assetProductline',id:'assetProductline0',inputValue: 0,boxLabel: '自有',width:100},
										{name: 'assetProductline',id:'assetProductline1',inputValue: 1,boxLabel: '租赁',width:100,checked:true}
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
										{name: 'workTime',id:'workTime1',inputValue: 1,boxLabel: '0-3年',width:80,checked:true},
										{name: 'workTime',id:'workTime2',inputValue: 2,boxLabel: '3-5年',width:80},
										{name: 'workTime',id:'workTime3',inputValue: 3,boxLabel: '5年以上',width:80}
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
										{name: 'employeeNum',id:'employeeNum1',inputValue: 1,boxLabel: '100人以下',width:80},
										{name: 'employeeNum',id:'employeeNum2',inputValue: 2,boxLabel: '100-300人',width:80,checked:true},
										{name: 'employeeNum',id:'employeeNum3',inputValue: 3,boxLabel: '300人以上',width:80}
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
										{boxLabel: '公司担保金额', name: 'assureAgreement',id:'assureAgreement1',inputValue: 1,width:140},
										{boxLabel: '个人担保金额', name: 'assureAgreement',id:'assureAgreement2',inputValue: '2',width:140},
                                        {boxLabel: '资产担保金额', name: 'assureAgreement',id:'assureAgreement3',inputValue: '3',width:140},
                                        {boxLabel: '其他担保金额', name: 'assureAgreement',id:'assureAgreement4',inputValue: '4',width:140}
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
										{boxLabel: '营业执照', name: 'baseData',id:'baseData1',inputValue: 1,width:100},
										{boxLabel: '财务报表', name: 'baseData',id:'baseData2',inputValue: 2,width:100},
										{boxLabel: '其他', name: 'baseData',id:'baseData3',inputValue: 3,width:80}
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
										{boxLabel: '资质证书', name: 'otherData',id:'otherData1',inputValue: 1,width:140},
										{boxLabel: '第三方报告', name: 'otherData',id:'otherData2',inputValue: 2,width:140}
									]
								}],
							listeners: {
								'show': function (t, eOpts) {

									if(me.getCredit()){
                                        Ext.getCmp("commitButton").hide();
									}else{
                                        if(permission.creditEditBase==1){
                                            if(!me.adding&&(me.getEntity().get("tableStatus")==0||me.getEntity().get("tableStatus")==4000)){
                                                Ext.getCmp("commitButton").hide();
                                            }else{
                                                Ext.getCmp("commitButton").show();
                                            }
                                        }else{
                                            Ext.getCmp("commitButton").hide();
                                        }
									}
								}
							}
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
									items: [
										{name: 'isTrade',id:'isTrade1',inputValue: 1,boxLabel: '是',width:100,checked:true},
										{name: 'isTrade',id:'isTrade0',inputValue: 0,boxLabel: '否',width:100}
									]
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
										{name: 'position',inputValue: 1,id:'inputValue1',boxLabel: '战略供应商/客户',width:150,checked:true},
										{name: 'position',inputValue: 2,id:'inputValue2',boxLabel: '核心供应商/分销大客户',width:180},
										{name: 'position',inputValue: 3,id:'inputValue3',boxLabel: '稳定供应商/分销中小客户',width:180 },
										{name: 'position',inputValue: 4,id:'inputValue4',boxLabel: '行情投机供应商/客户',width:150}
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
								}],
						listeners: {
							'show': function (t, eOpts) {
                                if(me.getCredit()){
                                    Ext.getCmp("commitButton").hide();
                                }else{
                                    if(permission.creditEditAnalysis==1){
                                        if(!me.adding&&(me.getEntity().get("tableStatus")==0||me.getEntity().get("tableStatus")==4000)){
                                            Ext.getCmp("commitButton").hide();
                                        }else{
                                            Ext.getCmp("commitButton").show();
                                        }
                                    }else{
                                        Ext.getCmp("commitButton").hide();
                                    }
                                }

							}
						}

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
										{boxLabel: '信用违约', name: 'risk',id:'risk1',inputValue: 1,width:80},
										{boxLabel: '盈利差', name: 'risk',id:'risk2',inputValue: 2,width:80},
										{boxLabel: '高负债', name: 'risk',id:'risk3',inputValue: 3,width:80},
										{boxLabel: '融资差', name: 'risk',id:'risk4',inputValue: 4,width:80},
                                        {boxLabel: '高库存', name: 'risk',id:'risk5',inputValue: 5,width:80},
                                        {boxLabel: '地域环境', name: 'risk',id:'risk6',inputValue: 6,width:80},
                                        {boxLabel: '对外担保', name: 'risk',id:'risk7',inputValue: 7,width:80},
                                        {boxLabel: '安全责任', name: 'risk',id:'risk8',inputValue: 8,width:80},
                                        {boxLabel: '管理差', name: 'risk',id:'risk9',inputValue: 9,width:80},
                                        {boxLabel: '工商税务', name: 'risk',id:'risk10',inputValue: 10,width:80},
                                        {boxLabel: '法务诉讼', name: 'risk',id:'risk11',inputValue: 11,width:80},
                                        {boxLabel: '政策法规', name: 'risk',id:'risk12',inputValue: 12,width:80},
                                        {boxLabel: '负责人有不良习惯', name: 'risk',id:'risk13',inputValue: 13,width:120},
                                        {boxLabel: '其他', name: 'risk',id:'risk14',inputValue: 14,width:80}
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
								}],
							listeners: {
								'show': function (t, eOpts) {
                                    if(me.getCredit()){
                                        Ext.getCmp("commitButton").hide();
                                    }else{
                                        if(permission.creditEditRisk==1){
                                            if(permission.creditEditAnalysis==0&&(!me.adding&&me.getEntity().get("tableStatus")<2000
                                                    ||me.getEntity().get("tableStatus")==4000)){
                                                Ext.getCmp("commitButton").hide();
                                            }else{
                                                Ext.getCmp("commitButton").show();
                                            }

                                        }else{
                                            Ext.getCmp("commitButton").hide();
                                        }
                                    }

								}
							}
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
			tableStatus:!me.adding?me.getEntity().get("tableStatus"):1000
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

	setBoxGroup:function (idName,value) {
        var check=Ext.getCmp(idName).items;

        for (var i = 0; i <check.length; i++) {
			var indexNum=check.items[i].inputValue;

            if(value.indexOf(indexNum)>-1){
				Ext.getCmp(idName+indexNum).setValue(true)
			}
        }
    },

	setBaseReadOnly:function (bool) {

		Ext.getCmp("companyName").setDisabled(bool);
		Ext.getCmp("companyType").setDisabled(bool);
		Ext.getCmp("assessTimes").setDisabled(bool);
		Ext.getCmp("limit").setDisabled(bool);
		Ext.getCmp("companyAssetType").setDisabled(bool);
		Ext.getCmp("companyAddrType").setDisabled(bool);
		Ext.getCmp("otherCompany").setDisabled(bool);
		Ext.getCmp("companyTradeType").setDisabled(bool);
		Ext.getCmp("companyStrength").setDisabled(bool);
		Ext.getCmp("registerAddr").setDisabled(bool);
		Ext.getCmp("legalPerson").setDisabled(bool);
		Ext.getCmp("registerMoneySubscribe").setDisabled(bool);
		Ext.getCmp("registerMoneyPaid").setDisabled(bool);
		Ext.getCmp("check").setDisabled(bool);
		Ext.getCmp("contact").setDisabled(bool);
		Ext.getCmp("contactTel").setDisabled(bool);
		Ext.getCmp("mainWork").setDisabled(bool);
		Ext.getCmp("operateAddr").setDisabled(bool);
		Ext.getCmp("operateArea").setDisabled(bool);
		Ext.getCmp("assetOffice").setDisabled(bool);
		Ext.getCmp("assetWarehouse").setDisabled(bool);
		Ext.getCmp("assetProductline").setDisabled(bool);
		Ext.getCmp("asset").setDisabled(bool);
		Ext.getCmp("workTime").setDisabled(bool);
		Ext.getCmp("employeeNum").setDisabled(bool);
		Ext.getCmp("assureAgreement").setDisabled(bool);
		Ext.getCmp("baseData").setDisabled(bool);
		Ext.getCmp("otherData").setDisabled(bool);

    },

    setAnalysisReadOnly:function (bool) {
        Ext.getCmp("tradeBreed").setDisabled(bool);
        Ext.getCmp("isTrade").setDisabled(bool);
        Ext.getCmp("tradeReason").setDisabled(bool);
        Ext.getCmp("position").setDisabled(bool);
        Ext.getCmp("planQuantity").setDisabled(bool);
        Ext.getCmp("tradePeriod").setDisabled(bool);
        Ext.getCmp("influence").setDisabled(bool);
        Ext.getCmp("interflow").setDisabled(bool);

    },

    setRiskReadOnly:function (bool) {
        Ext.getCmp("risk").setDisabled(bool);
        Ext.getCmp("riskDescribe").setDisabled(bool);
        Ext.getCmp("riskKeepaway").setDisabled(bool);
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
			me.getParentForm().refreshMainGrid();
		}
	},

	setItemReadOnly:function () {
		var me=this;
        if(me.getCredit()==null){
            var permission=me.getParentForm().getPermission();
        }else{
            var permission=me.getPermission();
        }
        me.setBaseReadOnly(true);
        me.setAnalysisReadOnly(true);
        me.setRiskReadOnly(true);

		if(permission.creditEditBase==1){
			me.setBaseReadOnly(false);
		}

        if(permission.creditEditAnalysis==1){
            me.setAnalysisReadOnly(false);
        }

        if(permission.creditEditRisk==1){
            me.setRiskReadOnly(false);
        }

    },

	onWndShow : function() {
		var me = this;
		 me.setItemReadOnly();		//通过权限设置可否编辑

		if(me.getCredit()){
            me.setBaseReadOnly(true);
            me.setAnalysisReadOnly(true);
            me.setRiskReadOnly(true);
            var id=me.getCredit().id
		}else if(!me.adding){
			var id=me.getEntity().get("id");
			var tableStatus=me.getEntity().get("tableStatus");
			if(tableStatus>0&&tableStatus<4000&&me.getParentForm().getPermission().creditEdit==1){
				if(me.getParentForm().getPermission().creditEditBase==1){
					Ext.getCmp("commitButton").show();
				}
			}else{
				me.setBaseReadOnly(true);
				me.setAnalysisReadOnly(true);
				me.setRiskReadOnly(true);
			}
		}else{
			var id="";
            Ext.getCmp("commitButton").show();
		}

		me.__saved = false;

		Ext.get(window).on('beforeunload', me.onWindowBeforeUnload);

		var el = me.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL
							+ "Home/COCompany/getAssessInfo",
					params : {
						id : id
					},
					method : "POST",
					callback : function(options, success, response) {
						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							if(!me.adding||me.getCredit()){
                                Ext.getCmp("hiddenId").setValue(data.id);
                                Ext.getCmp("companyName").setValue(data.companyName);
                                Ext.getCmp("limit").setValue(data.limit);
                                Ext.getCmp("otherCompany").setValue(data.otherCompany);
                                Ext.getCmp("registerAddr").setValue(data.registerAddr);
                                Ext.getCmp("legalPerson").setValue(data.legalPerson);
                                Ext.getCmp("registerMoneySubscribe").setValue(data.registerMoneySubscribe);
                                Ext.getCmp("registerMoneyPaid").setValue(data.registerMoneyPaid);
                                Ext.getCmp("contact").setValue(data.contact);
                                Ext.getCmp("contactTel").setValue(data.contactTel);
                                Ext.getCmp("mainWork").setValue(data.mainWork);
                                Ext.getCmp("operateArea").setValue(data.operateArea);
                                Ext.getCmp("operateAddr").setValue(data.operateAddr);
                                Ext.getCmp("asset").setValue(data.asset);
                                Ext.getCmp("tradeBreed").setValue(data.tradeBreed);
                                Ext.getCmp("tradeReason").setValue(data.tradeReason);
                                Ext.getCmp("planQuantity").setValue(data.planQuantity);
                                Ext.getCmp("tradePeriod").setValue(data.tradePeriod);
                                Ext.getCmp("influence").setValue(data.influence);
                                Ext.getCmp("interflow").setValue(data.interflow);
                                Ext.getCmp("riskDescribe").setValue(data.riskDescribe);
                                Ext.getCmp("riskKeepaway").setValue(data.riskKeepaway);

                                if(data.assessTimes>=2){
                                    Ext.getCmp("assessTimes2").setValue(true);
                                }else{
                                    Ext.getCmp("assessTimes1").setValue(true);
                                }

                                me.setBoxGroup("companyType",data.companyType);
                                me.setBoxGroup("companyAssetType",data.companyAssetType);
                                me.setBoxGroup("companyAddrType",data.companyAddrType);
                                me.setBoxGroup("companyTradeType",data.companyTradeType);
                                me.setBoxGroup("companyStrength",data.companyStrength);
                                me.setBoxGroup("check",data.check);
                                me.setBoxGroup("assetOffice",data.assetOffice);
                                me.setBoxGroup("assetWarehouse",data.assetWarehouse);
                                me.setBoxGroup("assetProductline",data.assetProductline);
                                me.setBoxGroup("workTime",data.workTime);
                                me.setBoxGroup("employeeNum",data.employeeNum);
                                me.setBoxGroup("assureAgreement",data.assureAgreement);
                                me.setBoxGroup("baseData",data.baseData);
                                me.setBoxGroup("otherData",data.otherData);
                                me.setBoxGroup("isTrade",data.isTrade);
                                me.setBoxGroup("risk",data.risk);
							}


						} else {
							PSI.MsgBox.showInfo("网络错误");
						}
						el.unmask();
					}
				});
	}
});