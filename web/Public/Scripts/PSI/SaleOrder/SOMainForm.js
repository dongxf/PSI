/**
 * 销售订单 - 主界面
 * 
 * @author 李静波
 */
Ext.define("PSI.SaleOrder.SOMainForm", {
	extend : "PSI.AFX.BaseMainExForm",

	config : {
		permission : null
	},

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;

		Ext.apply(me, {
			tbar : me.getToolbarCmp(),
			items : [{
						id : "panelQueryCmp",
						region : "north",
						height : 65,
						layout : "fit",
						header : false,
						collapsible : true,
						collapseMode : "mini",
						border : 0,
						layout : {
							type : "table",
							columns : 4
						},
						items : me.getQueryCmp()
					}, {
						region : "center",
						layout : "border",
						border : 0,
						items : [{
									region : "north",
									height : "40%",
									split : true,
									layout : "fit",
									border : 0,
									items : [me.getMainGrid()]
								}, {
									region : "center",
									layout : "fit",
									xtype : "tabpanel",
									border : 0,
									items : [me.getDetailGrid(), me.getWSGrid()]
								}]
					}]
		});

		me.callParent(arguments);

		me.refreshMainGrid();
	},

	/**
	 * 工具栏
	 */
	getToolbarCmp : function() {
		var me = this;
		return [{
					text : "新建销售订单",
					id : "buttonAdd",
					hidden : me.getPermission().add == "0",
					scope : me,
					handler : me.onAddBill
				}, {
					hidden : me.getPermission().add == "0",
					xtype : "tbseparator"
				}, {
					text : "编辑销售订单",
					hidden : me.getPermission().edit == "0",
					scope : me,
					handler : me.onEditBill,
					id : "buttonEdit"
				}, {
					hidden : me.getPermission().edit == "0",
					xtype : "tbseparator"
				}, {
					text : "删除销售订单",
					hidden : me.getPermission().del == "0",
					scope : me,
					handler : me.onDeleteBill,
					id : "buttonDelete"
				}, {
					hidden : me.getPermission().del == "0",
					xtype : "tbseparator",
					id : "tbseparator1"
				}, {
					text : "审核",
					hidden : me.getPermission().confirm == "0",
					scope : me,
					handler : me.onCommit,
					id : "buttonCommit"
				}, {
					text : "取消审核",
					hidden : me.getPermission().confirm == "0",
					scope : me,
					handler : me.onCancelConfirm,
					id : "buttonCancelConfirm"
				}, {
					hidden : me.getPermission().confirm == "0",
					xtype : "tbseparator",
					id : "tbseparator2"
				}, {
					text : "生成销售出库单",
					hidden : me.getPermission().genWSBill == "0",
					scope : me,
					handler : me.onGenWSBill,
					id : "buttonGenWSBill"
				}, {
					hidden : me.getPermission().genWSBill == "0",
					xtype : "tbseparator"
				}, {
					text : "导出",
					hidden : me.getPermission().genPDF == "0",
					menu : [{
								text : "单据生成pdf",
								iconCls : "PSI-button-pdf",
								id : "buttonPDF",
								scope : me,
								handler : me.onPDF
							}]
				}, {
					hidden : me.getPermission().genPDF == "0",
					xtype : "tbseparator"
				}, {
					text : "打印",
					hidden : me.getPermission().print == "0",
					menu : [{
								text : "打印预览",
								iconCls : "PSI-button-print-preview",
								scope : me,
								handler : me.onPrintPreview
							}, "-", {
								text : "直接打印",
								iconCls : "PSI-button-print",
								scope : me,
								handler : me.onPrint
							}]
				}, {
					xtype : "tbseparator",
					hidden : me.getPermission().print == "0"
				}, {
					text : "帮助",
					handler : function() {
						window.open(me.URL("/Home/Help/index?t=sobill"));
					}
				}, "-", {
					text : "关闭",
					handler : function() {
						me.closeWindow();
					}
				}];
	},

	/**
	 * 查询条件
	 */
	getQueryCmp : function() {
		var me = this;
		return [{
			id : "editQueryBillStatus",
			xtype : "combo",
			queryMode : "local",
			editable : false,
			valueField : "id",
			labelWidth : 60,
			labelAlign : "right",
			labelSeparator : "",
			fieldLabel : "状态",
			margin : "5, 0, 0, 0",
			store : Ext.create("Ext.data.ArrayStore", {
						fields : ["id", "text"],
						data : [[-1, "全部"], [0, "待审核"], [1000, "已审核"],
								[2000, "部分出库"], [3000, "全部出库"]]
					}),
			value : -1
		}, {
			id : "editQueryRef",
			labelWidth : 60,
			labelAlign : "right",
			labelSeparator : "",
			fieldLabel : "单号",
			margin : "5, 0, 0, 0",
			xtype : "textfield"
		}, {
			id : "editQueryFromDT",
			xtype : "datefield",
			margin : "5, 0, 0, 0",
			format : "Y-m-d",
			labelAlign : "right",
			labelSeparator : "",
			fieldLabel : "交货日期（起）"
		}, {
			id : "editQueryToDT",
			xtype : "datefield",
			margin : "5, 0, 0, 0",
			format : "Y-m-d",
			labelAlign : "right",
			labelSeparator : "",
			fieldLabel : "交货日期（止）"
		}, {
			id : "editQueryCustomer",
			xtype : "psi_customerfield",
			labelAlign : "right",
			labelSeparator : "",
			labelWidth : 60,
			margin : "5, 0, 0, 0",
			fieldLabel : "客户"
		}, {
			id : "editQueryReceivingType",
			labelAlign : "right",
			labelSeparator : "",
			fieldLabel : "收款方式",
			labelWidth : 60,
			margin : "5, 0, 0, 0",
			xtype : "combo",
			queryMode : "local",
			editable : false,
			valueField : "id",
			store : Ext.create("Ext.data.ArrayStore", {
						fields : ["id", "text"],
						data : [[-1, "全部"], [0, "记应收账款"], [1, "现金收款"]]
					}),
			value : -1
		}, {
			xtype : "container",
			items : [{
						xtype : "button",
						text : "查询",
						width : 100,
						height : 26,
						margin : "5 0 0 10",
						handler : me.onQuery,
						scope : me
					}, {
						xtype : "button",
						text : "清空查询条件",
						width : 100,
						height : 26,
						margin : "5, 0, 0, 10",
						handler : me.onClearQuery,
						scope : me
					}]
		}, {
			xtype : "container",
			items : [{
						xtype : "button",
						text : "隐藏查询条件栏",
						width : 130,
						height : 26,
						iconCls : "PSI-button-hide",
						margin : "5 0 0 10",
						handler : function() {
							Ext.getCmp("panelQueryCmp").collapse();
						},
						scope : me
					}]
		}];
	},

	/**
	 * 销售订单主表
	 */
	getMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSISOBill";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "ref", "customerName", "contact", "tel",
							"fax", "inputUserName", "bizUserName",
							"billStatus", "goodsMoney", "dateCreated",
							"receivingType", "tax", "moneyWithTax", "dealDate",
							"dealAddress", "orgName", "confirmUserName",
							"confirmDate", "billMemo"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : [],
					pageSize : 20,
					proxy : {
						type : "ajax",
						actionMethods : {
							read : "POST"
						},
						url : me.URL("Home/Sale/sobillList"),
						reader : {
							root : 'dataList',
							totalProperty : 'totalCount'
						}
					}
				});
		store.on("beforeload", function() {
					store.proxy.extraParams = me.getQueryParam();
				});
		store.on("load", function(e, records, successful) {
					if (successful) {
						me.gotoMainGridRecord(me.__lastId);
					}
				});

		me.__mainGrid = Ext.create("Ext.grid.Panel", {
					cls : "PSI",
					viewConfig : {
						enableTextSelection : true
					},
					border : 1,
					columnLines : true,
					columns : [{
								xtype : "rownumberer",
								width : 50
							}, {
								header : "状态",
								dataIndex : "billStatus",
								menuDisabled : true,
								sortable : false,
								width : 80,
								renderer : function(value) {
									if (value == 0) {
										return "<span style='color:red'>待审核</span>";
									} else if (value == 1000) {
										return "已审核";
									} else if (value == 2000) {
										return "<span style='color:green'>部分出库</span>";
									} else if (value == 3000) {
										return "全部出库";
									} else {
										return "";
									}
								}
							}, {
								header : "销售订单号",
								dataIndex : "ref",
								width : 110,
								menuDisabled : true,
								sortable : false
							}, {
								header : "交货日期",
								dataIndex : "dealDate",
								menuDisabled : true,
								sortable : false
							}, {
								header : "交货地址",
								dataIndex : "dealAddress",
								menuDisabled : true,
								sortable : false
							}, {
								header : "客户",
								dataIndex : "customerName",
								width : 300,
								menuDisabled : true,
								sortable : false
							}, {
								header : "客户联系人",
								dataIndex : "contact",
								menuDisabled : true,
								sortable : false
							}, {
								header : "客户电话",
								dataIndex : "tel",
								menuDisabled : true,
								sortable : false
							}, {
								header : "客户传真",
								dataIndex : "fax",
								menuDisabled : true,
								sortable : false
							}, {
								header : "销售金额",
								dataIndex : "goodsMoney",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}, {
								header : "税金",
								dataIndex : "tax",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}, {
								header : "价税合计",
								dataIndex : "moneyWithTax",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}, {
								header : "收款方式",
								dataIndex : "receivingType",
								menuDisabled : true,
								sortable : false,
								width : 100,
								renderer : function(value) {
									if (value == 0) {
										return "记应收账款";
									} else if (value == 1) {
										return "现金收款";
									} else {
										return "";
									}
								}
							}, {
								header : "业务员",
								dataIndex : "bizUserName",
								menuDisabled : true,
								sortable : false
							}, {
								header : "组织机构",
								dataIndex : "orgName",
								menuDisabled : true,
								sortable : false
							}, {
								header : "制单人",
								dataIndex : "inputUserName",
								menuDisabled : true,
								sortable : false
							}, {
								header : "制单时间",
								dataIndex : "dateCreated",
								menuDisabled : true,
								sortable : false,
								width : 140
							}, {
								header : "审核人",
								dataIndex : "confirmUserName",
								menuDisabled : true,
								sortable : false
							}, {
								header : "审核时间",
								dataIndex : "confirmDate",
								menuDisabled : true,
								sortable : false,
								width : 140
							}, {
								header : "备注",
								dataIndex : "billMemo",
								menuDisabled : true,
								sortable : false
							}],
					store : store,
					bbar : ["->", {
								id : "pagingToobar",
								xtype : "pagingtoolbar",
								border : 0,
								store : store
							}, "-", {
								xtype : "displayfield",
								value : "每页显示"
							}, {
								id : "comboCountPerPage",
								xtype : "combobox",
								editable : false,
								width : 60,
								store : Ext.create("Ext.data.ArrayStore", {
											fields : ["text"],
											data : [["20"], ["50"], ["100"],
													["300"], ["1000"]]
										}),
								value : 20,
								listeners : {
									change : {
										fn : function() {
											store.pageSize = Ext
													.getCmp("comboCountPerPage")
													.getValue();
											store.currentPage = 1;
											Ext.getCmp("pagingToobar")
													.doRefresh();
										},
										scope : me
									}
								}
							}, {
								xtype : "displayfield",
								value : "条记录"
							}],
					listeners : {
						select : {
							fn : me.onMainGridSelect,
							scope : me
						},
						itemdblclick : {
							fn : me.getPermission().edit == "1"
									? me.onEditBill
									: Ext.emptyFn,
							scope : me
						}
					}
				});

		return me.__mainGrid;
	},

	/**
	 * 销售订单明细记录
	 */
	getDetailGrid : function() {
		var me = this;
		if (me.__detailGrid) {
			return me.__detailGrid;
		}

		var modelName = "PSISOBillDetail";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "goodsCode", "goodsName", "goodsSpec",
							"unitName", "goodsCount", "goodsMoney",
							"goodsPrice", "taxRate", "tax", "moneyWithTax",
							"wsCount", "leftCount", "memo"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : []
				});

		me.__detailGrid = Ext.create("Ext.grid.Panel", {
					cls : "PSI",
					title : "销售订单明细",
					viewConfig : {
						enableTextSelection : true
					},
					columnLines : true,
					columns : [Ext.create("Ext.grid.RowNumberer", {
										text : "序号",
										width : 40
									}), {
								header : "商品编码",
								dataIndex : "goodsCode",
								menuDisabled : true,
								sortable : false,
								width : 120
							}, {
								header : "商品名称",
								dataIndex : "goodsName",
								menuDisabled : true,
								sortable : false,
								width : 200
							}, {
								header : "规格型号",
								dataIndex : "goodsSpec",
								menuDisabled : true,
								sortable : false,
								width : 200
							}, {
								header : "销售数量",
								dataIndex : "goodsCount",
								menuDisabled : true,
								sortable : false,
								align : "right"
							}, {
								header : "出库数量",
								dataIndex : "wsCount",
								menuDisabled : true,
								sortable : false,
								align : "right"
							}, {
								header : "未出库数量",
								dataIndex : "leftCount",
								menuDisabled : true,
								sortable : false,
								align : "right",
								renderer : function(value) {
									if (value > 0) {
										return "<span style='color:red'>"
												+ value + "</span>";
									} else {
										return value;
									}
								}
							}, {
								header : "单位",
								dataIndex : "unitName",
								menuDisabled : true,
								sortable : false,
								width : 60
							}, {
								header : "销售单价",
								dataIndex : "goodsPrice",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}, {
								header : "销售金额",
								dataIndex : "goodsMoney",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}, {
								header : "税率(%)",
								dataIndex : "taxRate",
								menuDisabled : true,
								sortable : false,
								xtype : "numbercolumn",
								format : "0",
								align : "right"
							}, {
								header : "税金",
								dataIndex : "tax",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}, {
								header : "价税合计",
								dataIndex : "moneyWithTax",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}, {
								header : "备注",
								dataIndex : "memo",
								menuDisabled : true,
								sortable : false,
								width : 120
							}],
					store : store
				});

		return me.__detailGrid;
	},

	/**
	 * 刷新销售订单主表记录
	 */
	refreshMainGrid : function(id) {
		var me = this;

		Ext.getCmp("buttonEdit").setDisabled(true);
		Ext.getCmp("buttonDelete").setDisabled(true);
		Ext.getCmp("buttonCommit").setDisabled(true);
		Ext.getCmp("buttonCancelConfirm").setDisabled(true);
		Ext.getCmp("buttonGenWSBill").setDisabled(true);

		var gridDetail = me.getDetailGrid();
		gridDetail.setTitle("销售订单明细");
		gridDetail.getStore().removeAll();

		Ext.getCmp("pagingToobar").doRefresh();
		me.__lastId = id;
	},

	/**
	 * 新增销售订单
	 */
	onAddBill : function() {
		var me = this;

		var form = Ext.create("PSI.SaleOrder.SOEditForm", {
					parentForm : me
				});
		form.show();
	},

	/**
	 * 编辑销售订单
	 */
	onEditBill : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("没有选择要编辑的销售订单");
			return;
		}
		var bill = item[0];

		var form = Ext.create("PSI.SaleOrder.SOEditForm", {
					parentForm : me,
					entity : bill
				});
		form.show();
	},

	/**
	 * 删除销售订单
	 */
	onDeleteBill : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("请选择要删除的销售订单");
			return;
		}

		var bill = item[0];

		if (bill.get("billStatus") > 0) {
			me.showInfo("当前销售订单已经审核，不能删除");
			return;
		}

		var store = me.getMainGrid().getStore();
		var index = store.findExact("id", bill.get("id"));
		index--;
		var preIndex = null;
		var preItem = store.getAt(index);
		if (preItem) {
			preIndex = preItem.get("id");
		}

		var info = "请确认是否删除销售订单: <span style='color:red'>" + bill.get("ref")
				+ "</span>";
		var funcConfirm = function() {
			var el = Ext.getBody();
			el.mask("正在删除中...");
			var r = {
				url : me.URL("Home/Sale/deleteSOBill"),
				params : {
					id : bill.get("id")
				},
				callback : function(options, success, response) {
					el.unmask();

					if (success) {
						var data = me.decodeJSON(response.responseText);
						if (data.success) {
							me.showInfo("成功完成删除操作", function() {
										me.refreshMainGrid(preIndex);
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

		me.confirm(info, funcConfirm);
	},

	onMainGridSelect : function() {
		var me = this;
		me.getDetailGrid().setTitle("销售订单明细");
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			Ext.getCmp("buttonEdit").setDisabled(true);
			Ext.getCmp("buttonDelete").setDisabled(true);
			Ext.getCmp("buttonCommit").setDisabled(true);
			Ext.getCmp("buttonCancelConfirm").setDisabled(true);
			Ext.getCmp("buttonGenWSBill").setDisabled(true);

			return;
		}
		var bill = item[0];
		var commited = bill.get("billStatus") >= 1000;

		var buttonEdit = Ext.getCmp("buttonEdit");
		buttonEdit.setDisabled(false);
		if (commited) {
			buttonEdit.setText("查看销售订单");
		} else {
			buttonEdit.setText("编辑销售订单");
		}

		Ext.getCmp("buttonDelete").setDisabled(commited);
		Ext.getCmp("buttonCommit").setDisabled(commited);
		Ext.getCmp("buttonCancelConfirm").setDisabled(!commited);
		Ext.getCmp("buttonGenWSBill").setDisabled(!commited);

		me.refreshDetailGrid();

		me.refreshWSGrid();
	},

	/**
	 * 刷新销售订单明细记录
	 */
	refreshDetailGrid : function(id) {
		var me = this;
		me.getDetailGrid().setTitle("销售订单明细");
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var bill = item[0];

		var grid = me.getDetailGrid();
		grid.setTitle("单号: " + bill.get("ref") + " 客户: "
				+ bill.get("customerName"));
		var el = grid.getEl();
		el.mask(PSI.Const.LOADING);

		var r = {
			url : me.URL("Home/Sale/soBillDetailList"),
			params : {
				id : bill.get("id")
			},
			callback : function(options, success, response) {
				var store = grid.getStore();

				store.removeAll();

				if (success) {
					var data = me.decodeJSON(response.responseText);
					store.add(data);

					if (store.getCount() > 0) {
						if (id) {
							var r = store.findExact("id", id);
							if (r != -1) {
								grid.getSelectionModel().select(r);
							}
						}
					}
				}

				el.unmask();
			}
		};
		me.ajax(r);
	},

	/**
	 * 审核销售订单
	 */
	onCommit : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("没有选择要审核的销售订单");
			return;
		}
		var bill = item[0];

		if (bill.get("billStatus") > 0) {
			me.showInfo("当前销售订单已经审核，不能再次审核");
			return;
		}

		var detailCount = me.getDetailGrid().getStore().getCount();
		if (detailCount == 0) {
			me.showInfo("当前销售订单没有录入商品明细，不能审核");
			return;
		}

		var info = "请确认是否审核单号: <span style='color:red'>" + bill.get("ref")
				+ "</span> 的销售订单?";
		var id = bill.get("id");

		var funcConfirm = function() {
			var el = Ext.getBody();
			el.mask("正在提交中...");
			var r = {
				url : me.URL("Home/Sale/commitSOBill"),
				params : {
					id : id
				},
				callback : function(options, success, response) {
					el.unmask();

					if (success) {
						var data = me.decodeJSON(response.responseText);
						if (data.success) {
							me.showInfo("成功完成审核操作", function() {
										me.refreshMainGrid(id);
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
		me.confirm(info, funcConfirm);
	},

	/**
	 * 取消审核
	 */
	onCancelConfirm : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("没有选择要取消审核的销售订单");
			return;
		}
		var bill = item[0];

		if (bill.get("billStatus") == 0) {
			me.showInfo("当前销售订单还没有审核，无法取消审核");
			return;
		}

		var info = "请确认是否取消审核单号为 <span style='color:red'>" + bill.get("ref")
				+ "</span> 的销售订单?";
		var id = bill.get("id");
		var funcConfirm = function() {
			var el = Ext.getBody();
			el.mask("正在提交中...");
			var r = {
				url : me.URL("Home/Sale/cancelConfirmSOBill"),
				params : {
					id : id
				},
				callback : function(options, success, response) {
					el.unmask();

					if (success) {
						var data = me.decodeJSON(response.responseText);
						if (data.success) {
							me.showInfo("成功完成取消审核操作", function() {
										me.refreshMainGrid(id);
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
		me.confirm(info, funcConfirm);
	},

	gotoMainGridRecord : function(id) {
		var me = this;
		var grid = me.getMainGrid();
		grid.getSelectionModel().deselectAll();
		var store = grid.getStore();
		if (id) {
			var r = store.findExact("id", id);
			if (r != -1) {
				grid.getSelectionModel().select(r);
			} else {
				grid.getSelectionModel().select(0);
			}
		} else {
			grid.getSelectionModel().select(0);
		}
	},

	/**
	 * 查询
	 */
	onQuery : function() {
		var me = this;

		me.getMainGrid().getStore().currentPage = 1;
		me.refreshMainGrid();
	},

	/**
	 * 清除查询条件
	 */
	onClearQuery : function() {
		var me = this;

		Ext.getCmp("editQueryBillStatus").setValue(-1);
		Ext.getCmp("editQueryRef").setValue(null);
		Ext.getCmp("editQueryFromDT").setValue(null);
		Ext.getCmp("editQueryToDT").setValue(null);
		Ext.getCmp("editQueryCustomer").clearIdValue();
		Ext.getCmp("editQueryReceivingType").setValue(-1);

		me.onQuery();
	},

	getQueryParam : function() {
		var me = this;

		var result = {
			billStatus : Ext.getCmp("editQueryBillStatus").getValue()
		};

		var ref = Ext.getCmp("editQueryRef").getValue();
		if (ref) {
			result.ref = ref;
		}

		var customerId = Ext.getCmp("editQueryCustomer").getIdValue();
		if (customerId) {
			result.customerId = customerId;
		}

		var fromDT = Ext.getCmp("editQueryFromDT").getValue();
		if (fromDT) {
			result.fromDT = Ext.Date.format(fromDT, "Y-m-d");
		}

		var toDT = Ext.getCmp("editQueryToDT").getValue();
		if (toDT) {
			result.toDT = Ext.Date.format(toDT, "Y-m-d");
		}

		var receivingType = Ext.getCmp("editQueryReceivingType").getValue();
		result.receivingType = receivingType;

		return result;
	},

	/**
	 * 生成销售出库单
	 */
	onGenWSBill : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("没有选择要生成出库单的销售订单");
			return;
		}
		var bill = item[0];

		if (bill.get("billStatus") < 1000) {
			me.showInfo("当前销售订单还没有审核，无法生成销售出库单");
			return;
		}

		var form = Ext.create("PSI.Sale.WSEditForm", {
					genBill : true,
					sobillRef : bill.get("ref")
				});
		form.show();
	},

	onPDF : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("没有选择要生成pdf文件的销售订单");
			return;
		}
		var bill = item[0];

		var url = me.URL("Home/Sale/soBillPdf?ref=" + bill.get("ref"));
		window.open(url);
	},

	getWSGrid : function() {
		var me = this;
		if (me.__wsGrid) {
			return me.__wsGrid;
		}
		var modelName = "PSISOBill_WSBill";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "ref", "bizDate", "customerName",
							"warehouseName", "inputUserName", "bizUserName",
							"billStatus", "amount", "dateCreated",
							"receivingType", "memo"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : []
				});

		me.__wsGrid = Ext.create("Ext.grid.Panel", {
			cls : "PSI",
			title : "销售订单出库详情",
			viewConfig : {
				enableTextSelection : true
			},
			columnLines : true,
			columns : [{
						xtype : "rownumberer",
						width : 50
					}, {
						header : "状态",
						dataIndex : "billStatus",
						menuDisabled : true,
						sortable : false,
						width : 60,
						renderer : function(value) {
							if (value == "待出库") {
								return "<span style='color:red'>" + value
										+ "</span>";
							} else if (value == "已退货") {
								return "<span style='color:blue'>" + value
										+ "</span>";
							} else {
								return value;
							}
						}
					}, {
						header : "单号",
						dataIndex : "ref",
						width : 110,
						menuDisabled : true,
						sortable : false,
						renderer : function(value, md, record) {
							return "<a href='"
									+ PSI.Const.BASE_URL
									+ "Home/Bill/viewIndex?fid=2028&refType=销售出库&ref="
									+ encodeURIComponent(record.get("ref"))
									+ "' target='_blank'>" + value + "</a>";
						}
					}, {
						header : "业务日期",
						dataIndex : "bizDate",
						menuDisabled : true,
						sortable : false
					}, {
						header : "客户",
						dataIndex : "customerName",
						width : 300,
						menuDisabled : true,
						sortable : false
					}, {
						header : "收款方式",
						dataIndex : "receivingType",
						menuDisabled : true,
						sortable : false,
						width : 100,
						renderer : function(value) {
							if (value == 0) {
								return "记应收账款";
							} else if (value == 1) {
								return "现金收款";
							} else if (value == 2) {
								return "用预收款支付";
							} else {
								return "";
							}
						}
					}, {
						header : "销售金额",
						dataIndex : "amount",
						menuDisabled : true,
						sortable : false,
						align : "right",
						xtype : "numbercolumn",
						width : 150
					}, {
						header : "出库仓库",
						dataIndex : "warehouseName",
						menuDisabled : true,
						sortable : false
					}, {
						header : "业务员",
						dataIndex : "bizUserName",
						menuDisabled : true,
						sortable : false
					}, {
						header : "制单人",
						dataIndex : "inputUserName",
						menuDisabled : true,
						sortable : false
					}, {
						header : "制单时间",
						dataIndex : "dateCreated",
						width : 140,
						menuDisabled : true,
						sortable : false
					}, {
						header : "备注",
						dataIndex : "memo",
						width : 200,
						menuDisabled : true,
						sortable : false
					}],
			store : store
		});

		return me.__wsGrid;
	},

	refreshWSGrid : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var bill = item[0];

		var grid = me.getWSGrid();
		var el = grid.getEl();
		if (el) {
			el.mask(PSI.Const.LOADING);
		}

		var r = {
			url : me.URL("Home/Sale/soBillWSBillList"),
			params : {
				id : bill.get("id")
			},
			callback : function(options, success, response) {
				var store = grid.getStore();

				store.removeAll();

				if (success) {
					var data = me.decodeJSON(response.responseText);
					store.add(data);
				}

				if (el) {
					el.unmask();
				}
			}
		};
		me.ajax(r);
	},

	onPrintPreview : function() {
		var lodop = getLodop();
		if (!lodop) {
			PSI.MsgBox.showInfo("没有安装Lodop控件，无法打印");
			return;
		}

		var me = this;

		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("没有选择要打印的销售订单");
			return;
		}
		var bill = item[0];

		var el = Ext.getBody();
		el.mask("数据加载中...");
		var r = {
			url : PSI.Const.BASE_URL + "Home/Sale/genSOBillPrintPage",
			params : {
				id : bill.get("id")
			},
			callback : function(options, success, response) {
				el.unmask();

				if (success) {
					var data = response.responseText;
					me.previewSOBill(bill.get("ref"), data);
				}
			}
		};
		me.ajax(r);
	},

	PRINT_PAGE_WIDTH : "200mm",
	PRINT_PAGE_HEIGHT : "95mm",

	previewSOBill : function(ref, data) {
		var me = this;

		var lodop = getLodop();
		if (!lodop) {
			PSI.MsgBox.showInfo("Lodop打印控件没有正确安装");
			return;
		}

		lodop.PRINT_INIT("销售订单" + ref);
		lodop.SET_PRINT_PAGESIZE(1, me.PRINT_PAGE_WIDTH, me.PRINT_PAGE_HEIGHT,
				"");
		lodop.ADD_PRINT_HTM("0mm", "0mm", "100%", "100%", data);
		var result = lodop.PREVIEW("_blank");
	},

	onPrint : function() {
		var lodop = getLodop();
		if (!lodop) {
			PSI.MsgBox.showInfo("没有安装Lodop控件，无法打印");
			return;
		}

		var me = this;

		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("没有选择要打印的销售订单");
			return;
		}
		var bill = item[0];

		var el = Ext.getBody();
		el.mask("数据加载中...");
		var r = {
			url : PSI.Const.BASE_URL + "Home/Sale/genSOBillPrintPage",
			params : {
				id : bill.get("id")
			},
			callback : function(options, success, response) {
				el.unmask();

				if (success) {
					var data = response.responseText;
					me.printSOBill(bill.get("ref"), data);
				}
			}
		};
		me.ajax(r);
	},

	printSOBill : function(ref, data) {
		var me = this;

		var lodop = getLodop();
		if (!lodop) {
			PSI.MsgBox.showInfo("Lodop打印控件没有正确安装");
			return;
		}

		lodop.PRINT_INIT("销售订单" + ref);
		lodop.SET_PRINT_PAGESIZE(1, me.PRINT_PAGE_WIDTH, me.PRINT_PAGE_HEIGHT,
				"");
		lodop.ADD_PRINT_HTM("0mm", "0mm", "100%", "100%", data);
		var result = lodop.PRINT();
	}
});