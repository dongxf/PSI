/**
 * 表单视图 - 主界面
 */
Ext.define("PSI.FormView.MainForm", {
			extend : "Ext.panel.Panel",

			config : {
				formViewId : null
			},

			URL : function(url) {
				return PSI.Const.BASE_URL + url;
			},

			ajax : function(r) {
				if (!r.method) {
					r.method = "POST";
				}
				Ext.Ajax.request(r);
			},

			decodeJSON : function(str) {
				return Ext.JSON.decode(str);
			},

			initComponent : function() {
				var me = this;

				Ext.apply(me, {
							border : 0,
							tbar : {
								id : "PSI_FormView_MainForm_toolBar",
								xtype : "toolbar"
							}
						});

				me.callParent(arguments);
				
				me.__toolBar = Ext.getCmp("PSI_FormView_MainForm_toolBar");

				me.fetchMeatData();
			},

			fetchMeatData : function() {
				var me = this;
				var el = me.getEl();
				el && el.mask(PSI.Const.LOADING);
				me.ajax({
							url : me.URL("Home/FormView/getFormViewMetaData"),
							params : {
								viewId : me.getFormViewId()
							},
							callback : function(options, success, response) {
								if (success) {
									var data = me
											.decodeJSON(response.responseText);

									me.__md = data;

									me.initUI();
								}

								el && el.unmask();
							}
						});

			},

			initUI : function() {
				var me = this;
				var data = me.__md;
					debugger;

				// 创建工具栏
				if (data.toolBar) {
					var toolBar = data.toolBar;
					for (var i = 0; i < toolBar.length; i++) {
						me.__toolBar.add({
									text : toolBar[i]["text"]
								});
					}
				}
			}
		});