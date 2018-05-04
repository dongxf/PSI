/**
 * 采购入库单 - 新增或编辑界面
 */
Ext.define("PSI.Purchase.PWEditForm", {
	extend : "PSI.AFX.BaseDialogForm",
	config : {
		genBill : false,
		pobillRef : null,
		showAddGoodsButton : "0",
		viewPrice : true
	},

	initComponent : function() {
		var me = this;
		me.__readOnly = false;
		var entity = me.getEntity();
		me.adding = entity == null;

		var title = entity == null ? "新建采购入库单" : "编辑采购入库单";
		title = me.formatTitle(title);
		var iconCls = entity == null ? "PSI-button-add" : "PSI-button-edit";

		Ext.apply(me, {
			header : {
				title : title,
				height : 40,
				iconCls : iconCls
			},
			maximized : true,
			width : 1000,
			height : 600,
			layout : "border",
			defaultFocus : "editSupplier",
			tbar : [{
						xtype : "displayfield",
						value : "条码录入",
						id : "displayFieldBarcode"
					}, {
						xtype : "textfield",
						cls : "PSI-toolbox",
						id : "editBarcode",
						listeners : {
							specialkey : {
								fn : me.onEditBarcodeKeydown,
								scope : me
							}
						}

					}, " ", {
						text : "保存",
						id : "buttonSave",
						iconCls : "PSI-button-ok",
						handler : me.onOK,
						scope : me
					}, "-", {
						text : "取消",
						id : "buttonCancel",
						handler : function() {
							if (me.__readonly) {
								me.close();
								return;
							}

							me.confirm("请确认是否取消当前操作？", function() {
										me.close();
									});
						},
						scope : me
					}, "->", {
						text : "表单通用操作帮助",
						iconCls : "PSI-help",
						handler : function() {
							window.open(me.URL("/Home/Help/index?t=commBill"));
						}
					}],
			items : [{
						region : "center",
						layout : "fit",
						border : 0,
						bodyPadding : 10,
						items : [me.getGoodsGrid()]
					}, {
						region : "north",
						id : "editForm",
						layout : {
							type : "table",
							columns : 2
						},
						height : 120,
						bodyPadding : 10,
						border : 0,
						items : [{
									xtype : "hidden",
									id : "hiddenId",
									name : "id",
									value : entity == null ? null : entity
											.get("id")
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
									id : "editSupplier",
									colspan : 2,
									width : 430,
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									xtype : "psi_supplierfield",
									fieldLabel : "供应商",
									allowBlank : false,
									blankText : "没有输入供应商",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									listeners : {
										specialkey : {
											fn : me.onEditSupplierSpecialKey,
											scope : me
										}
									},
									showAddButton : true
								}, {
									id : "editWarehouse",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "入库仓库",
									xtype : "psi_warehousefield",
									fid : "2001",
									allowBlank : false,
									blankText : "没有输入入库仓库",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									listeners : {
										specialkey : {
											fn : me.onEditWarehouseSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editBizUser",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "业务员",
									xtype : "psi_userfield",
									allowBlank : false,
									blankText : "没有输入业务员",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									listeners : {
										specialkey : {
											fn : me.onEditBizUserSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editPaymentType",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "付款方式",
									xtype : "combo",
									queryMode : "local",
									editable : false,
									valueField : "id",
									store : Ext.create("Ext.data.ArrayStore", {
												fields : ["id", "text"],
												data : [["0", "记应付账款"],
														["1", "现金付款"],
														["2", "预付款"]]
											}),
									value : "0",
									listeners : {
										specialkey : {
											fn : me.onEditPaymentTypeSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editBillMemo",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "备注",
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onEditBillMemoSpecialKey,
											scope : me
										}
									}
								}]
					}],
			listeners : {
				show : {
					fn : me.onWndShow,
					scope : me
				},
				close : {
					fn : me.onWndClose,
					scope : me
				}
			}
		});

		me.callParent(arguments);

		me.editRef = Ext.getCmp("editRef");
		me.editBizDT = Ext.getCmp("editBizDT");
		me.editSupplier = Ext.getCmp("editSupplier");
		me.editWarehouse = Ext.getCmp("editWarehouse");
		me.editBizUser = Ext.getCmp("editBizUser");
		me.editPaymentType = Ext.getCmp("editPaymentType");
		me.editBillMemo = Ext.getCmp("editBillMemo");

		me.editHiddenId = Ext.getCmp("hiddenId");

		me.columnActionDelete = Ext.getCmp("columnActionDelete");
		me.columnActionAdd = Ext.getCmp("columnActionAdd");
		me.columnActionAppend = Ext.getCmp("columnActionAppend");
		me.editBarcode = Ext.getCmp("editBarcode");

		me.columnGoodsCode = Ext.getCmp("columnGoodsCode");
		me.columnGoodsPrice = Ext.getCmp("columnGoodsPrice");
		me.columnGoodsMoney = Ext.getCmp("columnGoodsMoney");

		me.buttonSave = Ext.getCmp("buttonSave");
		me.buttonCancel = Ext.getCmp("buttonCancel");

		me.displayFieldBarcode = Ext.getCmp("displayFieldBarcode");
	},

	onWindowBeforeUnload : function(e) {
		return (window.event.returnValue = e.returnValue = '确认离开当前页面？');
	},

	onWndClose : function() {
		Ext.get(window).un('beforeunload', this.onWindowBeforeUnload);
	},

	onWndShow : function() {
		Ext.get(window).on('beforeunload', this.onWindowBeforeUnload);

		var me = this;

		var el = me.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		me.ajax({
			url : me.URL("Home/Purchase/pwBillInfo"),
			params : {
				id : me.editHiddenId.getValue(),
				pobillRef : me.getPobillRef()
			},
			callback : function(options, success, response) {
				el.unmask();

				if (success) {
					var data = me.decodeJSON(response.responseText);
					me.editBillMemo.setValue(data.billMemo);

					if (me.getGenBill()) {
						// 从采购订单生成采购入库单
						me.editSupplier.setIdValue(data.supplierId);
						me.editSupplier.setValue(data.supplierName);
						me.editBizUser.setIdValue(data.bizUserId);
						me.editBizUser.setValue(data.bizUserName);
						me.editBizDT.setValue(data.dealDate);
						me.editPaymentType.setValue(data.paymentType);
						var store = me.getGoodsGrid().getStore();
						store.removeAll();
						store.add(data.items);

						me.editSupplier.setReadOnly(true);
						me.columnActionDelete.hide();
						me.columnActionAdd.hide();
						me.columnActionAppend.hide();

						me.editBarcode.setDisabled(true);
					} else {
						if (!data.genBill) {
							me.columnGoodsCode.setEditor({
								xtype : "psi_goods_with_purchaseprice_field",
								parentCmp : me,
								showAddButton : me.getShowAddGoodsButton() == "1"
							});
							if (me.getViewPrice()) {
								me.columnGoodsPrice.setEditor({
											xtype : "numberfield",
											hideTrigger : true
										});
								me.columnGoodsMoney.setEditor({
											xtype : "numberfield",
											hideTrigger : true
										});
							}
						} else {
							me.editSupplier.setReadOnly(true);
							me.columnActionDelete.hide();
							me.columnActionAdd.hide();
							me.columnActionAppend.hide();
						}

						if (data.ref) {
							me.editRef.setValue(data.ref);
						}

						me.editSupplier.setIdValue(data.supplierId);
						me.editSupplier.setValue(data.supplierName);

						me.editWarehouse.setIdValue(data.warehouseId);
						me.editWarehouse.setValue(data.warehouseName);

						me.editBizUser.setIdValue(data.bizUserId);
						me.editBizUser.setValue(data.bizUserName);
						if (data.bizDT) {
							me.editBizDT.setValue(data.bizDT);
						}
						if (data.paymentType) {
							me.editPaymentType.setValue(data.paymentType);
						}

						var store = me.getGoodsGrid().getStore();
						store.removeAll();
						if (data.items) {
							store.add(data.items);
						}
						if (store.getCount() == 0) {
							store.add({});
						}

						if (data.billStatus && data.billStatus != 0) {
							me.setBillReadonly();
						}
					}
				}
			}
		});
	},

	onOK : function() {
		var me = this;
		Ext.getBody().mask("正在保存中...");
		var r = {
			url : me.URL("Home/Purchase/editPWBill"),
			params : {
				jsonStr : me.getSaveData()
			},
			callback : function(options, success, response) {
				Ext.getBody().unmask();

				if (success) {
					var data = me.decodeJSON(response.responseText);
					if (data.success) {
						me.showInfo("成功保存数据", function() {
									me.close();
									var pf = me.getParentForm();
									if (pf) {
										pf.refreshMainGrid(data.id);
									}
								});
					} else {
						me.showInfo(data.msg);
					}
				}
			}
		};
		me.ajax(r);
	},

	onEditBizDTSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() == e.ENTER) {
			me.editSupplier.focus();
		}
	},

	onEditSupplierSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() == e.ENTER) {
			me.editWarehouse.focus();
		}
	},

	onEditWarehouseSpecialKey : function(field, e) {
		var me = this;

		if (e.getKey() == e.ENTER) {
			me.editBizUser.focus();
		}
	},

	onEditBizUserSpecialKey : function(field, e) {
		var me = this;

		if (me.__readonly) {
			return;
		}

		if (e.getKey() == e.ENTER) {
			me.editPaymentType.focus();
		}
	},

	onEditBillMemoSpecialKey : function(field, e) {
		var me = this;

		if (me.__readonly) {
			return;
		}

		if (e.getKey() == e.ENTER) {
			var store = me.getGoodsGrid().getStore();
			if (store.getCount() == 0) {
				store.add({});
			}
			me.getGoodsGrid().focus();
			me.__cellEditing.startEdit(0, 1);
		}
	},

	onEditPaymentTypeSpecialKey : function(field, e) {
		var me = this;

		if (me.__readonly) {
			return;
		}

		if (e.getKey() == e.ENTER) {
			me.editBillMemo.focus();
		}
	},

	getGoodsGrid : function() {
		var me = this;
		if (me.__goodsGrid) {
			return me.__goodsGrid;
		}
		var modelName = "PSIPWBillDetail_EditForm";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "goodsId", "goodsCode", "goodsName",
							"goodsSpec", "unitName", "goodsCount", {
								name : "goodsMoney",
								type : "float"
							}, "goodsPrice", "memo", "poBillDetailId"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : []
				});

		me.__cellEditing = Ext.create("PSI.UX.CellEditing", {
					clicksToEdit : 1,
					listeners : {
						edit : {
							fn : me.cellEditingAfterEdit,
							scope : me
						}
					}
				});

		me.__goodsGrid = Ext.create("Ext.grid.Panel", {
			viewConfig : {
				enableTextSelection : true
			},
			features : [{
						ftype : "summary"
					}],
			plugins : [me.__cellEditing],
			columnLines : true,
			columns : [{
						xtype : "rownumberer"
					}, {
						header : "商品编码",
						dataIndex : "goodsCode",
						menuDisabled : true,
						sortable : false,
						draggable : false,
						id : "columnGoodsCode"
					}, {
						header : "商品名称",
						dataIndex : "goodsName",
						menuDisabled : true,
						sortable : false,
						draggable : false,
						width : 200
					}, {
						header : "规格型号",
						dataIndex : "goodsSpec",
						menuDisabled : true,
						sortable : false,
						draggable : false,
						width : 200
					}, {
						header : "入库数量",
						dataIndex : "goodsCount",
						menuDisabled : true,
						sortable : false,
						draggable : false,
						align : "right",
						width : 100,
						editor : {
							xtype : "numberfield",
							allowDecimals : PSI.Const.GC_DEC_NUMBER > 0,
							decimalPrecision : PSI.Const.GC_DEC_NUMBER,
							minValue : 0,
							hideTrigger : true
						}
					}, {
						header : "单位",
						dataIndex : "unitName",
						menuDisabled : true,
						sortable : false,
						draggable : false,
						width : 60
					}, {
						header : "采购单价",
						dataIndex : "goodsPrice",
						menuDisabled : true,
						sortable : false,
						draggable : false,
						align : "right",
						xtype : "numbercolumn",
						width : 100,
						id : "columnGoodsPrice",
						summaryRenderer : function() {
							return "采购金额合计";
						},
						hidden : !me.getViewPrice()
					}, {
						header : "采购金额",
						dataIndex : "goodsMoney",
						menuDisabled : true,
						sortable : false,
						draggable : false,
						align : "right",
						xtype : "numbercolumn",
						width : 120,
						id : "columnGoodsMoney",
						summaryType : "sum",
						hidden : !me.getViewPrice()
					}, {
						header : "备注",
						dataIndex : "memo",
						menuDisabled : true,
						sortable : false,
						draggable : false,
						width : 200,
						editor : {
							xtype : "textfield"
						}
					}, {
						header : "",
						id : "columnActionDelete",
						align : "center",
						menuDisabled : true,
						draggable : false,
						width : 50,
						xtype : "actioncolumn",
						items : [{
									icon : me
											.URL("Public/Images/icons/delete.png"),
									tooltip : "删除当前记录",
									handler : function(grid, row) {
										var store = grid.getStore();
										store.remove(store.getAt(row));
										if (store.getCount() == 0) {
											store.add({});
										}
									},
									scope : me
								}]
					}, {
						header : "",
						id : "columnActionAdd",
						align : "center",
						menuDisabled : true,
						draggable : false,
						width : 50,
						xtype : "actioncolumn",
						items : [{
									icon : me
											.URL("Public/Images/icons/insert.png"),
									tooltip : "在当前记录之前插入新记录",
									handler : function(grid, row) {
										var store = grid.getStore();
										store.insert(row, [{}]);
									},
									scope : me
								}]
					}, {
						header : "",
						id : "columnActionAppend",
						align : "center",
						menuDisabled : true,
						draggable : false,
						width : 50,
						xtype : "actioncolumn",
						items : [{
									icon : me
											.URL("Public/Images/icons/add.png"),
									tooltip : "在当前记录之后新增记录",
									handler : function(grid, row) {
										var store = grid.getStore();
										store.insert(row + 1, [{}]);
									},
									scope : me
								}]
					}],
			store : store,
			listeners : {
				cellclick : function() {
					return !me.__readonly;
				}
			}
		});

		return me.__goodsGrid;
	},

	__setGoodsInfo : function(data) {
		var me = this;
		var item = me.getGoodsGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var goods = item[0];

		goods.set("goodsId", data.id);
		goods.set("goodsCode", data.code);
		goods.set("goodsName", data.name);
		goods.set("unitName", data.unitName);
		goods.set("goodsSpec", data.spec);

		// 设置建议采购价
		goods.set("goodsPrice", data.purchasePrice);

		me.calcMoney(goods);
	},

	cellEditingAfterEdit : function(editor, e) {
		var me = this;

		if (me.__readonly) {
			return;
		}

		var fieldName = e.field;
		var goods = e.record;
		var oldValue = e.originalValue;
		if (fieldName == "memo") {
			var store = me.getGoodsGrid().getStore();
			if (e.rowIdx == store.getCount() - 1) {
				store.add({});
			}
			e.rowIdx += 1;
			me.getGoodsGrid().getSelectionModel().select(e.rowIdx);
			me.__cellEditing.startEdit(e.rowIdx, 1);
		} else if (fieldName == "goodsMoney") {
			if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
				me.calcPrice(goods);
			}
		} else if (fieldName == "goodsCount") {
			if (goods.get(fieldName) != oldValue) {
				me.calcMoney(goods);
			}
		} else if (fieldName == "goodsPrice") {
			if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
				me.calcMoney(goods);
			}
		}
	},

	calcMoney : function(goods) {
		if (!goods) {
			return;
		}

		goods.set("goodsMoney", goods.get("goodsCount")
						* goods.get("goodsPrice"));
	},

	calcPrice : function(goods) {
		if (!goods) {
			return;
		}

		var goodsCount = goods.get("goodsCount");
		if (goodsCount && goodsCount != 0) {
			goods.set("goodsPrice", goods.get("goodsMoney")
							/ goods.get("goodsCount"));
		}
	},

	getSaveData : function() {
		var me = this;

		var result = {
			id : me.editHiddenId.getValue(),
			bizDT : Ext.Date.format(me.editBizDT.getValue(), "Y-m-d"),
			supplierId : me.editSupplier.getIdValue(),
			warehouseId : me.editWarehouse.getIdValue(),
			bizUserId : me.editBizUser.getIdValue(),
			paymentType : me.editPaymentType.getValue(),
			pobillRef : me.getPobillRef(),
			billMemo : me.editBillMemo.getValue(),
			viewPrice : me.getViewPrice() ? "1" : "0",
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
						goodsMoney : item.get("goodsMoney"),
						memo : item.get("memo"),
						poBillDetailId : item.get("poBillDetailId")
					});
		}

		return Ext.JSON.encode(result);
	},

	setBillReadonly : function() {
		var me = this;
		me.__readonly = true;
		me.setTitle("<span style='font-size:160%;'>查看采购入库单</span>");
		me.buttonSave.setDisabled(true);
		me.buttonCancel.setText("关闭");
		me.editBizDT.setReadOnly(true);
		me.editSupplier.setReadOnly(true);
		me.editWarehouse.setReadOnly(true);
		me.editBizUser.setReadOnly(true);
		me.editPaymentType.setReadOnly(true);
		me.editBillMemo.setReadOnly(true);
		me.columnActionDelete.hide();
		me.columnActionAdd.hide();
		me.columnActionAppend.hide();
		me.displayFieldBarcode.setDisabled(true);
		me.editBarcode.setDisabled(true);
	},

	onEditBarcodeKeydown : function(field, e) {
		if (e.getKey() == e.ENTER) {
			var me = this;

			var el = Ext.getBody();
			el.mask("查询中...");
			var r = {
				url : me.URL("Home/Goods/queryGoodsInfoByBarcodeForPW"),
				params : {
					barcode : field.getValue()
				},
				callback : function(options, success, response) {
					el.unmask();

					if (success) {
						var data = me.decodeJSON(response.responseText);
						if (data.success) {
							var goods = {
								goodsId : data.id,
								goodsCode : data.code,
								goodsName : data.name,
								goodsSpec : data.spec,
								unitName : data.unitName,
								goodsCount : 1,
								goodsPrice : data.purchasePrice,
								goodsMoney : data.purchasePrice
							};
							me.addGoodsByBarCode(goods);
							var edit = me.editBarcode;
							edit.setValue(null);
							edit.focus();
						} else {
							var edit = me.editBarcode;
							edit.setValue(null);
							me.showInfo(data.msg, function() {
										edit.focus();
									});
						}
					} else {
						me.showInfo("网络错误");
					}
				}
			};
			me.ajax(r);
		}
	},

	addGoodsByBarCode : function(goods) {
		if (!goods) {
			return;
		}

		var me = this;
		var store = me.getGoodsGrid().getStore();

		if (store.getCount() == 1) {
			var r = store.getAt(0);
			var id = r.get("goodsId");
			if (id == null || id == "") {
				store.removeAll();
			}
		}

		store.add(goods);
	}
});