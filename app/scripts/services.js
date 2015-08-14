angular.module('AdobePublishForCMS.services', [])

// Adobe API Service
// TODO: direct contact with Adobe's API
.factory('Adobe', function(_, $http){
	/*
	options.beforeSend = function(xhr) {
		xhr.setRequestHeader('X-DPS-Nonce', AdobePublishForCMS.nonce);
	
		if (beforeSend) {
			return beforeSend.apply(this, arguments);
		}
	};
	*/
})

/* LOADING */
.factory('Loading', function(_){
	
	var Loading = {};
		Loading.text = "Loading";	
		Loading.visible = true;
		
		Loading.isVisible = function(){
			return this.visible;
		}
		
		Loading.show = function(){
			this.visible = true;
		};
		
		Loading.hide = function(){
			this.visible = false;
		};
	
	return Loading;
})

.factory('Alerts', function(_, $timeout){
	
	function Alert(type, msg, options) {
		this.type = type;
		this.msg = msg;
		this.options = {
			responseText: (_.has(options,"responseText") && !_.isUndefined(options.responseText)) ? options.responseText : null ,
			time: (_.has(options,"time") && !_.isUndefined(options.time)) ? options.time : 3000 ,
			group: (_.has(options,"group") && !_.isUndefined(options.group)) ? options.group : false ,
			clearGroup: (_.has(options,"clearGroup") && !_.isUndefined(options.clearGroup)) ? options.clearGroup : false, 
			raw: (_.has(options,"raw") && !_.isUndefined(options.raw)) ? options.raw : null, 
		};
		
		if(this.type === "danger" || this.options.time < 0){
			// no timer
		}else{
			this.startTimer();
		}
	};
	
	Alert.prototype.startTimer = function(){
		var $alert = this;
		$timeout( function(){ 
			Alerts.delete($alert);
		}, this.options.time);
	}
	
	var Alerts = {};
		Alerts.alerts = [];
		
		Alerts.add = function(type, msg, options){
			alert = new Alert(type, msg, options);
			
			if(alert.options.group !== false && alert.options.clearGroup !== false){
				Alerts.removeGroup(alert.options.group);
			}
			Alerts.alerts.push( alert );
		};
		
		Alerts.removeGroup = function(group){
			Alerts.alerts = _.reject(Alerts.alerts, function(cachedAlert){ 
				return cachedAlert.options.group == group; 
			});
		}
		
		Alerts.delete = function(alert){
			Alerts.alerts = _.reject(Alerts.alerts, function(cachedAlert){ 
				return cachedAlert == alert; 
			});
		};
		
		Alerts.clear = function(){
			Alerts.alerts = [];
		}
		
	return Alerts;
})

