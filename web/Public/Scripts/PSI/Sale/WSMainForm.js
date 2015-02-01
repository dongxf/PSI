Ext.define("PSI.Sale.WSMainForm", {
	extend : "Ext.panel.Panel",

	border : 0,
	layout : "border",

	initComponent : function() {
		var me = this;

		Ext.define("PSIWSBill", {
			extend : "Ext.data.Model",
			fields : [ "id", "ref", "bizDate", "customerName", "warehouseName",
					"inputUserName", "bizUserName", "billStatus", "amount" ]
		});
		var storeWSBill = Ext.create("Ext.data.Store", {
			autoLoad : false,
			model : "PSIWSBill",
			data : []
		});

		var gridWSBill = Ext.create("Ext.grid.Panel", {
			columnLines : true,
			columns : [ {
				header : "状态",
				dataIndex : "billStatus",
				menuDisabled : true,
				sortable : false,
				width : 60
			}, {
				header : "单号",
				dataIndex : "ref",
				width : 110,
				menuDisabled : true,
				sortable : false
			}, {
				header : "业务日期",
				dataIndex : "bizDate",
				menuDisabled : true,
				sortable : false
			}, {
				header : "客户",
				dataIndex : "customerName",
				width : 200,
				menuDisabled : true,
				sortable : false
			}, {
				header : "销售金额",
				dataIndex : "amount",
				menuDisabled : true,
				sortable : false,
				align : "right",
				xtype : "numbercolumn",
				width : 80
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
				header : "录单人",
				dataIndex : "inputUserName",
				menuDisabled : true,
				sortable : false
			} ],
			listeners : {
				select : {
					fn : me.onWSBillGridSelect,
					scope : me
				},
				itemdblclick : {
					fn : me.onEditWSBill,
					scope : me
				}
			},
			store : storeWSBill
		});

		Ext.define("PSIWSBillDetail", {
			extend : "Ext.data.Model",
			fields : [ "id", "goodsCode", "goodsName", "goodsSpec", "unitName",
					"goodsCount", "goodsMoney", "goodsPrice" ]
		});
		var storeWSBillDetail = Ext.create("Ext.data.Store", {
			autoLoad : false,
			model : "PSIWSBillDetail",
			data : []
		});

		var gridWSBillDetail = Ext.create("Ext.grid.Panel", {
			title : "销售出库单明细",
			columnLines : true,
			columns : [ Ext.create("Ext.grid.RowNumberer", {
				text : "序号",
				width : 30
			}), {
				header : "商品编码",
				dataIndex : "goodsCode",
				menuDisabled : true,
				sortable : false,
				width : 60
			}, {
				header : "商品名称",
				dataIndex : "goodsName",
				menuDisabled : true,
				sortable : false,
				width : 120
			}, {
				header : "规格型号",
				dataIndex : "goodsSpec",
				menuDisabled : true,
				sortable : false
			}, {
				header : "数量",
				dataIndex : "goodsCount",
				menuDisabled : true,
				sortable : false,
				align : "right"
			}, {
				header : "单位",
				dataIndex : "unitName",
				menuDisabled : true,
				sortable : false,
				width : 60
			}, {
				header : "单价",
				dataIndex : "goodsPrice",
				menuDisabled : true,
				sortable : false,
				align : "right",
				xtype : "numbercolumn",
				width : 60
			}, {
				header : "销售金额",
				dataIndex : "goodsMoney",
				menuDisabled : true,
				sortable : false,
				align : "right",
				xtype : "numbercolumn",
				width : 80
			} ],
			store : storeWSBillDetail
		});

		Ext.apply(me, {
			tbar : [ {
				text : "新建销售出库单",
				iconCls : "PSI-button-add",
				scope : me,
				handler : me.onAddWSBill
			}, "-", {
				text : "编辑销售出库单",
				iconCls : "PSI-button-edit",
				scope : me,
				handler : me.onEditWSBill
			}, "-", {
				text : "删除销售出库单",
				iconCls : "PSI-button-delete",
				scope : me,
				handler : me.onDeleteWSBill
			}, "-", {
				text : "提交出库",
				iconCls : "PSI-button-commit",
				scope : me,
				handler : me.onCommit
			}, "-", {
				text : "关闭",
				iconCls : "PSI-button-exit",
				handler : function() {
					location.replace(PSI.Const.BASE_URL);
				}
			} ],
			items : [ {
				region : "north",
				height : "30%",
				split : true,
				layout : "fit",
				border : 0,
				items : [ gridWSBill ]
			}, {
				region : "center",
				layout : "fit",
				border : 0,
				items : [ gridWSBillDetail ]
			} ]
		});

		me.wsBillGrid = gridWSBill;
		me.wsBillDetailGrid = gridWSBillDetail;

		me.callParent(arguments);

		me.refreshWSBillGrid();
	},

	refreshWSBillGrid : function(id) {
		var gridDetail = this.wsBillDetailGrid;
		gridDetail.setTitle("销售出库单明细");
		gridDetail.getStore().removeAll();

		var grid = this.wsBillGrid;
		var el = grid.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
			url : PSI.Const.BASE_URL + "Home/Sale/wsbillList",
			method : "POST",
			callback : function(options, success, response) {
				var store = grid.getStore();

				store.removeAll();

				if (success) {
					var data = Ext.JSON.decode(response.responseText);
					store.add(data);

					if (store.getCount() > 0) {
						if (id) {
							var r = store.findExact("id", id);
							if (r != -1) {
								grid.getSelectionModel().select(r);
							}
						} else {
							grid.getSelectionModel().select(0);
						}
					}
				}

				el.unmask();
			}
		});
	},

	onAddWSBill : function() {
		var form = Ext.create("PSI.Sale.WSEditForm", {
			parentForm : this
		});
		form.show();
	},

	onEditWSBill : function() {
		var item = this.wsBillGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要编辑的销售出库单");
			return;
		}
		var wsBill = item[0];

		var form = Ext.create("PSI.Sale.WSEditForm", {
			parentForm : this,
			entity : wsBill
		});
		form.show();
	},

	onDeleteWSBill : function() {
		var item = this.wsBillGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要删除的销售出库单");
			return;
		}
		var wsBill = item[0];

		var info = "请确认是否删除销售出库单: <span style='color:red'>" + wsBill.get("ref")
				+ "</span>";
		var me = this;
		PSI.MsgBox.confirm(info, function() {
			var el = Ext.getBody();
			el.mask("正在删除中...");
			Ext.Ajax.request({
				url : PSI.Const.BASE_URL + "Home/Sale/deleteWSBill",
				method : "POST",
				params : {
					id : wsBill.get("id")
				},
				callback : function(options, success, response) {
					el.unmask();

					if (success) {
						var data = Ext.JSON.decode(response.responseText);
						if (data.success) {
							PSI.MsgBox.showInfo("成功完成删除操作", function() {
								me.refreshWSBillGrid();
							});
						} else {
							PSI.MsgBox.showInfo(data.msg);
						}
					} else {
						PSI.MsgBox.showInfo("网络错误", function() {
							window.location.reload();
						});
					}
				}

			});
		});
	},

	onWSBillGridSelect : function() {
		this.refreshWSBillDetailGrid();
	},

	refreshWSBillDetailGrid : function(id) {
		var me = this;
		me.wsBillDetailGrid.setTitle("销售出库单明细");
		var grid = me.wsBillGrid;
		var item = grid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var bill = item[0];

		grid = me.wsBillDetailGrid;
		grid.setTitle("单号: " + bill.get("ref") + " 客户: "
				+ bill.get("customerName") + " 出库仓库: "
				+ bill.get("warehouseName"));
		var el = grid.getEl();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
			url : PSI.Const.BASE_URL + "Home/Sale/wsBillDetailList",
			params : {
				billId : bill.get("id")
			},
			method : "POST",
			callback : function(options, success, response) {
				var store = grid.getStore();

				store.removeAll();

				if (success) {
					var data = Ext.JSON.decode(response.responseText);
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
		});
	},

	refreshWSBillInfo : function() {
		var me = this;
		var item = me.wsBillGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var bill = item[0];

		Ext.Ajax.request({
			url : PSI.Const.BASE_URL + "Home/Sale/refreshWSBillInfo",
			method : "POST",
			params : {
				id : bill.get("id")
			},
			callback : function(options, success, response) {
				if (success) {
					var data = Ext.JSON.decode(response.responseText);
					bill.set("amount", data.amount);
					me.wsBillGrid.getStore().commitChanges();
				}
			}
		});
	},

    onCommit: function() {
        var me = this;
    	var item = me.wsBillGrid.getSelectionModel().getSelection();
        if (item == null || item.length != 1) {
            PSI.MsgBox.showInfo("没有选择要提交的销售出库单");
            return;
        }
        var bill = item[0]; 	
        
        var detailCount = this.wsBillDetailGrid.getStore().getCount();
        if (detailCount == 0) {
        	PSI.MsgBox.showInfo("当前销售出库单没有录入商品明细，不能提交");
        	return;
        }
        
        var info = "请确认是否提交单号: <span style='color:red'>" + bill.get("ref") + "</span> 的销售出库单?";
        PSI.MsgBox.confirm(info, function () {
            var el = Ext.getBody();
            el.mask("正在提交中...");
            Ext.Ajax.request({
                url: PSI.Const.BASE_URL + "Home/Sale/commitWSBill",
                method: "POST",
                params: { id: bill.get("id") },
                callback: function (options, success, response) {
                    el.unmask();

                    if (success) {
                        var data = Ext.JSON.decode(response.responseText);
                        if (data.success) {
                            PSI.MsgBox.showInfo("成功完成提交操作", function () {
                                me.refreshWSBillGrid(data.id);
                            });
                        } else {
                            PSI.MsgBox.showInfo(data.msg);
                        }
                    } else {
                        PSI.MsgBox.showInfo("网络错误", function () {
                            window.location.reload();
                        });
                    }
                }
            });
        });
    }
});