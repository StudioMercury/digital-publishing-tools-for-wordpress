angular.module('AdobePublishForCMS', 
   ['ui.router', 
	'AdobePublishForCMS.controllers', 
	'AdobePublishForCMS.services', 
	'AdobePublishForCMS.directives', 
	'AdobePublishForCMS.config', 
	'ui.bootstrap', 
	'ngTagsInput', 
	'angularFileUpload', 
	'ngSanitize',
	'infinite-scroll'])

.run(function($rootScope, name, environment, Loading, $state) {
	
	$rootScope.name = name;
	$rootScope.showSideMenu = true;
	$rootScope.config = environment;
	$rootScope.$state = $state;
	
	$rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams){ 
        Loading.show();
    });

    $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams){ 
    	Loading.hide();
    });
    
    $rootScope.$on('$stateChangeError', function(event, toState, toParams, fromState, fromParams){ 
    	Loading.hide();
    });
    
    $rootScope.$on('$stateNotFound', function(event, toState, toParams, fromState, fromParams){ 
    	Loading.hide();
    });
     
})

.config(function($stateProvider, $urlRouterProvider) {
	
	angular.injector(['AdobePublishForCMS.config']).invoke(function(environment) {
    	config = environment;
    });
        
	// if none of the above states are matched, use this as the fallback
	$urlRouterProvider.otherwise('/article/list');


	$stateProvider
	
		.state('settings', {
			url: '/settings',
			templateUrl: config.viewPath + 'views/settings/settings.html',
			controller: 'settingsCtrl',
			resolve: {
				settings: function(Settings, Loading){
					return Settings.get();
				}
			},
			data: {
				title: "Settings"
			}
		})
		
		.state('about', {
			url: '/about',
			templateUrl: config.viewPath + 'views/about/about.html',
			controller: 'aboutCtrl',
			data: {
				title: "About"
			}
		})
		
		.state('article', {
			abstract: true,
	        url: '/article',
	        template: '<ui-view/>'
	    })
		
		.state('article.list', {
			url: '/list',
			templateUrl: config.viewPath + 'views/article/list.html',
			controller: 'articleListCtrl',
			resolve: {
				articles: function(CMS, _, Article){
					$article = new Article();
					return $article.all();
				}
			},
			data: {
				title: "Articles"
			}
		})
			
		.state('article.view', {
			url: '/:id',
			templateUrl: config.viewPath + 'views/article/single.html',
			controller: 'articleSingleCtrl',
			resolve: {
				article: function(Article, $stateParams){
					$article = new Article();
					return $article.get($stateParams.id);
				}
			},
			data: {
				title: "Article"
			}

		})
		
		.state('article.edit', {
			url: '/:id/edit',
			templateUrl: config.viewPath + 'views/article/single.html',
			controller: 'articleSingleCtrl',
			resolve: {
				article: function(Article, $stateParams){
					$article = new Article();
					return $article.get($stateParams.id);
				}
			}
		});
	
	$urlRouterProvider.when('/article', '/article/list');

});