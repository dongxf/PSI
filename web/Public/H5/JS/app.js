// Dom7
var $$ = Dom7;

// Framework7 App main instance
var app = new Framework7({
			root : '#app',
			id : 'com.gitee.crm8000.psi',
			name : 'PSI',
			theme : 'auto', // Automatic theme detection
			// App root data
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
			// App root methods
			methods : {},
			// App routes
			routes : routesPSI
		});

// Init/Create main view
var mainView = app.views.create('.view-main', {
			url : '/'
		});