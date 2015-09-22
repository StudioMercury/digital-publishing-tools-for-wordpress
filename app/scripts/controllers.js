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
.controller('settingsCtrl', function($scope, $rootScope, $stateParams, environment, CMS, Settings, Alerts, _){

	$scope.config = environment;
	$scope.settings = Settings;

	$scope.addDevice = function(){
		$scope.settings.devices.push( {} );
	}
	
	$scope.removeDevice = function(device){
		$scope.settings.devices = _.reject($scope.settings.devices, function(savedDevice){ 
			return savedDevice == device; 
		});
	}
	
	$scope.clearDevices = function(){
		$scope.settings.devices = [];
	}
		
	$scope.save = function(){
		Alerts.add("warning", "Saving settings", { group: 'saving' });
		$scope.settings.save()
		.then(function(settings){
			if(!_.isEmpty(settings)){ $scope.settings = settings; }
			Alerts.add("success", "Settings were saved.", { group: 'saving' });
		});
	}
	
	$scope.refresh = function(){
		Alerts.add("warning", "Refreshing settings", { group: 'refresh' });
		Settings.getSettings()
		.then(function(settings){
			if(!_.isEmpty(settings)){ $scope.settings = settings; }
			Alerts.add("success", "Settings were refreshed.", { group: 'refresh' });
		});
	}
	
	$scope.reset = function(){
		$scope.settings = $scope.settings.reset();
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
		$scope.close();
	};
	
	$scope.close = function () {
		$modalInstance.close();
	};
})

/* Bulk Action Modal */

.controller('modalBulkActionCtrl', function($scope, $modalInstance, environment, $state, _, $q){	
	$scope.config = environment; // Environment Variables
	
	// Array of action promises
	$scope.actions = [];
	$scope.selectedEntities = $scope.getSelectedEntities();
	$scope.complete = false;
	$scope.completeCount = 0;
		
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
		
	$scope.cancel = function () {
		$scope.close();
	};
	
	$scope.close = function () {
		$modalInstance.close();
	};
})


