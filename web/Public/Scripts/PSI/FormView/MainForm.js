/**
 * 表单视图 - 主界面
 */
Ext.define("PSI.FormView.MainForm", {
			extend : "Ext.panel.Panel",

			config : {
				formViewId : null,
				devMode : false
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

			onCloseForm : function() {
				var me = this;

				if (me.getDevMode()) {
					window.close();
					if (!window.closed) {
						window.location.replace(PSI.Const.BASE_URL);
					}
					return;
				}

				if (PSI.Const.MOT == "0") {
					window.location.replace(PSI.Const.BASE_URL);
				} else {
					window.close();

					if (!window.closed) {
						window.location.replace(PSI.Const.BASE_URL);
					}
				}
			},

			onTodo : function() {
				PSI.MsgBox.showInfo("TODO");
			},

			onHelp : function() {
				var me = this;

				var helpId = me.__md.helpId;
				if (!helpId) {
					return;
				}

				var url = Ext.String.format("/Home/Help/index?t={0}", helpId);
				window.open(me.URL(url));
			},

			initComponent : function() {
				var me = this;

				Ext.apply(me, {
							border : 0,
							tbar : {
								id : "PSI_FormView_MainForm_toolBar",
								xtype : "toolbar"
							},
							layout : "border",
							items : [{
										id : "PSI_FormView_MainForm_panelQueryCmp",
										region : "north",
										height : 0,
										header : false,
										collapsible : true,
										collapseMode : "mini",
										border : 0,
										layout : {
											type : "table",
											columns : 4
										}
									}, {
										id : "PSI_FormView_MainForm_panelMain",
										region : "center"
									}]
						});

				me.callParent(arguments);

				me.__toolBar = Ext.getCmp("PSI_FormView_MainForm_toolBar");
				me.__panelQueryCmp = Ext
						.getCmp("PSI_FormView_MainForm_panelQueryCmp");
				me.__panelMain = Ext.getCmp("PSI_FormView_MainForm_panelMain");

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
				if (!data) {
					return;
				}

				// 创建工具栏
				if (data.toolBar) {
					var toolBar = data.toolBar;

					for (var i = 0; i < toolBar.length; i++) {
						var item = toolBar[i];
						var text = item.text;
						if (text == "-") {
							me.__toolBar.add("-");
						} else {
							var handler = Ext.emptyFn;
							if (item.handler && me[item.handler]) {
								handler = me[item.handler];
							}

							var btn = {
								text : text
							};
							if (item.iconCls) {
								btn.iconCls = item.iconCls;
							}
							if (item.subButtons) {
								// 有子按钮
								btn.menu = [];
								for (var si = 0; si < item.subButtons.length; si++) {
									var b = item.subButtons[si];

									if (b.text == "-") {
										btn.menu.push("-");
									} else {
										var h = Ext.emptyFn;
										if (b.handler && me[b.handler]) {
											h = me[b.handler];
										}
										var subBtn = {
											text : b.text,
											handler : h,
											scope : me
										};
										if (b.iconCls) {
											subBtn.iconCls = b.iconCls;
										}
										btn.menu.push(subBtn);
									}
								}
							} else {
								Ext.apply(btn, {
											handler : handler,
											scope : me
										});
							}

							me.__toolBar.add(btn);
						}
					}
				}

				// 查询栏
				if (data.queryCmp) {
					var qcList = data.queryCmp;
					var len = qcList.length;
					var col = parseInt(data.queryCmpColCount);
					if (!col) {
						col = 4;
					}
					var rowHeight = 40;
					var height = Math.ceil(len / col) * rowHeight;

					me.__panelQueryCmp.setHeight(height);

					for (var i = 0; i < len; i++) {
						var qc = qcList[i];
						me.__panelQueryCmp.add({
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : qc.label,
									margin : "5, 0, 0, 0",
									xtype : qc.xtype
								});
					}
				}
			}
		});