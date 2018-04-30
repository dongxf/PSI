// Dom7
var $$ = Dom7;

// Framework7 App main instance
var app = new Framework7({
			root : '#app',
			id : 'io.framework7.testapp',
			name : 'PSI',
			theme : 'auto', // Automatic theme detection
			// App root data
			data : function() {
				return {
					PSI : {
						productionName : productionName
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