// CMS Service
.factory('CMS', function(_, $http, environment, $q, Alerts){
	
	var CMS = {};
		
	/* COMBINATION FUNCTIONS */
		CMS.getEntity = function(type, id, inCloud){
			return request('get_entity', { entityType: type, id: id, cloud: inCloud });
		};
		
		CMS.createEntity = function(entity, inCloud){
			return request('create_entity', { entity: entity, cloud: inCloud });		
		};
		
		CMS.saveEntity = function(entity, inCloud){
			return request('save_entity', { entity: entity, cloud: inCloud });
		};
		
		CMS.deleteEntity = function(entity, inCloud){
			return request('delete_entity', { entity: entity, cloud: inCloud });
		};
		
		CMS.getEntityList = function(entityType, inCloud){
			return request('entity_list', { entityType: entityType, cloud: inCloud });
		};
		
		CMS.linkEntity = function(entity, cloudnEntity){
			return request('link_entity', { entity: entity, link: cloudnEntity });
		};
		
		CMS.unlinkEntity = function(entity){
			return request('unlink_entity', { entity: entity });
		};
		
	// CLOUD ONLY
		CMS.pushArticleFolio = function(entity){
			return request('push_article_folio', { entity: entity });
		};
		
		CMS.pushEntityContents = function(entity){
			return request('push_entity_contents', { entity: entity });
		};
		
		CMS.pushEntity = function(entity){
			return request('push_entity', { entity: entity });
		};
		
		CMS.pushEntityMetadata = function(entity){
			return request('push_entity_metadata', { entity: entity });
		};
		
		CMS.publishEntity = function(entity){
			return request('publish_entity', { entity: entity });
		};
		
	// LOCAL ONLY
		CMS.downloadEntity = function(entity){
			window.open(environment.endpoint + "?action=download_article&id="+entity.id, '_blank');
		};
		
	// SETTINGS
		CMS.getSettings = function(){
			return request('get_settings');
		};
		
		CMS.saveSettings = function(settings){
			return request('save_settings', {settings: settings});
		};
		
		CMS.syncArticle = function(entity){
			return request('sync_article', { id: entity.id });
		};

/* HELPERS: Interacting with the CMS */
		request = function(action, reqData){
			var deferred = $q.defer();
			
			var req = {
					method: 'POST',
					url: environment.endpoint,
					responseType: 'json',
					headers: {
						'Content-Type': 'json',
						'WP-nonce': environment.nonce
					},
					params: { 
						action: action,
					},
					data: reqData
				}
			
			$http(req)
			.success(function(data, status, headers, config){
				if(!_.isObject(data)){
					Alerts.add("danger", "Failed. No response from the server. Please make sure the CMS is responding.", {clearGroup: true, group: 'request'});
					deferred.reject(config);
				}else{
					console.log("SUCCESS", data, status, headers, config);
					deferred.resolve(data);
				}
			})
			.error(function(data, status, headers, config){
				console.log("FAIL", data, status, headers, config);
				deferred.reject;
				
				var options = {};
					options.responseText = (_.has(data,"responseText") && !_.isUndefined(data.responseText)) ? data.responseText : null ;
					options.raw = (_.has(data,"raw") && !_.isUndefined(data.raw)) ? data.raw : null;
					options.clearGroup = true;
					options.group = 'request';
				var message = _.has(data, "message") ? data.message : "";
				Alerts.add("danger", "Failed. Could not perform action (" + config.params.action + "). " + message, options);
			});
			return deferred.promise;
		};
		
		download = function(action, reqData){
			var req = {
			        url: environment.endpoint,
			        method: 'POST',
			        responseType: 'arraybuffer',
			        cache: false,
			        headers: {
			        	'Content-Type': 'json',
						'WP-nonce': environment.nonce
			        },
			        params: { 
						action: action,
					},
					data: reqData
	    	};
	    	
	    	$http(req)
			.success(function(data, status, headers, config){
				console.log("SUCCESS", data, status, headers, config);
				deferred.resolve(data);
			})
			.error(deferred.reject);
			return deferred.promise;
		};
			
	return CMS;
})


/* SETTINGS Service */
.factory('Settings', function(CMS, _){
		
	function Settings(args) {
	// API
		this.company = '';
		this.key = '';
		this.secret = '';
		this.refresh_token = '';
		
	// Endpoints
	    this.authentication_endpoint = '';
	    this.authorization_endpoint = '';
	    this.ingestion_endpoint = '';
	    this.producer_endpoint = '';
	    this.product_endpoint = '';
	    
	// Classic Settings
	    this.tooltips = true;
		this.auto_preview_toc = true;
		this.preset_template = ''; 
		this.htmlresources = '';
		this.login = '';
		this.password = '';
		
	// plugin 2.0 Settings
		this.appMode = 'app';
		this.apiVersion = 2.0;	
		this.publications = [];	
		this.permissions = [];
		this.devices = [];
		this.templates = [];
		
		angular.extend(this, args);	
	};
	
	Settings.prototype.update = function(){
		var settings = this;
		return CMS.getSettings().then(function(data){
			if(!_.isEmpty(data)){
				angular.extend(settings, new Settings(data.settings));	
			}
		});
	}
	
	Settings.prototype.save = function(){
		var settings = this;
		return CMS.saveSettings(this).then(function(data){
			angular.extend(settings, new Settings(data.settings));	
		});
	};
	
	Settings.prototype.reset = function(){
		// TODO
	};
	
	Settings.prototype.refresh = function(data){
		angular.extend(this, new Settings(data) );	
	}
	
	Settings.prototype.hasAPIAccess = function(){
		return (this.secret.length > 1 && this.key.length > 1);
	};
	
	Settings.prototype.hasPublications = function(){
		return this.publications.length > 0;
	};
	
	Settings.prototype.canAccessCloud = function(){
		return (this.hasPublications() && this.hasAPIAccess());
	}
	
	var PluginSettings = new Settings();
		PluginSettings.update();

	return PluginSettings;
})

