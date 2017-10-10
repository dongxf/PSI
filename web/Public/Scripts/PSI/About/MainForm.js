/**
 * 关于窗体
 */
Ext.define("PSI.About.MainForm", {
	extend : 'Ext.window.Window',
	config : {
		productionName : "PSI",
		phpVersion : "",
		mySQLVersion : "",
		PSIDBVersion : "",
		PSIDBUpdateDT : ""
	},

	modal : true,
	closable : false,
	width : 400,
	layout : "fit",

	initComponent : function() {
		var me = this;

		Ext.apply(me, {
			header : {
				title : "<span style='font-size:120%'>关于 - "
						+ me.getProductionName() + "</span>",
				iconCls : "PSI-fid-9994",
				height : 40
			},
			height : 300,
			items : [{
				border : 0,
				xtype : "container",
				margin : "0 0 0 10",
				html : "<h1>欢迎使用"
						+ me.getProductionName()
						+ "</h1><p>当前版本："
						+ PSI.Const.VERSION
						+ "</p>"
						+ "<p>产品源码下载请访问  <a href='https://gitee.com/crm8000/PSI' target='_blank'>https://gitee.com/crm8000/PSI</a></p>"
						+ "<p>技术支持QQ群 414474186</p>" + "<p>PHP版本号:"
						+ me.getPhpVersion() + " , MySQL版本号: "
						+ me.getMySQLVersion() + "</p><p>数据库表结构版本号:"
						+ me.getPSIDBVersion() + "</p><p>数据库表结构更新时间:"
						+ me.getPSIDBUpdateDT() + "</p>"
			}],
			buttons : [{
						id : "buttonAboutFormOK",
						text : "关闭",
						handler : me.onOK,
						scope : me,
						iconCls : "PSI-button-ok"
					}],
			listeners : {
				show : {
					fn : me.onWndShow,
					scope : me
				}
			}
		});

		me.callParent(arguments);
	},

	onWndShow : function() {
		Ext.getCmp("buttonOK").focus();
	},

	onOK : function() {
		if (PSI.Const.MOT == "0") {
			window.location.replace(PSI.Const.BASE_URL);

		} else {
			window.close();

			if (!window.closed) {
				window.location.replace(PSI.Const.BASE_URL);
			}
		}
	}
});