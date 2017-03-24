/**
 * 仓库 - 主界面
 */
Ext.define("PSI.Warehouse.MainForm", {
	extend : "PSI.AFX.BaseOneGridMainForm",

	config : {
		pAdd : null,
		pEdit : null,
		pDelete : null,
		pEditDataOrg : null
	},

	/**
	 * 重载父类方法
	 */
	afxGetToolbarCmp : function() {
		var me = this;

		return [{
					text : "新增仓库",
					disabled : me.getPAdd() == "0",
					iconCls : "PSI-button-add",
					handler : me.onAddWarehouse,
					scope : me
				}, {
					text : "编辑仓库",
					iconCls : "PSI-button-edit",
					disabled : me.getPEdit() == "0",
					handler : me.onEditWarehouse,
					scope : me
				}, {
					text : "删除仓库",
					disabled : me.getPDelete() == "0",
					iconCls : "PSI-button-delete",
					handler : me.onDeleteWarehouse,
					scope : me
				}, "-", {
					text : "修改数据域",
					disabled : me.getPEditDataOrg() == "0",
					iconCls : "PSI-button-dataorg",
					handler : me.onEditDataOrg,
					scope : me
				}, "-", {
					text : "关闭",
					iconCls : "PSI-button-exit",
					handler : function() {
						window.close();
					}
				}];
	},

	/**
	 * 重载父类方法
	 */
	afxGetRefreshGridURL : function() {
		return "Home/Warehouse/warehouseList";
	},

	/**
	 * 重载父类方法
	 */
	afxGetMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSI_Warehouse_MainForm_PSIWarehouse";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "code", "name", "inited", "dataOrg"]
				});

		me.__mainGrid = Ext.create("Ext.grid.Panel", {
					border : 0,
					viewConfig : {
						enableTextSelection : true
					},
					columnLines : true,
					columns : [{
								xtype : "rownumberer"
							}, {
								header : "仓库编码",
								dataIndex : "code",
								menuDisabled : true,
								sortable : false,
								width : 60
							}, {
								header : "仓库名称",
								dataIndex : "name",
								menuDisabled : true,
								sortable : false,
								width : 200
							}, {
								header : "建账完毕",
								dataIndex : "inited",
								menuDisabled : true,
								sortable : false,
								width : 70,
								renderer : function(value) {
									return value == 1
											? "完毕"
											: "<span style='color:red'>未完</span>";
								}
							}, {
								header : "数据域",
								dataIndex : "dataOrg",
								menuDisabled : true,
								sortable : false
							}],
					store : Ext.create("Ext.data.Store", {
								model : modelName,
								autoLoad : false,
								data : []
							}),
					listeners : {
						itemdblclick : {
							fn : me.onEditWarehouse,
							scope : me
						}
					}
				});

		return me.__mainGrid;
	},

	/**
	 * 新增仓库
	 */
	onAddWarehouse : function() {
		var me = this;

		var form = Ext.create("PSI.Warehouse.EditForm", {
					parentForm : me
				});

		form.show();
	},

	/**
	 * 编辑仓库
	 */
	onEditWarehouse : function() {
		var me = this;

		if (me.getPEdit() == "0") {
			return;
		}

		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("请选择要编辑的仓库");
			return;
		}

		var warehouse = item[0];

		var form = Ext.create("PSI.Warehouse.EditForm", {
					parentForm : me,
					entity : warehouse
				});

		form.show();
	},

	/**
	 * 删除仓库
	 */
	onDeleteWarehouse : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("请选择要删除的仓库");
			return;
		}

		var warehouse = item[0];
		var info = "请确认是否删除仓库 <span style='color:red'>" + warehouse.get("name")
				+ "</span> ?";

		var preIndex = me.getPreIndexInMainGrid(warehouse.get("id"));

		var funcConfirm = function() {
			var el = Ext.getBody();
			el.mask(PSI.Const.LOADING);
			var r = {
				url : me.URL("Home/Warehouse/deleteWarehouse"),
				params : {
					id : warehouse.get("id")
				},
				method : "POST",
				callback : function(options, success, response) {
					el.unmask();
					if (success) {
						var data = me.decodeJSON(response.responseText);
						if (data.success) {
							me.tip("成功完成删除操作");
							me.freshGrid(preIndex);
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
	 * 编辑数据域
	 */
	onEditDataOrg : function() {
		var me = this;

		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.showInfo("请选择要编辑数据域的仓库");
			return;
		}

		var warehouse = item[0];

		var form = Ext.create("PSI.Warehouse.EditDataOrgForm", {
					parentForm : me,
					entity : warehouse
				});

		form.show();
	}
});