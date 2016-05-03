<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Entity
 * Description: This class contains article specific parameters and functions.
 */
 
namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Entity')) { 
    
    class Entity {
	    
	    public $id; // corresponding ID in the CMS
	    
	    /* Entity Specific */
	    public $entityType = ''; // "publication", "view", "collection", "article", "layout", "cardTemplate", "banner"
		public $entityName = ''; // [[0-9a-zA-Z]{1}[0-9a-zA-Z._-]{0,63}
		public $entityId = ''; // urn:{Id}:{entityType}:{entityName}
		public $version = ''; // Each time the entity changes a new version of the entity is created
		public $url = ''; // This property can be used to identify a related location for the entity outside the DPS system
		public $modified = ''; // Time the entity was last modified
		public $created = ''; // Time the entity was created
		public $published; // Time the entity was published
		public $userData = array(); // Free-form field for 3rd party integrators to add a custom schema in.
		public $isPublishable = FALSE; // if the entity can be published
		public $publicationId = ''; // associated publication Id
	    public $_links = array(); // array of entity's links
		
		/* CMS Specific */
		public $contentVersion = ''; // Version of the content
		public $contentUrl = ''; // Url to content available via the Content Delivery API.
		public $local_modified = ''; // Time modified in the CMS
		public $date_created = ''; // Time created in the CMS
		public $isMissing = FALSE; // if the entity has an entityId but that entityID is missing from the cloud
	    public $origin = ''; // Associated ID of the original entity this was created from in the CMS
	    public $time_synced = ''; // Last modified date of the original post when synced
	    public $editUrl = ''; // The url to edit the entity in the CMS
	    public $entityVersion = ''; // the version of the entity (when created)
	    
	    public $contents = array();
	    public $internal = array();
	    public $readOnly = array();
			    
	    /* CONSTRUCT the entity */
	    public function __construct($id = 0) {
		    $data = array();
		    if( is_object($id) && get_class($id) == get_class($this)){
		    	$data = get_object_vars($id);
		    }else if(is_array($id)){
		    	$data = $id;
		    }else if( is_numeric($id) && !empty($id) ){
			    $data['id'] = $id;
		    }

		    if( array_key_exists('id', $data) && !empty( $data['id'] ) ){
			    $this->id = $data['id'];
			    $CMS = new CMS();
				$data = array_merge($CMS->get_entity_data($this->id), $data);
		    }

		    // Init the object
		    $this->init($data);
			
			// Internal
			$this->internal = array(); // reset
			array_push($this->internal,
				'id',
				'entityName',
				'entityType',
				'origin',
				'version',
				'contentVersion',
				'editUrl',
				'local_modified',
				'local_created',
				'entityVersion',
				'contents',
				'internal',
				'readOnly',
				'isMissing',
				'time_synced'
			);
			
			// Read Only
			$this->readOnly = array(); // reset
			array_push($this->readOnly,
				'entityId',
				'modified',
				'published',
				'isPublishable',
				'publicationId',
				'contentUrl',
				'_links',
				'contents',
				'date_created',
				'created',
				'modified',
				'published'
			);

	    }
	    
	    public function init($data = array()){
			$availableKeys = get_object_vars($this);
			foreach ($data as $key => $value) {
		    	if(array_key_exists($key, $availableKeys)){
			    	$this->$key = $value;
			    }
		    }
		    $this->verify();
	    }
	    
	    public function verify(){}
	    
	    public function __set($key, $value){
		    $this->verify();
	    }
			    
	    /* CREATE a new entity */
	    // $cloud = true: creates the entity in the Adobe cloud
	    // $cloud = false: creates the entity locally in the CMS
	    public function create($cloud = false){
			if($cloud){ // If cloud, create entity in Adobe's Cloud
				$adobe = new Adobe();
				$data = $adobe->create_entity($this);
				$this->save_data($data);
				$this->refresh();
			}else{ // Create local entity				
				$date = new \DateTime();
				$this->date_created = $date->getTimestamp();				
				$CMS = new CMS();
				$this->id = $CMS->create_entity($this->entityType, $this->to_array());
			}
		}
		
		/* SAVE entity */
		// $cloud = true: saves the entity in the Adobe cloud
		// $cloud = false: saves the entity in the CMS
	    public function save($cloud = false){
		    if($cloud){ // If cloud, create entity in Adobe's Cloud
				$adobe = new Adobe();
				$adobe->save_entity($this);
			}else{ // Save local entity
				// If it doesn't exist, create it
				if(empty($this->id)){
					$this->create();
				}
			    $CMS = new CMS();
				$CMS->save_entity($this->id, $this->entityType, $this->get_writeable_fields());
			}
	    }
	    
	    private function get_writeable_fields(){
		    // Grab fields that are not readonly
		    $data = get_object_vars($this);
		    $keys = array_diff(array_keys($data), $this->readOnly);
		    foreach($data as $key => $value){
			    if(!in_array($key, $keys)){ unset($data[$key]); }
		    }
		    return $data;
	    }
	    
	    /* SAVE entity field */
	    // Save only the specified field for the entity in the CMS
	    public function save_field($field){
		    $CMS = new CMS();
		    $CMS->save_field($this->id, $this->entityType, $field, $this->$field);
	    }
		
		/* DELETE entity */
		// $cloud = true: deletes the entity in the Adobe cloud
		// $cloud = false: deletes the entity in the CMS
		public function delete($cloud = false){
			if($cloud){ // If cloud, delete entity in Adobe's Cloud
				$adobe = new Adobe();
				$adobe->delete_entity($this);
				$this->unlink();
			}else{ // Delete local entity
				$CMS = new CMS();
				$CMS->delete_entity($this->id);
			}
		}
		
		/* GET entity */
		// Cloud only function to get an entity from the Adobe cloud	
		public function get(){
			$adobe = new Adobe();
			return $adobe->get_entity($this);
		}
		
	    /* PUSH entity */
	    // Cloud only function to push entity to the Adobe cloud
		public function push(){
			$adobe = new Adobe();
			/* Does entity exist in the cloud */
			if(empty($this->entityId)){
				/* Create the entity */
				$adobe->create_entity($this);
			}else{
				/* Update the Entity */
				$adobe->update_entity($this);
			}
	    }
	    
	    /* PUSH content from entity */
	    // TODO: Upload ID caching
	    // Cloud only function to push an entity's contents
		public function push_content($content){
			if(!empty($this->$content)){
				$adobe = new Adobe();
				
				// Create an upload Id
			    $uploadID = $adobe->create_guid();
			    
			    // Get content path
			    $contentData = $this->get_content($content);
			    
			    // Upload asset to Adobe
				$response = $adobe->upload_asset( $this, $contentData['path'], $this->contents[$content]);
				
				$this->refresh($adobe->get_entity($this));

				// Updates asset reference in the entity links
				if(is_array($response)){
					// If saving multiple image renditions (downsample sizes)
					$this->_links[$content] = $response;
				}else{
					// If just saving one image size
					$this->_links[$content]['href'] = 'contents/' . $this->contents[$content];
				}
			    
			    // Update entity in the cloud
				$adobe->update_entity($this, null, array("_links" => $this->_links));
				
				// Seal the contents of the entity
				$adobe->seal_entity($this, $uploadID);
			}
		}
		
		/* PUSH ALL contents */
		public function push_contents(){
			$adobe = new Adobe();
			
			// Create an upload Id
			$uploadID = $adobe->create_guid();

		    $seal = false; // set seal to false first
		    foreach($this->contents as $name => $path){
			    if(!empty($this->$name)){
				    
				    // Get content path
					$contentData = $this->get_content($name);
					
				    // Upload asset to Adobe
					$response = $adobe->upload_asset( $this, $contentData['path'], $this->contents[$name], null, $uploadID );
					
					// Get Article
					$this->refresh($adobe->get_entity($this));
					
					// Combine _links
					if(is_array($response)){
						// If saving multiple image renditions (downsample sizes)
						$this->_links[$name] = $response;
					}else{
						// If just saving one image size
						$this->_links[$name] = array('href' => 'contents/' . $path);
					}
					
				    // Update entity in the cloud
					$adobe->update_entity($this, null, array("_links" => $this->_links));
	
					// Get entity
					$this->refresh($adobe->get_entity($this));
	
					// Seal the contents of the entity
					$adobe->seal_entity($this, $uploadID);
			    }			    
		    }	
		    
		    
		}
		
		/* GET LIST of entities */
		/*
			TODO: DEFINE filters
				page: default 1
				limit: default 30
				metadata: array if metadata ie: array( keywords => array('tag') )
				order: sort order (desc or asc)
				orderby: order of articles by fieldname
		*/
		public function get_list($filter = array(), $cloud = false){
			if($cloud){
				// get entities from cloud
				$adobe = new Adobe();
				return $adobe->get_list($this->entityType);
			}else{ 
				// get entities from CMS
				$CMS = new CMS();
				$ids = $CMS->get_entity_list($this->entityType, $filter);
				$entities = array();
				$class = get_class($this);
				foreach($ids as $id){
					array_push($entities, new $class($id));
				}
				return $entities;
			}
		}
		
		/* GET STATUS (cloud only function) */
		public function get_status(){
			$adobe = new Adobe();
			return $adobe->get_status($this);
		}
		
		/* PUBLISH entity */
		public function publish(){
			if($this->isPublishable){
				$adobe = new Adobe();
				$adobe->publish_entity($this);
			}else{
				$error = new Error("Error", 400);
				$error->setRaw(array(
					"headers" => "Could not publish " . $this->entityType,
					"body" => "Missing content. Please make sure the entity has all required content before publishing.",
					"url" => "",
					"entity" => $this
				));
				throw $error;
			}
		}
		
		/* SEAL entity */
		// Cloud only function to seal an entity
		public function seal(){
			$adobe = new Adobe();
			$adobe->seal_entity($this);
		}
		
		/* UPDATE entity */
		public function update($cloud = false, $allProperties = false){
			if($cloud){
				// Request entity from the Adobe cloud
				try{
					$data = $this->get();
					
					// Filter out $readOnlyFields or update all properties
					if(!$allProperties){
						$updates = array_intersect_key($data, array_flip($this->readOnly));
					}else{
						$updates = $data;
					}
					
					$updates['isMissing'] = false;
					
					foreach($updates as $key => $value){
						$this->$key = $value;
						$this->save_field($key);
					}
				}catch(Error $error){
					if($error->getCode() == 404 || $error->getCode() == 410){
						$this->isMissing = true;
						$this->save_field('isMissing');
					}
				}
				
			}else{
				$this->refresh();
			}
		}
		
		/* ADD CONTENT to entity */
		// Local only function to add content to an entity
		public function add_content($type, $File){
			/*
				TYPES OF CONTENT:
				contents
					_links/thumbnail
					_links/socialSharing
					_links/contentUrl
				collections:
					_links/background
					_links/contentElements
			*/
			
			// Process file upload
			$CMS = new CMS();
			$this->$type = $CMS->handle_file_upload($this->id, $File);
			$this->save_field($type);
		}
		
		public function get_content($key){
			if(isset($this->$key)){
				$CMS = new CMS();
				$content = $CMS->get_entity_content($this->$key);
			}
			return isset($content) ? $content : FALSE;
		}
		
		/* REFRESH entity */
		public function refresh($overrides = array()){
			$CMS = new CMS();
			$data = array_merge($CMS->get_entity_data($this->id), $overrides);
			$this->init($data);
		}	
		
		/* LINK entity */
		// Cloud only function to link the entity with 
		// $entity = entity in the cloud to link the current entity ($this) with
		public function link($entityName){
			$adobe = new Adobe();
			$entityClass = get_class($this);
			$cloudEntity = new $entityClass(array('entityName'=>$entityName));
			$cloudEntityData = $adobe->get_entity($cloudEntity);
			if(!empty($cloudEntityData)){
				foreach($this->readOnly as $property){
					$this->$property = $cloudEntityData[$property];
					$this->save_field($property);
				}
				$this->entityName = $entityName;
				$this->save_field('entityName');
			}
		}
		
		/* UNLINK entity */
		// local only function to unlink a local entity with a cloud entity
		public function unlink(){
			foreach($this->readOnly as $property){
				$this->$property = null;
				$this->save_field($property);
			}
			
			$this->isMissing = null;
			$this->save_field('isMissing');
			
			$this->version = null;
			$this->save_field('version');
			
			$this->contentVersion = null;
			$this->save_field('contentVersion');
		}
		
		/* SYNC entity */
		// Sync an entity from it's original CMS entity (if imported)
		public function sync($presetName = ""){
			if(!empty($this->origin) && $this->origin_exists() && !empty($presetName)){
				$CMS = new CMS();
				$CMS->sync($this->id, $this->origin, $presetName);
			}
		}
		
		/* DUPLICATE entity */
		// Duplciate the current entity
		public function duplicate(){
			$CMS = new CMS();
			$id = $CMS->duplicate_entity($this->id);
			$class = get_class($this);
			$entity = new $class($id);
			$entity->unlink();
			return $entity;
		}
		
		public function origin_exists(){
			if(!empty($this->origin)){
				$CMS = new CMS();
				return $CMS->origin_exists($this->origin);
			}else{
				return false;
			}
		}
		
		public function to_array($keys = array(), $exportContents = true){
			$keys = empty($keys) ? array_keys(get_object_vars($this)) : $keys;
			$export = array();

			// Export all keys
			foreach($keys as $key){
				$export[$key] = $this->$key;
			}
												
			// Export contents
			if($exportContents){
				foreach($this->contents as $content => $url){
					$export["_$content"] = $this->get_content($content);
				}
			}
						
			return $export;
		}
		
		private function save_data($data){
			if(!empty($data)){
				$CMS = new CMS();
				$CMS->save_entity($this->id, $this->entityType, $data);
			}
		}
						        
    } // END class Entity 
}