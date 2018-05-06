var app = new Framework7({
			root : '#app',
			id : 'com.gitee.crm8000.psi',
			name : 'PSI',
			theme : 'auto',
			data : function() {
				return {
					PSI : {
						productionName : productionName,
						demoLoginInfo : demoLoginInfo,
						baseURI : baseURI,
						userIsLoggedIn : userIsLoggedIn
					}
				};
			},
			methods : {},
			routes : routesPSI,
			dialog : {
				buttonOk : "确定",
				buttonCancel : "取消"
			}
		});

var mainView = app.views.create('.view-main', {
			url : '/'
		});