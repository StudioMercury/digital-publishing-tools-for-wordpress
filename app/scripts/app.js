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
	'ngAnimate'])

.run(function($rootScope, name, environment, Loading) {
	
	$rootScope.name = name;
	$rootScope.showSideMenu = true;
	$rootScope.config = environment;
	
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
	$urlRouterProvider.otherwise('/settings');

	$stateProvider
	
		.state('settings', {
			url: '/settings',
			templateUrl: config.viewPath + 'views/settings/settings.html',
			controller: 'settingsCtrl',
			resolve: {
				settings: function(Settings, Loading){
					return Settings.update().then(function(settings){
							Loading.hide();
						return settings;
					});
				}
			}
		})
		
		.state('about', {
			url: '/about',
			templateUrl: config.viewPath + 'views/about/about.html',
			controller: 'aboutCtrl',
		})
		
		.state('folio', {
			abstract: true,
	        url: '/folio',
	        template: '<ui-view/>'
		})
		
		.state('folio.list', {
			url: '/list',
			templateUrl: config.viewPath + 'views/folio/list.html',
			controller: 'folioCtrl',
			resolve: {
				folios: function(CMS){
					return CMS.getEntityList('folio').then(function(data){
						return data.data;
					});
				}
			}
		})
		
		.state('folio.single', {
			url: '/:id',
			templateUrl: config.viewPath + 'views/folio/single.html',
			controller: 'folioSingleCtrl',
		})
		
		.state('collection', {
			abstract: true,
	        url: '/collection',
	        template: '<ui-view/>'
		})
		
		.state('collection.list', {
			url: '/list',
			templateUrl: config.viewPath + 'views/collection/list.html',
			controller: 'collectionListCtrl',
		})
		
		.state('collection.view', {
			url: '/:id',
			templateUrl: config.viewPath + 'views/collection/single.html',
			controller: 'collectionSingleCtrl',
			resolve: {
				collections: function(CMS){
					return CMS.getEntityList('collection').then(function(data){
						return data.data;
					});
				}
			}
		})
		
		.state('collection.single', {
			url: '/:id/edit',
			templateUrl: config.viewPath + 'views/collection/single.html',
			controller: 'collectionSingleCtrl',
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
		})
		
		.state('banner', {
			abstract: true,
	        url: '/banner',
	        template: '<ui-view/>'
		})
		
		.state('banner.list', {
			url: '/list',
			templateUrl: config.viewPath + 'views/banner/list.html',
			controller: 'bannerCtrl',
/*
			resolve: {
				folios: function(CMS){
					return CMS.getEntityList('banner').then(function(data){
						return data.data;
					});
				}
			}
*/
		})
		
		.state('banner.single', {
			url: '/:id',
			templateUrl: config.viewPath + 'views/banner/single.html',
			controller: 'bannerSingleCtrl',
		});
	
	$urlRouterProvider.when('/article', '/article/list');

});