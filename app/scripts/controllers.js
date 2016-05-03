angular.module('AdobePublishForCMS.controllers', [])

/* Side Left Menu */
.controller('sideMenu', function($scope, $state, $rootScope, $stateParams, environment, Settings){	
	$scope.$state = $state;
	$scope.config = environment;
	$scope.settings = Settings;
})

/* Alerts */
.controller('alerts', function($scope, Alerts, environment){
	$scope.config = environment;
	$scope.alerts = Alerts;
})

/* Loading */
.controller('loading', function($scope, Loading, environment){
	$scope.config = environment;
	$scope.text = Loading.text;
	$scope.show = function(){
		return Loading.isVisible();
	}
})

/* Settings */
.controller('settingsCtrl', function($scope, $rootScope, $stateParams, environment, CMS, Settings, Alerts, _, $modal){

	$scope.config = environment;
	$scope.settings = Settings;

/* RENDITIONS */
	$scope.addDevice = function(){
		$scope.settings.dps_devices.push( {} );
	}
	
	$scope.removeDevice = function(device){
		$scope.settings.dps_devices = _.reject($scope.settings.dps_devices, function(savedDevice){ 
			return savedDevice == device; 
		});
	}
	
	$scope.clearDevices = function(){
		$scope.settings.dps_devices = [];
	}
	
	$scope.addImage = function(){
		$scope.settings.dps_images.push( {} );
	}
	
	$scope.removeImage = function(image){
		$scope.settings.dps_images = _.reject($scope.settings.dps_images, function(savedImage){ 
			return savedImage == image; 
		});
	}

/* IMPORTING */
	$scope.add_import_preset = function(){		
		$scope.settings.importPresets.push({
			"entityType" : "article",
		});	
		$scope.edit_import_preset(_.last($scope.settings.importPresets));
	}
	
	$scope.edit_import_preset = function(preset){
		$scope.editPreset = preset;
		var modalInstance = $modal.open({
				animation: true,
				size: 'large',
				templateUrl: $scope.config.viewPath + 'views/modals/modal-settings-importing-edit.html',
				scope: $scope,
				controller: 'modalCtrl',
			});
	}
	
	$scope.delete_import_preset = function(preset){
		$scope.settings.importPresets = _.reject($scope.settings.importPresets, function(savedPreset){ 
			return savedPreset == preset; 
		});
	}
		
/* GENERIC */
	$scope.save = function(){
		$scope.settings.save();
	}
	
	$scope.refresh = function(){
		$scope.settings.update();
	}
	
})

/* ABOUT */
.controller('aboutCtrl', function($scope, $rootScope, $stateParams, environment){
	$scope.config = environment;
})

/* actionModal */
.controller('modalCtrl', function($scope, $modalInstance, environment, Alerts, $state, _, $q){	
	$scope.config = environment; // Environment Variables
	
	$scope.action = {};
	$scope.alert = function(message){
		alert(message);
	}
	$scope.doAction = function($actionPromise, action){
		console.log("Doing action ("+action+") on entity: ", $scope.entity);
		
		if(_.isArray($actionPromise)){
			$promise = $q.all($actionPromise);
		}else{
			$promise = $actionPromise;
		}
		
		$promise.then(function(){
			Alerts.add("success", "Success", { group: action });
			if(_.isFunction($scope.onActionComplete)){
				$scope.onActionComplete();
			}else{
				$state.reload();
			}
			$modalInstance.close();
		}).catch(function(error){
			$modalInstance.close();
		});
	};
	
	$scope.cancel = function () {
		$modalInstance.dismiss('cancel');
	};
	
	$scope.close = function ($data) {
		if(!_.isEmpty($data)){
			$modalInstance.close($data);
		}else{
			$modalInstance.close();
		}
	};
})

/* Bulk Action Modal */

