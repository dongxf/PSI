/**
 * 登录界面
 */
Ext.define("PSI.User.LoginForm", {
	extend : 'PSI.AFX.BaseDialogForm',

	config : {
		demoInfo : "",
		productionName : "",
		ip : "",
		cname : "",
		returnPage : ""
	},

	closable : false,
	width : 400,
	layout : "fit",
	defaultFocus : Ext.util.Cookies.get("PSI_user_login_name")
			? "editPassword"
			: "editLoginName",

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;

		Ext.apply(me, {
					height : me.getDemoInfo() == "" ? 140 : 200,
					header : {
						title : "<span style='font-size:120%'>登录 - "
								+ me.getProductionName() + "</span>",
						iconCls : "PSI-login",
						height : 40
					},
					items : [{
						id : "loginForm",
						xtype : "form",
						layout : {
							type : "table",
							columns : 1
						},
						height : "100%",
						border : 0,
						bodyPadding : 5,
						defaultType : 'textfield',
						fieldDefaults : {
							labelWidth : 60,
							labelAlign : "right",
							labelSeparator : "",
							msgTarget : 'side'
						},
						items : [{
									xtype : "hidden",
									name : "fromDevice",
									value : "web"
								}, {
									id : "editLoginName",
									width : 370,
									fieldLabel : "登录名",
									allowBlank : false,
									blankText : "没有输入登录名",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name : "loginName",
									value : me.getLoginNameFromCookie(),
									listeners : {
										specialkey : me.onEditLoginNameSpecialKey,
										scope : me
									}
								}, {
									id : "editPassword",
									fieldLabel : "密码",
									allowBlank : false,
									blankText : "没有输入密码",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									inputType : "password",
									name : "password",
									width : 370,
									listeners : {
										specialkey : me.onEditPasswordSpecialKey,
										scope : me
									}
								}, {
									xtype : "displayfield",
									value : me.getDemoInfo()
								}, {
									xtype : "hidden",
									name : "ip",
									value : me.getIp()
								}, {
									xtype : "hidden",
									name : "ipFrom",
									value : me.getCname()
								}],
						buttons : [{
									text : "登录",
									formBind : true,
									handler : me.onOK,
									scope : me,
									iconCls : "PSI-button-ok"
								}, {
									text : "帮助",
									iconCls : "PSI-help",
									handler : function() {
										window.open(PSI.Const.BASE_URL
												+ "/Home/Help/index?t=login");
									}
								}]
					}]
				});

		me.callParent(arguments);

		me.loginForm = Ext.getCmp("loginForm");

		me.editPassword = Ext.getCmp("editPassword");
		me.editLoginName = Ext.getCmp("editLoginName");
	},

	onEditLoginNameSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() === e.ENTER) {
			me.editPassword.focus();
		}
	},

	onEditPasswordSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() === e.ENTER) {
			if (me.loginForm.getForm().isValid()) {
				me.onOK();
			}
		}
	},

	/**
	 * 从Cookie中获得上次登录的用户登录名
	 * 
	 * @return {}
	 */
	getLoginNameFromCookie : function() {
		var loginName = Ext.util.Cookies.get("PSI_user_login_name");
		if (loginName) {
			return decodeURIComponent(loginName);
		} else {
			return "";
		}
	},

	/**
	 * 把登录名保存到Cookie中
	 */
	setLoginNameToCookie : function(loginName) {
		loginName = encodeURIComponent(loginName);
		var dt = Ext.Date.add(new Date(), Ext.Date.YEAR, 1)
		Ext.util.Cookies.set("PSI_user_login_name", loginName, dt);
	},

	onOK : function() {
		var me = this;

		var loginName = me.editLoginName.getValue();
		var f = me.loginForm;
		var el = f.getEl() || Ext.getBody();
		el.mask("系统登录中...");

		var r = {
			url : me.URL("/Home/User/loginPOST"),
			method : "POST",
			success : function(form, action) {
				me.setLoginNameToCookie(loginName);

				var returnPage = me.getReturnPage();
				if (returnPage) {
					location.replace(returnPage);
				} else {
					location.replace(PSI.Const.BASE_URL);
				}
			},
			failure : function(form, action) {
				el.unmask();
				PSI.MsgBox.showInfo(action.result.msg, function() {
							var editPassword = me.editPassword;
							editPassword.setValue(null);
							editPassword.clearInvalid();
							editPassword.focus();
						});
			}
		};

		f.getForm().submit(r);
	}
});