/* ARTICLE: List */
.controller('articleListCtrl', function($scope, $rootScope, $stateParams, $log, $modal, environment, articles, Settings, Article, _){	
	$scope.config = environment;
	$scope.entities = articles;
	
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
				templateUrl: $scope.config.viewPath + 'views/modals/modal-group-action.html',
				scope: $scope,
				windowClass: 'bulkAction',
				controller: 'modalBulkActionCtrl',
			});
	}
	
	$scope.push = function(){
		$scope.action = "update";
		$scope.header = "Updating";
		var modalInstance = $modal.open({
				animation: true,
				templateUrl: $scope.config.viewPath + 'views/modals/modal-group-action.html',
				scope: $scope,
				windowClass: 'bulkAction',
				controller: 'modalBulkActionCtrl',
			});
	}
	
	$scope.delete = function(inCloud){
		$scope.action = "delete";
		$scope.header = "Deleting";
		var modalInstance = $modal.open({
				animation: true,
				templateUrl: $scope.config.viewPath + 'views/modals/modal-group-delete.html',
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
	
	$scope.editMetadata = function(){
		$scope.action = "pushMetadata";
		$scope.header = "Pushing Metadata";
		var modalInstance = $modal.open({
				animation: true,
				templateUrl: $scope.config.viewPath + 'views/modals/modal-group-edit.html',
				scope: $scope,
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
		$scope.article.contents = response.entity.contents;
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
		$scope.article.contents = response.entity.contents;
	};
	
	$scope.download = function(){
		$scope.article.download();
	}
	
	$scope.create = function(inCloud){
		Alerts.add("warning", "Creating article in the cloud", { group: 'create' });
		$scope.article.create(inCloud)
		.then(function(article){
			if(!_.isEmpty(article)){ $scope.article = article; }
			Alerts.add("success", "Article was created successfully in the cloud", { group: 'create' });
		});
	};
	
	$scope.push_all = function(){
		Alerts.add("warning", "Pushing Article, Metadata, and Contents to the cloud", { group: 'push_all' }	);	
		$scope.article.push()
		.then(function(article){
			if(!_.isEmpty(article)){ $scope.article = article; }
			Alerts.add("success", "Article's metadata, folio, and contents were successfully updated in the cloud", { group: 'push_all' });
		});
	};

	$scope.push_metadata = function(){
		Alerts.add("warning", "Pushing article metadata", { group: 'push_metadata' });
		$scope.article.pushMetadata()
		.then(function(article){
			if(!_.isEmpty(article)){ $scope.article = article; }
			Alerts.add("success", "Article's metadata was successfully updated in the cloud", { group: 'push_metadata' });
		});
	};
	
	$scope.push_content = function(){
		Alerts.add("warning", "Pushing article content and images", { group: 'push_content' });
		$scope.article.pushContents()
		.then(function(article){
			if(!_.isEmpty(article)){ $scope.article = article; }
			Alerts.add("success", "Article was successfully published", { group: 'push_content' });
		});
	};
	
	$scope.push_article_folio = function(){
		Alerts.add("warning", "Bundling article and pushing it to the cloud", { group: 'push_article' });
		$scope.article.pushArticleFolio()
		.then(function(article){
			if(!_.isEmpty(article)){ $scope.article = article; }
			Alerts.add("success", "Article was successfully published", { group: 'push_article' });
		});
	};
	
	$scope.publish = function(){
		Alerts.add("warning", "Publishing article", { group: 'publish' });
		$scope.article.publish()
		.then(function(article){
			if(!_.isEmpty(article)){ $scope.article = article; }
			Alerts.add("success", "Article was queued to be published. You will NOT recieve a notice when the article has been published.", { group: 'publish' });
		});
	};
	
	$scope.link = function(){
		Alerts.add("warning", "Linking article in the cloud", { group: 'link' });
		$scope.article.link()
		.then(function(article){
			if(!_.isEmpty(article)){ $scope.article = article; }
			Alerts.add("success", "Article linked successfully", { group: 'link' });
		});
	};
	
	$scope.unlink = function(){
		Alerts.add("warning", "Unlinking article from the cloud", { group: 'link' });
		$scope.article.unlink()
		.then(function(article){
			if(!_.isEmpty(article)){ $scope.article = article; }
			Alerts.add("success", "Article unlinked successfully", { group: 'link' });
		});
	};
	
	$scope.save = function(){
		Alerts.add("warning", "Saving the article", { group: 'save' });
		$scope.article.save()
		.then(function(article){
			if(!_.isEmpty(article)){ $scope.article = article; }
			Alerts.add("success", "Article saved successfully", { group: 'save' });
		});
	};
	
	$scope.openSync = function(article){
		// Open Sync modal
		var modalInstance = $modal.open({
			animation: true,
			templateUrl: $scope.config.viewPath + 'views/modals/modal-sync-article.html',
			scope: $scope,
			controller: 'syncCtrl',
		});
		
		modalInstance.result.then(function (){
	    	$scope.article.sync()
			.then(function(article){
				if(!_.isEmpty(article)){ $scope.article = article; }
				Alerts.add("success", "Article was successfully synced", { group: 'sync' });
			});
	    });
	};
	
})

/* MODAL: SYNC*/
.controller('syncCtrl', function($scope, $stateParams, environment, $modalInstance){
	$scope.cancel = function () {
		$modalInstance.dismiss('cancel');
	};
	
	$scope.sync = function(){
    	$modalInstance.close();
	}
		
	$scope.close = function () {
		$modalInstance.dismiss('cancel');
	};
})

/* COLLECTION: List */
.controller('collectionListCtrl', function($scope, $rootScope, $stateParams, Collection, environment){
	$scope.config = environment;
	$scope.collections = [];
})

/* COLLECTION: Single */
.controller('collectionSingleCtrl', function($scope, $rootScope, $stateParams, Collection, environment){
	$scope.config = environment;

})

/* FOLIO: List */
.controller('folioCtrl', function($scope, $rootScope, $stateParams, Folio, environment){
	$scope.config = environment;

})

/* FOLIO: Single */
.controller('folioSingleCtrl', function($scope, $rootScope, $stateParams, Folio, environment){
	$scope.config = environment;

});