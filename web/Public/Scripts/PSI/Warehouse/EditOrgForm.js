Ext.define("PSI.Warehouse.EditOrgForm", {
	extend : "Ext.window.Window",
	config : {
		parentForm : null
	},
	initComponent : function() {
		var me = this;

		var buttons = [];

		buttons.push({
			text : "确定",
			formBind : true,
			iconCls : "PSI-button-ok",
			handler : function() {
				me.onOK(false);
			},
			scope : me
		}, {
			text : "取消",
			handler : function() {
				me.close();
			},
			scope : me
		});

		Ext.apply(me, {
			title : "添加组织机构",
			modal : true,
			onEsc : Ext.emptyFn,
			width : 500,
			height : 400,
			layout : "fit",
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
			items : [me.getOrgTreeGrid()],
			buttons : buttons
		});

		me.callParent(arguments);
	},
	// private
	onOK : function() {
		var me = this;
		me.close();
	},
	onWndClose : function() {
	},
	onWndShow : function() {
	},
	getOrgTreeGrid: function() {
		var me = this;
		if (me.__treeGrid) {
			return me.__treeGrid;
		}
		
		var modelName = "PSIOrgModel_EditOrgForm";
        Ext.define(modelName, {
            extend: "Ext.data.Model",
            fields: ["id", "text", "fullName", "orgCode", "leaf", "children"]
        });

        var orgStore = Ext.create("Ext.data.TreeStore", {
            model: modelName,
            proxy: {
                type: "ajax",
                url: PSI.Const.BASE_URL + "Home/Warehouse/allOrgs"
            }
        });

        me.__treeGrid = Ext.create("Ext.tree.Panel", {
            store: orgStore,
            rootVisible: false,
            useArrows: true,
            viewConfig: {
                loadMask: true
            },
            columns: {
                defaults: {
                    sortable: false,
                    menuDisabled: true,
                    draggable: false
                },
                items: [{
                        xtype: "treecolumn",
                        text: "名称",
                        dataIndex: "text",
                        width: 220
                    }, {
                        text: "编码",
                        dataIndex: "orgCode",
                        flex: 1
                    }]
            }
        });
        
        return me.__treeGrid;
	}
});