/* ENTITY Service */
.factory('Entity', function(CMS, Alerts){
	
	function Entity(args) {
		this.id = '';
		this.entityType = '';
		this.entityName = '';
		this.version = '';
		this.entityId = '';
		this.url = '';
		this.date_modified = '';
		this.contentUrl = '';
		this.modified = '';
		this.created = '';
		this.published = '';
		this.userData = {};
		
		angular.extend(this, args);	
	};
	
	Entity.prototype.save = function(inCloud){
		this.cleanup();
		var entity = this;
		return CMS.saveEntity(this, inCloud).then(function(data){
			return new entity.constructor(data.entity);
		});
	};
	
	Entity.prototype.delete = function(inCloud){
		return CMS.deleteEntity(this, inCloud);
	};
	
	Entity.prototype.create = function(inCloud){
		this.cleanup();
		var entity = this;
		return CMS.createEntity(this, inCloud).then(function(data){
			return new entity.constructor(data.entity);
		});
	};
	
	Entity.prototype.uploadAsset = function(){
		this.cleanup();
		var entity = this;
		return CMS.uploadEntityAsset(this).then(function(data){
			return new entity.constructor(data.entity);
		});
	};
	
	Entity.prototype.get = function(id, inCloud){
		this.cleanup();
		var entity = this;
		return CMS.getEntity(this.entityType, id).then(function(data){
			return new entity.constructor(data.entity)
		});
	};
	
	Entity.prototype.all = function(inCloud){
		var entity = this;
		return CMS.getEntityList(this.entityType).then(function(data){
			entities = [];
			_.each(data.entities, function(single){
				entities.push( new entity.constructor(single) );
			});
			return entities;
		});
	};
	
	Entity.prototype.download = function(){
		return CMS.downloadEntity(this);
	};
	
	Entity.prototype.push = function(){
		this.cleanup();
		var entity = this;
		return CMS.pushEntity(this).then(function(data){
			return new entity.constructor(data.entity)
		});
	};
	
	Entity.prototype.pushMetadata = function(){
		this.cleanup();
		var entity = this;
		return CMS.pushEntityMetadata(this).then(function(data){
			return new entity.constructor(data.entity)
		});
	};
	
	Entity.prototype.publish = function(){
		this.cleanup();
		var entity = this;
		return CMS.publishEntity(this).then(function(data){
			return new entity.constructor(data.entity)
		});
	};
	
	Entity.prototype.pushContents = function(){
		this.cleanup();
		var entity = this;
		return CMS.pushEntityContents(this).then(function(data){
			return new entity.constructor(data.entity)
		});
	};
	
	Entity.prototype.link = function(linkTo){
		this.cleanup();
		var entity = this;
		return CMS.linkEntity(this, linkTo).then(function(data){
			return new entity.constructor(data.entity)
		});
	};
	
	Entity.prototype.unlink = function(){
		this.cleanup();
		var entity = this;
		return CMS.unlinkEntity(this).then(function(data){
			return new entity.constructor(data.entity)
		});
	};
	
	Entity.prototype.pushArticleFolio = function(){
		this.cleanup();
		var entity = this;
		return CMS.pushArticleFolio(this).then(function(data){
			return new entity.constructor(data.entity);
		});
	};
	
	Entity.prototype.cleanup = function(){ }
		
	return Entity;
})