.controller('modalBulkActionCtrl', function($scope, $modalInstance, environment, $state, _, $q){	
	$scope.config = environment; // Environment Variables
	
	$scope.actions = [];
	$scope.selectedEntities = $scope.getSelectedEntities();
	$scope.complete = false;
	$scope.completeCount = 0;
	$scope.startingAction = false;

	$scope.startAction = function(){
		$scope.startingAction = true;
		
		// Array of action promises
		_.each($scope.selectedEntities, function($entity){
			$scope.actions.push($entity[$scope.action]().then(function(response){
				$entity.action = {};
				$entity.action.response = true;
				console.log("TRUE", $entity);
				$scope.completeCount ++;
			}, function(){
				$entity.action = {};
				$entity.action.response = false;
				console.log("FALSE", $entity);
				$entity.action.message = response;
				$scope.completeCount ++;
			}));
		});
		
		$q.all($scope.actions).then(function(){
			// show close button
			$scope.complete = true;
		});
	}
		
	$scope.cancel = function () {
		$state.reload();
		$modalInstance.close();
	};
	
	$scope.close = function () {
		$state.reload();
		$modalInstance.close();
	};
})


/* ARTICLE: List */
.controller('articleListCtrl', function($scope, $rootScope, $stateParams, $log, $modal, environment, Settings, Article, _, articles){	
	$scope.config = environment;
	$scope.entities = articles;
	$scope.settings = Settings;
	
	$scope.listBusy = false;
	$scope.listFinished = false;
	$scope.filters = {
		limit: 30,
		page: 2,
		order: 'DESC'
	}
	
	$scope.filterBy = function(field){
		$scope.filters.page = 1;
		$scope.filters.orderby = field;
		$scope.filters.order = (_.has($scope.filters, 'order') && $scope.filters.order == "DESC") ? "ASC" : "DESC";
		$scope.getArticles(true);
	}
	
	/* GET ARTICLES */
	$scope.getArticles = function(refresh){
		var entity = new Article();
			entity.all($scope.filters).then(function(articles){
				if(articles.length > 0){
					if(!_.isUndefined(refresh)){
						$scope.entities = [];
					}
					_.each(articles, function(article){
						$scope.entities.push(article);
						console.log($scope.entities);
					});
					$scope.listBusy = false;
				}else{
					$scope.listFinished = true;
				}
				$scope.filters.page++;
			});
	}
	$scope.getArticles();
	
	$scope.nextPage = function(){
		$scope.listBusy = true;
		$scope.getArticles();
	}
	
	$scope.addArticle = function(){
		$scope.entity = new Article();
		var modalInstance = $modal.open({
				animation: true,
				templateUrl: $scope.config.viewPath + 'views/modals/modal-add-entity.html',
				scope: $scope,
				controller: 'modalCtrl',
			});
	};
	
	$scope.toggleAll = function(){
		_.each($scope.entities, function($entity){
			$entity.checked = $scope.toggleAll.checked;
		});
	}
	
	$scope.getSelectedEntities = function(){
		return _.where($scope.entities, { checked: true });
	}
	
	$scope.unlink = function(){
		$scope.action = "unlink";
		$scope.header = "Unlinking";
		var modalInstance = $modal.open({
				animation: true,
				templateUrl: $scope.config.viewPath + 'views/modals/modal-group-unlink.html',
				scope: $scope,
				windowClass: 'bulkAction',
				controller: 'modalBulkActionCtrl',
			});
	}
	
	$scope.delete = function(){
		$scope.action = "delete";
		$scope.header = "Deleting";
		var modalInstance = $modal.open({
				animation: true,
				templateUrl: $scope.config.viewPath + 'views/modals/modal-group-delete.html',
				scope: $scope,
				controller: 'modalBulkActionCtrl',
			});
	}
	
	$scope.delete_cloud = function(){
		$scope.action = "cloud_delete";
		$scope.header = "Deleting from the cloud";
		var modalInstance = $modal.open({
				animation: true,
				templateUrl: $scope.config.viewPath + 'views/modals/modal-group-delete-cloud.html',
				scope: $scope,
				controller: 'modalBulkActionCtrl',
			});
	}
	
	$scope.publish = function(){
		$scope.action = "publish";
		$scope.header = "Publishing";
		var modalInstance = $modal.open({
				animation: true,
				templateUrl: $scope.config.viewPath + 'views/modals/modal-group-action.html',
				scope: $scope,
				windowClass: 'bulkAction',
				controller: 'modalBulkActionCtrl',
			});
	}
	
})

