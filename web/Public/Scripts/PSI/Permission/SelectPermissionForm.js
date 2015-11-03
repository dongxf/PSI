// 选择权限
Ext.define("PSI.Permission.SelectPermissionForm", {
    extend: "Ext.window.Window",

    config: {
        idList: null, // idList是数组
        parentForm: null
    },

    title: "选择权限",
    width: 600,
    height: 500,
    modal: true,
    resizable: false,
    layout: "border",

    initComponent: function () {
        var me = this;
        Ext.define("PSIPermission_SelectPermissionForm", {
            extend: "Ext.data.Model",
            fields: ["id", "name"]
        });

        var permissionStore = Ext.create("Ext.data.Store", {
            model: "PSIPermission_SelectPermissionForm",
            autoLoad: false,
            data: []
        });

        var permissionGrid = Ext.create("Ext.grid.Panel", {
            title: "角色的权限",
            padding: 5,
            selModel: {
                mode: "MULTI"
            },
            selType: "checkboxmodel",
            viewConfig: {
                deferEmptyText: false,
                emptyText: "所有权限都已经加入到当前角色中了"
            },
            store: permissionStore,
            columns: [
                { header: "权限名称", dataIndex: "name", flex: 1, menuDisabled: true }
            ]
        });

        this.permissionGrid = permissionGrid;

        Ext.apply(me, {
        	padding: 5,
            items: [{
            	region: "center",
            	layout: "fit",
            	border: 0,
            	items: [permissionGrid]
            }, {
            	region: "south",
            	layout: {
					type : "table",
					columns : 2
				},
            	border: 0,
            	height: 40,
            	items: [ {
            		xtype: "textfield",
            		fieldLabel: "数据域",
            		margin: "5 5 5 5",
            		labelWidth: 60,
                    labelAlign: "right",
                    labelSeparator: "",
                    width: 480,
                    readOnly: true,
                    id: "editDataOrg"
            	},{
            		xtype: "hidden",
            		id: "editDataOrgIdList"
            	},{
            		xtype: "button",
            		text: "选择数据域",
            		handler: me.onSelectDataOrg,
            		scope: me
            	}]
            }],
            buttons: [{
                text: "确定",
                formBind: true,
                iconCls: "PSI-button-ok",
                handler: this.onOK,
                scope: this
            }, { text: "取消", handler: function () { me.close(); }, scope: me }
            ],
            listeners: {
                show: me.onWndShow
            }
        });

        me.callParent(arguments);
    },

    onWndShow: function () {
        var me = this;
        var idList = me.getIdList();
        var permissionStore = me.permissionGrid.getStore();

        var el = me.getEl() || Ext.getBody();
        el.mask("数据加载中...");
        Ext.Ajax.request({
            url: PSI.Const.BASE_URL + "Home/Permission/selectPermission",
            params: { idList: idList.join() },
            method: "POST",
            callback: function (options, success, response) {
                permissionStore.removeAll();

                if (success) {
                    var data = Ext.JSON.decode(response.responseText);

                    for (var i = 0; i < data.length; i++) {
                        var item = data[i];
                        permissionStore.add({ id: item.id, name: item.name });
                    }
                }

                el.unmask();
            }
        });
    },

    onOK: function () {
    	var me = this;
        var grid = me.permissionGrid;

        var items = grid.getSelectionModel().getSelection();
        if (items == null || items.length == 0) {
            PSI.MsgBox.showInfo("没有选择权限");

            return;
        }
        
        var dataOrgList = Ext.getCmp("editDataOrgIdList").getValue();
        if (!dataOrgList) {
        	PSI.MsgBox.showInfo("没有选择数据域");
        	return;
        }

        if (me.getParentForm()) {
            me.getParentForm().setSelectedPermission(items);
        }

        me.close();
    },
    
    onSelectDataOrg: function() {
    	var me = this;
    	var form = Ext.create("PSI.Permission.SelectDataOrgForm", {
    		parentForm: me
    	});
    	form.show();
    },
    
    setDataOrgList: function(fullNameList, dataOrgList) {
    	Ext.getCmp("editDataOrg").setValue(fullNameList);
    	Ext.getCmp("editDataOrgIdList").setValue(dataOrgList);
    }
});