/* CONTENT Service */
.factory('Content', function(Entity, _){
	
	function Content(args){		
	    Entity.call(this);
		this.title = '';
		this.shortTitle = '';
		this.description = '';
		this.shortDescription = '';
		this.keywords = [];
		this.internalKeywords = [];
		this.thumbnail = '';
		this.socialSharing = '';
		this.productIds = [];
		this.department = '';
		this.importance = 'normal';
		this.collections = [];
		this.socialShareUrl = '';
		this.availabilityDate = '';
	// CMS
	    this.publication = '';
	    this.device = '';	 
	    
	    angular.extend(this, args);	   
	};
	
	Content.prototype = new Entity();
	Content.prototype.constructor = Content;
	
	Content.prototype.cleanup = function(){	
		
		keywordCleanup = [];
		_.each(this.keywords, function(value, key){
			keywordCleanup.push(value.text);
		});
		this.keywords = keywordCleanup;
		
		keywordCleanup = [];
		_.each(this.internalKeywords, function(value, key){
			keywordCleanup.push(value.text);
		});
		this.internalKeywords = keywordCleanup;
		
		Entity.prototype.cleanup.call(this)
	}
		
	return Content;
})

/* ARTICLE Service */
.factory('Article', function(Content, CMS){
	
	function Article(args){		
	    Content.call(this);
	    
	    this.article = ''; // Article Content
		this.author = ''; // Author name
		this.authorUrl = ''; // Author URL
		this.articleText = ''; 
		this.isAd = false;
		this.adType = 'static';
		this.adCategory = '';
		this.advertiser = '';
		this.accessState = 'metered';
		this.hideFromBrowsePage = false;
		this.articleFolio = '';
		this.isTrustedContent = false;
		
	// Adobe v1 Properties
		
	// CMS Properties
		this.body = "";
		this.template = "";
		this.cmsPreview = "";
		
		angular.extend(this, args);	    	    
		this.entityType = 'article';
	}
	
	Article.prototype = new Content();
	Article.prototype.constructor = Article;
	
	Article.prototype.cleanup = function(){
		Content.prototype.cleanup.call(this)
	}
	
	Article.prototype.sync = function(){
		var entity = this;
		return CMS.syncArticle(this).then(function(data){
			return new entity.constructor(data.entity);
		});
	}
	
	return Article;
})

/* COLLECTION Service */
.factory('Collection', function(Content){
	
	var Collection = function(args){
		this.isIssue = true;
		this.allowDownload = false;
		this.openTo = 'browsePage';
		this.readingPosition = 'retain';
		this.maxSize = -1;
		this.lateralNavigation = true;
		this.background = '';
		this.contentElements = [];
		this.view = '';
		this.coverDate = '';
		
		angular.extend(this, args);
			    
	    Content.call(this);
		this.entityType = 'collection';
	};
	
	Collection.prototype = new Content();
	Collection.prototype.constructor = Collection;
		
	return Collection;
})

/* FOLIO Service */
.factory('Folio', function(Entity){
	
	var Folio = function(args){
		this.isIssue = true;
		this.allowDownload = false;
		this.openTo = 'browsePage';
		this.readingPosition = 'retain';
		this.maxSize = -1;
		this.lateralNavigation = true;
		this.background = '';
		this.contentElements = [];
		this.view = '';
		this.coverDate = '';
		
		angular.extend(this, args);
	    
	    Entity.call(this);
		this.entityType = 'folio';
	};
	
	Folio.prototype = new Entity();
	Folio.prototype.constructor = Folio;
			
	return Folio;
})

// UNDESRSCORE
.factory('_', function() {
  return window._; //Underscore must already be loaded on the page
});