/* ARTICLE: Single */
.controller('articleSingleCtrl', function($scope, $rootScope, $stateParams, FileUploader, article, environment, Alerts, $timeout, $state, Settings, $modal){	
	
	$scope.config = environment;	
	$scope.settings = Settings;
	
	// Single Article
	$scope.article = article;
	console.log("Current Article", $scope.article);

	$scope.getCloudError = function(){
		if($scope.settings.secret.length < 1 && Settings.key.length < 1){
			return "There is no valid API Key/Secret.";
		}else if($scope.settings.publications.length < 1){
			return "There are no publications available.";
		}else{
			return "";
		}
	}
		
	/* MODAL FOR DELETING */
	$scope.delete = function(){
		$scope.entity = $scope.article;
		$scope.onActionComplete = function(){
			$state.go('article.list');
		}
		var modalInstance = $modal.open({
				animation: true,
				templateUrl: $scope.config.viewPath + 'views/modals/modal-delete-entity.html',
				scope: $scope,
				controller: 'modalCtrl',
			});
	};
	
	$scope.delete_cloud = function(){
		$scope.entity = $scope.article;
		var modalInstance = $modal.open({
				animation: true,
				templateUrl: $scope.config.viewPath + 'views/modals/modal-delete-cloud-entity.html',
				scope: $scope,
				controller: 'modalCtrl',
			});
	};

	/* Article Thumbnail */
	$scope.articleThumb = new FileUploader({
		url: environment.endpoint + '?action=add_entity_content',
		queueLimit: 1,
		removeAfterUpload: true,
		headers: {
			'WP-nonce': environment.nonce
		},
		autoUpload: true
	});
	
	$scope.articleThumb.onBeforeUploadItem = function (item) {
	    item.formData = [{ entity: JSON.stringify($scope.article), contentType: 'thumbnail' }];
	};
	
	$scope.articleThumb.onSuccessItem = function(item, response, status, headers){
		if(_.has(response, 'entity')){ $scope.article.refresh(response.entity); }
	};
	
	/* Social Media Thumb */
	$scope.socialMediaThumb = new FileUploader({
		url: environment.endpoint + '?action=add_entity_content',
		queueLimit: 1,
		removeAfterUpload: true,
		headers: {
			'WP-nonce': environment.nonce
		},
		autoUpload: true
	});
	
	$scope.socialMediaThumb.onBeforeUploadItem = function (item) {
	    item.formData = [{ entity: JSON.stringify($scope.article), contentType: 'socialSharing' }];
	};
	
	$scope.socialMediaThumb.onSuccessItem = function(item, response, status, headers){
		if(_.has(response, 'entity')){ $scope.article.refresh(response.entity); }
	};
	
	$scope.download = function(){
		$scope.article.download();
	}
	
	$scope.create = function(inCloud){
		$scope.article.create(inCloud);
	};
	
	$scope.push_all = function(){
		$scope.article.pushMetadata().finally(function(){
			$scope.article.pushContents().finally(function(){
				$scope.article.pushArticleFolio();
			});
		});
	};

	$scope.push_metadata = function(){
		$scope.article.pushMetadata();
	};
	
	$scope.push_content = function(){
		$scope.article.pushContents();
	};
	
	$scope.push_article_folio = function(){
		$scope.article.pushArticleFolio();
	};
	
	$scope.publish = function(){
		$scope.article.publish();
	};
	
	$scope.link = function(){
		$scope.entity = $scope.article;
		var modalInstance = $modal.open({
				animation: true,
				templateUrl: $scope.config.viewPath + 'views/modals/modal-link-entity.html',
				scope: $scope,
				controller: 'modalCtrl',
			});
	};
	
	$scope.unlink = function(){
		$scope.article.unlink();
	};
	
	$scope.save = function(){
		$scope.article.save();
	};
	
	$scope.previewDevice = function(device){
		$scope.selectedDevice = device;
	};
	
	$scope.selectedDevice = {
		title: 'Desktop',
		width: '100%'
	};
	
	$scope.openSync = function(){
		$scope.entity = $scope.article;
		
		// Open Sync modal
		var modalInstance = $modal.open({
			animation: true,
			templateUrl: $scope.config.viewPath + 'views/modals/modal-sync-article.html',
			scope: $scope,
			controller: 'modalCtrl',
		});
		
		modalInstance.result.then(function (importPreset){
	    	$scope.article.sync(importPreset);
	    });
	};
	
});