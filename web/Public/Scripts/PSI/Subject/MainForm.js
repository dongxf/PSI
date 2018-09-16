/**
 * 会计科目 - 主界面
 */
Ext.define("PSI.Subject.MainForm", {
			extend : "PSI.AFX.BaseMainExForm",

			/**
			 * 初始化组件
			 */
			initComponent : function() {
				var me = this;

				Ext.apply(me, {
							tbar : me.getToolbarCmp(),
							items : [{
										region : "west",
										width : 300,
										layout : "fit",
										border : 0,
										split : true,
										items : [me.getCompanyGrid()]
									}, {
										region : "center",
										xtype : "panel",
										layout : "fit",
										border : 0,
										items : me.getMainGrid()
									}]
						});

				me.callParent(arguments);

				me.refreshCompanyGrid();
			},

			getToolbarCmp : function() {
				var me = this;
				return [{
							text : "初始化国家标准科目",
							handler : me.onInit,
							scope : me
						}, "-", {
							text : "新增科目",
							handler : me.onAddSubject,
							scope : me
						}, "-", {
							text : "编辑科目",
							handler : me.onEditSubject,
							scope : me
						}, "-", {
							text : "删除科目",
							handler : me.onDeleteSubject,
							scope : me
						}, "-", {
							text : "关闭",
							handler : function() {
								me.closeWindow();
							}
						}];
			},

			refreshCompanyGrid : function() {
				var me = this;
				var el = Ext.getBody();
				var store = me.getCompanyGrid().getStore();
				el.mask(PSI.Const.LOADING);
				var r = {
					url : me.URL("Home/Subject/companyList"),
					callback : function(options, success, response) {
						store.removeAll();

						if (success) {
							var data = me.decodeJSON(response.responseText);
							store.add(data);
							if (store.getCount() > 0) {
								me.getCompanyGrid().getSelectionModel()
										.select(0);
							}
						}

						el.unmask();
					}
				};
				me.ajax(r);
			},

			getCompanyGrid : function() {
				var me = this;
				if (me.__companyGrid) {
					return me.__companyGrid;
				}

				var modelName = "PSI_Subject_Company";

				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["id", "code", "name"]
						});

				me.__companyGrid = Ext.create("Ext.grid.Panel", {
							cls : "PSI",
							header : {
								height : 30,
								title : me.formatGridHeaderTitle("公司")
							},
							forceFit : true,
							columnLines : true,
							columns : [{
										header : "公司编码",
										dataIndex : "code",
										menuDisabled : true,
										sortable : false,
										width : 70
									}, {
										header : "公司名称",
										dataIndex : "name",
										flex : 1,
										menuDisabled : true,
										sortable : false
									}],
							store : Ext.create("Ext.data.Store", {
										model : modelName,
										autoLoad : false,
										data : []
									}),
							listeners : {
								select : {
									fn : me.onCompanyGridSelect,
									scope : me
								}
							}
						});
				return me.__companyGrid;
			},

			onCompanyGridSelect : function() {
				var me = this;
				me.getMainGrid().setTitle(me.formatGridHeaderTitle("会计科目"));
				var item = me.getCompanyGrid().getSelectionModel()
						.getSelection();
				if (item == null || item.length != 1) {
					return;
				}

				var company = item[0];
				var title = Ext.String
						.format("{0} - 会计科目", company.get("name"));
				me.getMainGrid().setTitle(me.formatGridHeaderTitle(title));

				var store = me.getMainGrid().getStore();
				store.load();
			},

			onAddSubject : function() {
				var me = this;
				var item = me.getCompanyGrid().getSelectionModel()
						.getSelection();
				if (item == null || item.length != 1) {
					me.showInfo("没有选择公司");
					return;
				}

				var company = item[0];

				var form = Ext.create("PSI.Subject.EditForm", {
							parentForm : me,
							company : company
						});
				form.show();
			},

			onEditSubject : function() {
				var me = this;
				me.showInfo("TODO");
			},

			onDeleteSubject : function() {
				var me = this;
				me.showInfo("TODO");
			},

			getMainGrid : function() {
				var me = this;
				if (me.__mainGrid) {
					return me.__mainGrid;
				}

				var modelName = "PSISubject";
				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["id", "code", "name", "category", "leaf",
									"children", "isLeaf"]
						});

				var store = Ext.create("Ext.data.TreeStore", {
							model : modelName,
							proxy : {
								type : "ajax",
								actionMethods : {
									read : "POST"
								},
								url : me.URL("Home/Subject/subjectList")
							},
							listeners : {
								beforeload : {
									fn : function() {
										store.proxy.extraParams = me
												.getQueryParamForSubject();
									},
									scope : me
								}
							}
						});

				me.__mainGrid = Ext.create("Ext.tree.Panel", {
							cls : "PSI",
							header : {
								height : 30,
								title : me.formatGridHeaderTitle("会计科目")
							},
							store : store,
							rootVisible : false,
							useArrows : true,
							viewConfig : {
								loadMask : true
							},
							columns : {
								defaults : {
									sortable : false,
									menuDisabled : true,
									draggable : false
								},
								items : [{
											xtype : "treecolumn",
											text : "科目码",
											dataIndex : "code",
											width : 200
										}, {
											text : "科目名称",
											dataIndex : "name",
											width : 400
										}, {
											text : "分类",
											dataIndex : "category",
											width : 80,
											renderer : function(value) {
												if (value == 1) {
													return "资产";
												} else if (value == 2) {
													return "负债";
												} else if (value == 4) {
													return "所有者权益";
												} else if (value == 5) {
													return "成本";
												} else if (value == 6) {
													return "损益";
												} else {
													return "";
												}
											}
										}, {
											text : "末级科目",
											dataIndex : "isLeaf",
											width : 100
										}]
							}
						});

				return me.__mainGrid;
			},

			getQueryParamForSubject : function() {
				var me = this;
				var item = me.getCompanyGrid().getSelectionModel()
						.getSelection();
				if (item == null || item.length != 1) {
					return {};
				}

				var company = item[0];

				var result = {
					companyId : company.get("id")
				};

				return result;
			},

			onInit : function() {
				var me = this;
				var item = me.getCompanyGrid().getSelectionModel()
						.getSelection();
				if (item == null || item.length != 1) {
					me.showInfo("请选择要初始化科目的公司");
					return;
				}

				var company = item[0];

				var confirmFunc = function() {
					var el = Ext.getBody();
					el.mask("正在操作中...");
					var r = {
						url : me.URL("Home/Subject/init"),
						params : {
							id : company.get("id")
						},
						callback : function(options, success, response) {
							el.unmask();

							if (success) {
								var data = Ext.JSON
										.decode(response.responseText);
								if (data.success) {
									me.showInfo("成功完成初始化操作", function() {
												me.onCompanyGridSelect();
											});
								} else {
									me.showInfo(data.msg);
								}
							} else {
								me.showInfo("网络错误");
							}
						}
					};

					me.ajax(r);
				};

				var info = Ext.String.format(
						"请确认是否初始化<span style='color:red'>{0}</span>的科目",
						company.get("name"));
				me.confirm(info, confirmFunc);
			}
		});