Ext.define("PSI.Warehouse.OrgViewForm", {
	extend : "Ext.window.Window",
	config : {
		parentForm : null,
		warehouseId : null,
		fid : null
	},
	initComponent : function() {
		var me = this;

		var buttons = [];

		buttons.push({
			text : "关闭",
			formBind : true,
			iconCls : "PSI-button-ok",
			handler : function() {
				me.onOK();
			},
			scope : me
		});

		Ext.apply(me, {
			title : "查看仓库操作人",
			modal : true,
			onEsc : Ext.emptyFn,
			width : 1000,
			height : 600,
			maximized : true,
			layout : "border",
			listeners : {
				show : {
					fn : me.onWndShow,
					scope : me
				},
				close : {
					fn : me.onWndClose,
					scope : me
				}
			},
			items : [ {
				region : "west",
				width : "50%",
				split : true,
				layout : "fit",
				border : 0,
				items : [ me.getOrgTreeGrid() ]
			}, {
				region : "center",
				layout : "fit",
				items : [me.getWarehouseTreeGrid()]
			} ],
			buttons : buttons
		});

		me.callParent(arguments);
	},
	// private
	onOK : function() {
		this.close();
	},
	onWndClose : function() {
	},
	onWndShow : function() {
	},
	getOrgTreeGrid : function() {
		var me = this;
		if (me.__treeGrid) {
			return me.__treeGrid;
		}

		var modelName = "PSIOrgModel_EditOrgForm";
		Ext.define(modelName,
				{
					extend : "Ext.data.Model",
					fields : [ "id", "text", "fullName", "orgCode", "leaf",
							"children" ]
				});

		var orgStore = Ext.create("Ext.data.TreeStore", {
			model : modelName,
			proxy : {
				type : "ajax",
				url : PSI.Const.BASE_URL + "Home/Warehouse/allOrgs"
			}
		});

		me.__treeGrid = Ext.create("Ext.tree.Panel", {
			store : orgStore,
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
				items : [ {
					xtype : "treecolumn",
					text : "名称",
					dataIndex : "text",
					width : 220
				}, {
					text : "编码",
					dataIndex : "orgCode",
					width : 100
				}, {
					text : "全名",
					dataIndex : "fullName",
					flex : 1
				} ]
			}
		});

		return me.__treeGrid;
	},
	getWarehouseTreeGrid : function() {
		var me = this;
		if (me.__treeGridWarehouse) {
			return me.__treeGridWarehouse;
		}

		var modelName = "PSIOrgModel_EditOrgForm";
		Ext.define(modelName,
				{
					extend : "Ext.data.Model",
					fields : [ "id", "billType", "code", "name", "leaf",
							"children" ]
				});

		var store = Ext.create("Ext.data.TreeStore", {
			model : modelName,
			autoLoad : false,
			data: []
		});

		me.__treeGridWarehouse = Ext.create("Ext.tree.Panel", {
			store : store,
			rootVisible : false,
			root: {},
			useArrows : true,
			columns : {
				defaults : {
					sortable : false,
					menuDisabled : true,
					draggable : false
				},
				items : [ {
					xtype : "treecolumn",
					text : "业务类型",
					dataIndex : "billType",
					width : 220
				}, {
					text : "编码",
					dataIndex : "code",
					width : 100
				}, {
					text : "仓库名次",
					dataIndex : "name",
					flex : 1
				} ]
			}
		});

		return me.__treeGridWarehouse;
	}
});