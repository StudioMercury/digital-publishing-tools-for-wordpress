angular.module('AdobePublishForCMS.services', [])

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

/* ALERTS */
.factory('Alerts', function(_, $timeout){
	
	function Alert(type, msg, options) {
		this.type = type;
		this.msg = msg;
		this.options = {
			moreDetail: (_.has(options,"message") && !_.isUndefined(options.message)) ? options.message : null,
			responseText: (_.has(options,"responseText") && !_.isUndefined(options.responseText)) ? options.responseText : null,
			time: (_.has(options,"time") && !_.isUndefined(options.time)) ? options.time : 3000,
			group: (_.has(options,"group") && !_.isUndefined(options.group)) ? options.group : false,
			clearGroup: (_.has(options,"clearGroup") && !_.isUndefined(options.clearGroup)) ? options.clearGroup : true, 
			raw: (_.has(options,"raw") && !_.isUndefined(options.raw)) ? options.raw : null, 
		};
		
		if(this.type !== "danger" && this.options.time > 0){
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
		Alerts.isDisabled = false;
		
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
		
		Alerts.disable = function(){
			Alerts.isDisabled = true;
		}
		
		Alerts.disable = function(){
			Alerts.isDisabled = false;
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
		
		CMS.getEntityList = function(entityType, filters, inCloud){
			return request('entity_list', { entityType: entityType, filters: filters, cloud: inCloud });
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
		
		CMS.syncEntity = function(entity, preset){
			return request('sync_entity', { entity: entity, presetName: preset });
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
			return request('save_settings', { settings: settings });
		};

/* HELPERS: Interacting with the CMS */
		formatTags = function(rawTags){
			var tags = [];
			_.each(rawTags, function(tag) {
				tags.push(tag.text);
			})
			return tags;	
		}
		
		request = function(action, reqData){
			Alerts.removeGroup('request');
			
			// Cleanup any tags
			if(_.has(reqData, 'entity')){
				// Keywords
				if(_.has(reqData.entity, 'keywords')){
					reqData.entity.keywords = formatTags(reqData.entity.keywords);
				}
				// InternalKeywords
				if(_.has(reqData.entity, 'internalKeywords')){
					reqData.entity.internalKeywords = formatTags(reqData.entity.internalKeywords);
				}
			}
			
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
				console.log("SUCCESS", data);
				if(!_.isObject(data)){
					var options = {
						moreDetail: "There is an error with the ajax reponse from the server. Check the server response for more information.",
						group: 'request' ,
						clearGroup: true, 
						responseText: data, 
					};
					Alerts.add("danger", "Failed. The server's response wasn't recognized.", options);
					deferred.reject(data);
				}else{
					deferred.resolve(data);
				}
			})
			.error(function(data, status, headers, config){
				var options = {};
					options.moreDetail = _.has(data, "message") ? data.message : "";
					options.responseText = (_.has(data,"responseText") && !_.isUndefined(data.responseText)) ? data.responseText : null ;
					options.raw = (_.has(data,"raw") && !_.isUndefined(data.raw)) ? data.raw : null;
					options.message = _.has(data, "message") ? data.message : "";
				deferred.reject(data);
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
				deferred.resolve(data);
			})
			.error(deferred.reject);
			return deferred.promise;
		};
			
	return CMS;
})


/* SETTINGS Service */
.factory('Settings', function(CMS, _, Alerts){
		
	function Settings(args) {
		this.refresh(args);
	};
	
	Settings.prototype.get = function(){
		var settings = this;
		return CMS.getSettings(this).then(function(data){
			if(_.has(data, 'settings')){ settings.refresh(data.settings); }
		},function(data){
			if(_.has(data, 'settings')){ settings.refresh(data.settings); }
			Alerts.add("danger", "Could not get settings.", ( _.has(data, "options") ? _.extend(options, data.options) : options) );
		});
	}
	
	Settings.prototype.update = function(){
		var settings = this;
		var options = { group: 'refreshSettings' };
		Alerts.add("warning", "Refreshing settings", options);
		return CMS.getSettings(this).then(function(data){
			Alerts.add("success", "Settings refreshed successfully.", options);
			if(_.has(data, 'settings')){ settings.refresh(data.settings); }
		},function(data){
			if(_.has(data, 'settings')){ settings.refresh(data.settings); }
			Alerts.add("danger", "Settings could not be refreshed.", ( _.has(data, "options") ? _.extend(options, data.options) : options) );
		});
	}
	
	Settings.prototype.save = function(){
		var settings = this;
		var options = { group: 'saveSettings' };
		Alerts.add("warning", "Saving settings", options);
		return CMS.saveSettings(this).then(function(data){
			Alerts.add("success", "Settings saved successfully.", options);
			if(_.has(data, 'settings')){ settings.refresh(data.settings); }
		},function(data){
			if(_.has(data, 'settings')){ settings.refresh(data.settings); }
			Alerts.add("danger", "Settings could not be saved.", ( _.has(data, "options") ? _.extend(options, data.options) : options) );
		});
	};
	
	Settings.prototype.refresh = function(data){
		angular.extend(this, data);	
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
		PluginSettings.get();

	return PluginSettings;
})

/* ENTITY Service */
.factory('Entity', function(CMS, Alerts, _){
	
	function Entity(args) {
		this.refresh(args);
	};
	
	Entity.prototype.refresh = function(data){
		angular.extend(this, data);
	};

	Entity.prototype.save = function(inCloud){
		var entity = this;
		var options = { group: 'save' };
		Alerts.add("warning", "Saving the " + entity.constructor.name + ".", options);
		return CMS.saveEntity(this, inCloud).then(function(data){
			if(_.has(data, 'entity')){ entity.refresh(data.entity); }
			Alerts.add("success", entity.constructor.name + " saved successfully.", options);
		},function(data){
			if(_.has(data.raw, 'entity')){ entity.refresh(data.raw.entity); }
			Alerts.add("danger", "The " + entity.constructor.name + " could not be saved.", _.extend(options, data));
		});
	};
	
	Entity.prototype.delete = function(inCloud){
		var entity = this;
		var options = { group: 'delete' };
		Alerts.add("warning", "Deleting the " + entity.constructor.name + ".", options);
		return CMS.deleteEntity(this, inCloud).then(function(data){
			Alerts.add("success", entity.constructor.name + " deleted successfully.", options);
		},function(data){
			Alerts.add("danger", "The " + entity.constructor.name + " could not be deleted.", _.extend(options, data));
		});
	};
	
	Entity.prototype.cloud_delete = function(){
		var entity = this;
		var options = { group: 'delete' };
		Alerts.add("warning", "Deleting the " + entity.constructor.name + ".", options);
		return CMS.deleteEntity(this, true).then(function(data){
			Alerts.add("success", entity.constructor.name + " deleted successfully.", options);
		},function(data){
			Alerts.add("danger", "The " + entity.constructor.name + " could not be deleted.", _.extend(options, data));
		});
	};
	
	Entity.prototype.create = function(inCloud){
		var entity = this;
		var options = { group: 'create' };
		Alerts.add("warning", "Creating the new " + entity.constructor.name + ".", options);
		return CMS.createEntity(this, inCloud).then(function(data){
			Alerts.add("success", entity.constructor.name + " created successfully.", options);
			if(_.has(data, 'entity')){ entity.refresh(data.entity); }
		},function(data){
			if(_.has(data.raw, 'entity')){ entity.refresh(data.raw.entity); }
			Alerts.add("danger", "The " + entity.constructor.name + " could not be created.", _.extend(options, data));
		});
	};
	
	Entity.prototype.uploadAsset = function(){
		var entity = this;
		var options = { group: 'upload' };
		Alerts.add("warning", "Uploading " + entity.constructor.name + " asset.", options);
		return CMS.uploadEntityAsset(this).then(function(data){
			Alerts.add("success", entity.constructor.name + " asset successfully uploaded.", options);
			if(_.has(data, 'entity')){ entity.refresh(data.entity); }
		},function(data){
			if(_.has(data.raw, 'entity')){ entity.refresh(data.raw.entity); }
			Alerts.add("danger", "The " + entity.constructor.name + " asset could not be uploaded.", _.extend(options, data));
		});
	};
	
	Entity.prototype.get = function(id, inCloud){
		var entity = this;
		return CMS.getEntity(this.entityType, id).then(function(data){
			if(_.has(data, 'entity')){ return new entity.constructor(data.entity); }
		});
	};
	
	Entity.prototype.all = function(filters, inCloud){
		var entity = this;
		return CMS.getEntityList(this.entityType, filters, inCloud).then(function(data){
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
		var entity = this;
		var options = { group: 'push' };
		Alerts.add("warning", "Pushing " + entity.constructor.name + " to the cloud.", options);
		return CMS.pushEntity(this).then(function(data){
			Alerts.add("success", entity.constructor.name + " successfully pushed to the cloud.", options);
			if(_.has(data, 'entity')){ entity.refresh(data.entity); }
		},function(data){
			if(_.has(data.raw, 'entity')){ entity.refresh(data.raw.entity); }
			Alerts.add("danger", "The " + entity.constructor.name + " could not be pushed to the cloud.", _.extend(options, data));
		});
	};
	
	Entity.prototype.pushMetadata = function(){
		var entity = this;
		var options = { group: 'pushEntityMetadata' };
		Alerts.add("warning", "Pushing " + entity.constructor.name + "'s metadata to the cloud", options);
		return CMS.pushEntityMetadata(this).then(function(data){
			Alerts.add("success", entity.constructor.name + "'s metadata successfully pushed the cloud.", options);
			if(_.has(data, 'entity')){ entity.refresh(data.entity); }
		},function(data){
			if(_.has(data.raw, 'entity')){ entity.refresh(data.raw.entity); }
			Alerts.add("danger", entity.constructor.name + "'s metadata could not be pushed to the cloud.", _.extend(options, data));
		});		
	};
	
	Entity.prototype.publish = function(){
		var entity = this;
		var options = { group: 'publish' };
		Alerts.add("warning", "Publishing " + entity.constructor.name + " in the cloud.", options);
		return CMS.publishEntity(this).then(function(data){
			Alerts.add("success", entity.constructor.name + " has been successfully published in the cloud.", options);
			if(_.has(data, 'entity')){ entity.refresh(data.entity); }
		},function(data){
			if(_.has(data.raw, 'entity')){ entity.refresh(data.raw.entity); }
			Alerts.add("danger", "The " + entity.constructor.name + " could not be published in the cloud.", _.extend(options, data));
		});
	};
	
	Entity.prototype.pushContents = function(){
		var entity = this;
		var options = { group: 'pushContents' };
		Alerts.add("warning", "Pushing " + entity.constructor.name + "'s contents in the cloud.", options);
		return CMS.pushEntityContents(this).then(function(data){
			Alerts.add("success", entity.constructor.name + "'s contents have been successfully pushed in the cloud.", options);
			if(_.has(data, 'entity')){ entity.refresh(data.entity); }
		},function(data){
			if(_.has(data.raw, 'entity')){ entity.refresh(data.raw.entity); }
			Alerts.add("danger", "The " + entity.constructor.name + "'s contents could not be pushed in the cloud.", _.extend(options, data));
		});
	};
	
	Entity.prototype.link = function(linkTo){
		var entity = this;
		var options = { group: 'link' };
		Alerts.add("warning", "Linking this" + entity.constructor.name + " to an " + entity.constructor.name + " in the cloud.", options);
		return CMS.linkEntity(this, linkTo).then(function(data){
			Alerts.add("success", entity.constructor.name + " has been linked to an " + entity.constructor.name + " in the cloud.", options);
			if(_.has(data, 'entity')){ entity.refresh(data.entity); }
		},function(data){
			if(_.has(data.raw, 'entity')){ entity.refresh(data.raw.entity); }
			Alerts.add("danger", "The " + entity.constructor.name + " could not be linked in the cloud.", _.extend(options, data));
		});
	};
	
	Entity.prototype.unlink = function(){
		var entity = this;
		var options = { group: 'unlink' };
		Alerts.add("warning", "Unlinking the" + entity.constructor.name + " from the cloud.", options);
		return CMS.unlinkEntity(this).then(function(data){
			Alerts.add("success", entity.constructor.name + " has been unlinked in the cloud.", options);
			if(_.has(data, 'entity')){ entity.refresh(data.entity); }
		},function(data){
			if(_.has(data.raw, 'entity')){ entity.refresh(data.raw.entity); }
			Alerts.add("danger", "The " + entity.constructor.name + " could not be unlinked from the cloud.", _.extend(options, data));
		});
	};
	
	Entity.prototype.pushArticleFolio = function(){
		var entity = this;
		var options = { group: 'pushArticle' };
		Alerts.add("warning", "Pushing the content of the " + entity.constructor.name + " to the cloud.", options);		
		return CMS.pushArticleFolio(this).then(function(data){
			Alerts.add("success", entity.constructor.name + " content has been successfully pushed to the cloud.", options);
			if(_.has(data, 'entity')){ entity.refresh(data.entity); }
		},function(data){
			if(_.has(data.raw, 'entity')){ entity.refresh(data.raw.entity); }
			Alerts.add("danger", "The " + entity.constructor.name + " content could not be pushed to the cloud.", _.extend(options, data));
		});
	};
	
	Entity.prototype.sync = function(preset){
		var entity = this;
		var options = { group: 'sync' };
		Alerts.add("warning", "Syncing " + entity.constructor.name + ".", options);		
		return CMS.syncEntity(this, preset).then(function(data){
			Alerts.add("success", entity.constructor.name + " was successfully synced with the origin.", options);
			if(_.has(data, 'entity')){ entity.refresh(data.entity); }
		},function(data){
			if(_.has(data.raw, 'entity')){ entity.refresh(data.raw.entity); }
			Alerts.add("danger", "The " + entity.constructor.name + " could not be synced.", _.extend(options, data));
		});
	}
			
	return Entity;
})

/* CONTENT Service */
.factory('Content', function(Entity, _){
	
	function Content(args){		
	    Entity.call(this, args);
	};
	
	Content.prototype = new Entity();
	Content.prototype.constructor = Content;
		
	return Content;
})

/* ARTICLE Service */
.factory('Article', function(Content, CMS){
	
	function Article(args){		
	    Content.call(this, args);
		this.entityType = 'article';
	}
	
	Article.prototype = new Content();
	Article.prototype.constructor = Article;
		
	return Article;
})


// UNDESRSCORE
.factory('_', function() {
  return window._; //Underscore must already be loaded on the page
});