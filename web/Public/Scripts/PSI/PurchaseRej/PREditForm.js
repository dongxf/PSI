// 采购退货出库单 - 新增或编辑界面
Ext.define("PSI.PurchaseRej.PREditForm", {
	extend : "Ext.window.Window",
	config : {
		parentForm : null,
		entity : null
	},
	initComponent : function() {
		var me = this;
		var entity = me.getEntity();
		this.adding = entity == null;

		Ext.apply(me, {
			title : entity == null ? "新建采购退货出库单" : "编辑采购退货出库单",
			modal : true,
			onEsc : Ext.emptyFn,
			maximized: true,
			width : 1200,
			height : 600,
			tbar:["-",{
                text: "选择采购入库单",
                iconCls: "PSI-button-add",
                handler: me.onSelectPWBill,
                scope: me,
                disabled: me.entity != null
			}, "-"],
			layout : "border",
			defaultFocus : "editWarehouse",
			items : [ {
				region : "center",
				layout : "fit",
				border : 0,
				bodyPadding : 10,
				items : [ me.getGoodsGrid() ]
			}, {
				region : "north",
				id : "editForm",
				layout : {
					type : "table",
					columns : 2
				},
				height : 100,
				bodyPadding : 10,
				border : 0,
				items : [ {
					xtype : "hidden",
					id : "hiddenId",
					name : "id",
					value : entity == null ? null : entity.get("id")
				}, {
                    id: "editSupplier",
                    xtype: "displayfield",
                    fieldLabel: "供应商",
                    labelWidth: 60,
                    labelAlign: "right",
                    labelSeparator: "",
                    colspan: 2,
                    width: 430
                }, {
					id : "editRef",
					labelWidth : 60,
					labelAlign : "right",
					labelSeparator : "",
					fieldLabel : "单号",
					xtype : "displayfield",
					value : "<span style='color:red'>保存后自动生成</span>"
				}, {
					id : "editBizDT",
					fieldLabel : "业务日期",
					labelWidth : 60,
					labelAlign : "right",
					labelSeparator : "",
					allowBlank : false,
					blankText : "没有输入业务日期",
					beforeLabelTextTpl : PSI.Const.REQUIRED,
					xtype : "datefield",
					format : "Y-m-d",
					value : new Date(),
					name : "bizDT",
					listeners : {
						specialkey : {
							fn : me.onEditBizDTSpecialKey,
							scope : me
						}
					}
				}, {
					xtype : "hidden",
					id : "editSupplierId"
				}, {
					xtype : "hidden",
					id : "editWarehouseId",
					name : "warehouseId"
				}, {
					id : "editWarehouse",
					labelWidth : 60,
					labelAlign : "right",
					labelSeparator : "",
					fieldLabel : "出库仓库",
					xtype : "psi_warehousefield",
					parentCmp : me,
					fid: "2007",
					allowBlank : false,
					blankText : "没有输入出库仓库",
					beforeLabelTextTpl : PSI.Const.REQUIRED,
					listeners : {
						specialkey : {
							fn : me.onEditWarehouseSpecialKey,
							scope : me
						}
					}
				}, {
					xtype : "hidden",
					id : "editBizUserId",
					name : "bizUserId"
				}, {
					id : "editBizUser",
					labelWidth : 60,
					labelAlign : "right",
					labelSeparator : "",
					fieldLabel : "业务员",
					xtype : "psi_userfield",
					parentCmp : me,
					allowBlank : false,
					blankText : "没有输入业务员",
					beforeLabelTextTpl : PSI.Const.REQUIRED,
					listeners : {
						specialkey : {
							fn : me.onEditBizUserSpecialKey,
							scope : me
						}
					}
				} ]
			} ],
			listeners : {
				show : {
					fn : me.onWndShow,
					scope : me
				}
			},
			buttons : [ {
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
			} ]
		});

		me.callParent(arguments);
	},
	
	onWndShow : function() {
		var me = this;

		var el = me.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
			url : PSI.Const.BASE_URL + "Home/PurchaseRej/prBillInfo",
			params : {
				id : Ext.getCmp("hiddenId").getValue()
			},
			method : "POST",
			callback : function(options, success, response) {
				el.unmask();

				if (success) {
					var data = Ext.JSON.decode(response.responseText);

					if (data.ref) {
						Ext.getCmp("editRef").setValue(data.ref);
					} else {
						// 新建采购退货出库单，第一步就是选择采购入库单
						me.onSelectPWBill();
					}

					Ext.getCmp("editSupplierId").setValue(data.supplierId);
					Ext.getCmp("editSupplier").setValue(data.supplierName + " 采购入库单单号：" + data.pwbillRef);

					Ext.getCmp("editWarehouseId").setValue(data.warehouseId);
					Ext.getCmp("editWarehouse").setValue(data.warehouseName);

					Ext.getCmp("editBizUserId").setValue(data.bizUserId);
					Ext.getCmp("editBizUser").setValue(data.bizUserName);
					if (data.bizDT) {
						Ext.getCmp("editBizDT").setValue(data.bizDT);
					}
					me.__billId = data.pwbillId;

					var store = me.getGoodsGrid().getStore();
					store.removeAll();
					if (data.items) {
						store.add(data.items);
					}
				}
			}
		});
	},
	
	onOK : function() {
		var me = this;
		Ext.getBody().mask("正在保存中...");
		Ext.Ajax.request({
			url : PSI.Const.BASE_URL + "Home/PurchaseRej/editPRBill",
			method : "POST",
			params : {
				jsonStr : me.getSaveData()
			},
			callback : function(options, success, response) {
				Ext.getBody().unmask();

				if (success) {
					var data = Ext.JSON.decode(response.responseText);
					if (data.success) {
						PSI.MsgBox.showInfo("成功保存数据", function() {
							me.close();
							me.getParentForm().refreshMainGrid(data.id);
						});
					} else {
						PSI.MsgBox.showInfo(data.msg);
					}
				}
			}
		});

	},
	onEditBizDTSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editWarehouse").focus();
		}
	},
	onEditWarehouseSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editBizUser").focus();
		}
	},
	onEditBizUserSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			var me = this;
			var store = me.getGoodsGrid().getStore();
			if (store.getCount() == 0) {
				store.add({});
			}
			me.getGoodsGrid().focus();
			me.__cellEditing.startEdit(0, 1);
		}
	},

	// WarehouseField回调此方法
	__setWarehouseInfo : function(data) {
		Ext.getCmp("editWarehouseId").setValue(data.id);
	},
	
	// UserField回调此方法
	__setUserInfo : function(data) {
		Ext.getCmp("editBizUserId").setValue(data.id);
	},
	
	getGoodsGrid : function() {
		var me = this;
		if (me.__goodsGrid) {
			return me.__goodsGrid;
		}
		var modelName = "PSIPRBillDetail_EditForm";
		Ext.define(modelName, {
			extend : "Ext.data.Model",
			fields : [ "id", "goodsId", "goodsCode", "goodsName", "goodsSpec",
					"unitName", "goodsCount", "goodsMoney", "goodsPrice", "rejCount",
					"rejPrice", "rejMoney"]
		});
		var store = Ext.create("Ext.data.Store", {
			autoLoad : false,
			model : modelName,
			data : []
		});

		me.__cellEditing = Ext.create("Ext.grid.plugin.CellEditing", {
			clicksToEdit : 1,
			listeners : {
				edit : {
					fn : me.cellEditingAfterEdit,
					scope : me
				}
			}
		});
		Ext.apply(me.__cellEditing, {
			onSpecialKey : function(ed, field, e) {
				var sm;

				if (e.getKey() === e.TAB || e.getKey() == e.ENTER) {
					e.stopEvent();

					if (ed) {
						ed.onEditorTab(e);
					}

					sm = ed.up('tablepanel').getSelectionModel();
					if (sm.onEditorTab) {
						return sm.onEditorTab(ed.editingPlugin, e);
					}
				}
			}
		});
		me.__goodsGrid = Ext.create("Ext.grid.Panel", {
			plugins : [ me.__cellEditing ],
			columnLines : true,
			columns : [
					Ext.create("Ext.grid.RowNumberer", {
						text : "序号",
						width : 30
					}),
					{
						header : "商品编码",
						dataIndex : "goodsCode",
						menuDisabled : true,
						sortable : false
					},
					{
						header : "商品名称",
						dataIndex : "goodsName",
						menuDisabled : true,
						sortable : false,
						width : 200
					},
					{
						header : "规格型号",
						dataIndex : "goodsSpec",
						menuDisabled : true,
						sortable : false,
						width : 200
					},
					{
						header : "退货数量",
						dataIndex : "rejCount",
						menuDisabled : true,
						sortable : false,
						align : "right",
						width : 100,
						editor : {
							xtype : "numberfield",
							allowDecimals : false,
							hideTrigger : true
						}
					},
					{
						header : "单位",
						dataIndex : "unitName",
						menuDisabled : true,
						sortable : false,
						width : 60
					},
					{
						header : "退货单价",
						dataIndex : "rejPrice",
						menuDisabled : true,
						sortable : false,
						align : "right",
						xtype : "numbercolumn",
						width : 100,
						editor : {
							xtype : "numberfield",
							hideTrigger : true
						}
					},
					{
						header : "退货金额",
						dataIndex : "rejMoney",
						menuDisabled : true,
						sortable : false,
						align : "right",
						xtype : "numbercolumn",
						width : 120
					},{
						header : "原采购数量",
						dataIndex : "goodsCount",
						menuDisabled : true,
						sortable : false,
						align : "right",
						xtype : "numbercolumn",
						width : 120
					},{
						header : "原采购单价",
						dataIndex : "goodsPrice",
						menuDisabled : true,
						sortable : false,
						align : "right",
						xtype : "numbercolumn",
						width : 120
					},{
						header : "原采购金额",
						dataIndex : "goodsMoney",
						menuDisabled : true,
						sortable : false,
						align : "right",
						xtype : "numbercolumn",
						width : 120
					}],
			store : store,
			listeners : {}
		});

		return me.__goodsGrid;
	},

	cellEditingAfterEdit : function(editor, e) {
		var me = this;
		if (e.colIdx == 6) {
			me.calcMoney();

			var store = me.getGoodsGrid().getStore();
			e.rowIdx += 1;
			me.getGoodsGrid().getSelectionModel().select(e.rowIdx);
			me.__cellEditing.startEdit(e.rowIdx, 1);
		} else if (e.colIdx == 4) {
			me.calcMoney();
		}
	},
	calcMoney : function() {
		var me = this;
		var item = me.getGoodsGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var goods = item[0];
		goods.set("rejMoney", goods.get("rejCount") * goods.get("rejPrice"));
	},
	
	getSaveData : function() {
		var me = this;
		
		var result = {
			id : Ext.getCmp("hiddenId").getValue(),
			bizDT : Ext.Date.format(Ext.getCmp("editBizDT").getValue(), "Y-m-d"),
			warehouseId : Ext.getCmp("editWarehouseId").getValue(),
			bizUserId : Ext.getCmp("editBizUserId").getValue(),
			pwBillId: me.__billId,
			items : []
		};

		var store = me.getGoodsGrid().getStore();
		for (var i = 0; i < store.getCount(); i++) {
			var item = store.getAt(i);
			result.items.push({
				id : item.get("id"),
				goodsId : item.get("goodsId"),
				goodsCount : item.get("goodsCount"),
				goodsPrice : item.get("goodsPrice"),
				rejCount: item.get("rejCount"),
				rejPrice: item.get("rejPrice")
			});
		}

		return Ext.JSON.encode(result);
	},
	
	onSelectPWBill: function() {
		var form = Ext.create("PSI.PurchaseRej.PRSelectPWBillForm", {
            parentForm: this
        });
        form.show();
	},
	
	getPWBillInfo: function(id) {
		var me = this;
        me.__billId = id;
        var el = me.getEl() || Ext.getBody();
        el.mask(PSI.Const.LOADING);
        Ext.Ajax.request({
            url: PSI.Const.BASE_URL + "Home/PurchaseRej/getPWBillInfoForPRBill",
            params: {
                id: id
            },
            method: "POST",
            callback: function (options, success, response) {
                if (success) {
                    var data = Ext.JSON.decode(response.responseText);
                    Ext.getCmp("editSupplier").setValue(data.supplierName + " 采购入库单单号: " + data.ref);
                    Ext.getCmp("editSupplierId").setValue(data.supplierId);
                    Ext.getCmp("editWarehouseId").setValue(data.warehouseId);
                    Ext.getCmp("editWarehouse").setValue(data.warehouseName);
                    
                    var store = me.getGoodsGrid().getStore();
                    store.removeAll();
                    store.add(data.items);
                }

                el.unmask();
            }
        });
	}
});