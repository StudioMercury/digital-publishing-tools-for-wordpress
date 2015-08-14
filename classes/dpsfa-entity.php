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
	    
		public $entityType = '';
		public $entityName = '';
		public $version = '';
		public $entityId = ''; // urn:{Id}:{entityType}:{entityName}
		public $url = '';
		public $modified = '';
		public $created = '';
		public $userData = array();
		
		/* CMS Specific */
		public $id; // corresponding ID in the CMS
		public $contentVersion;
		public $contentUrl;
		public $local_modified;
		public $local_created;
		public $published;
		public $contents = array(); // array of added content: array('id','thumbnail','original')
	    public $origin; // if this entity was imported from another entity
	    public $images;
	    public $editUrl; // CMS edit url

	    
	    public function __construct($data = array()) {
		    $this->populate_object($data);
	    }
	    
	    public function create($cloud = false){
			if($cloud){ // If cloud, create entity in Adobe's Cloud
				$adobe = new Adobe();
				$adobe->create_entity($this);
			}else{ // Create local entity
				$CMS = new CMS();
				$CMS->create_entity($this);
			}
		}
		
	    public function save($cloud = false){
		    if($cloud){ // If cloud, create entity in Adobe's Cloud
				$adobe = new Adobe();
				$adobe->save_entity($this);
			}else{ // Save local entity
				$CMS = new CMS();
				$CMS->save_entity($this);
			}
	    }		
		
		public function delete($cloud = false){
			if($cloud){ // If cloud, delete entity in Adobe's Cloud
				$adobe = new Adobe();
				$adobe->delete_entity($this);
				$this->unlink();
			}else{ // Delete local entity
				$CMS = new CMS();
				$CMS->delete_entity($this);
			}
		}
				
		public function get($cloud = false){
			if($cloud){ // If cloud, get entity in Adobe's Cloud
				$adobe = new Adobe();
				$adobe->get_entity($this);
			}else{ // Get local entity
				$this->refresh( array("id" => $this->id) );
			}
		}
		
	    /* CLOUD ONLY FUNCTION */
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
	    
	    /* CLOUD ONLY FUNCTION */
	    public function push_content($content = null){
			$adobe = new Adobe();
		    $uploadID = $adobe->create_upload_id();
		    
			if(!empty($content)){
				$adobe->upload_asset($this, $this->contents[$content], '/images/'. $content, null, $uploadID );
			}else{
				foreach($this->contents as $name => $content){
					$adobe->upload_asset($this, $content['path'], '/images/'. $name, null, $uploadID );
					$this->_links[$name]['href'] = 'contents/images/' . $name;
				}
			}

			if(!empty($content) || count($this->contents) > 0){
				$adobe->update_entity($this);
				$adobe->seal_entity($this, $uploadID);
			}
		}
		
		public function get_list($filter = array(), $cloud = false){
			if($cloud){ // If cloud, create entity in Adobe's Cloud
				$adobe = new Adobe();
				return $adobe->get_list($this->entityType);
			}else{ // Create local entity
				$CMS = new CMS();
				return $CMS->entity_list($this->entityType, $filter);
			}
		}
		
		/* CLOUD ONLY FUNCTION */
		public function publish(){
			$adobe = new Adobe();
			$adobe->publish_entity($this);
		}
		
		/* CLOUD ONLY FUNCTION */
		public function seal(){
			$adobe = new Adobe();
			$adobe->seal_entity($this);
		}
		
		/* Duplicate of save */
		public function update($cloud = false){
			$this->save($cloud);
		}
				
		public function add_content($type, $File){
			$CMS = new CMS();
			$CMS->add_entity_content($this, $type, $File);
			$this->refresh();
/*
			contents
			_links/thumbnail
			_links/socialSharing
			_links/contentUrl
			collections:
			_links/background
			_links/contentElements
*/
		}
				
		public function refresh($overrides = array(), $from_cloud = false){
			if($from_cloud){
				$adobe = new Adobe();
				$adobe->get_entity($this);
			}else{
				$CMS = new CMS();
				$data = $CMS->get_entity_data($this);
				$this->populate_object($data);
			}
			$this->populate_object( $overrides );
		}	
		
		public function link($entity){
			$adobe = new Adobe();
			$cloudEntity = $adobe->getEntity($entity);
			
			foreach($this->cloudProperties as $property){
				$this->$property = $cloudEntity->$property;
			}
			$this->save();
		}
		
		public function unlink(){
			foreach($this->cloudProperties as $property){
				$this->$property = '';
			}
			$this->save();
		}
				
// HELPERS
		public function populate_object($data = array()){
			$availableKeys = get_object_vars($this);
			foreach ($data as $key => $val) {
		    	if(array_key_exists($key, $availableKeys)){
			    	$this->$key = $val;
			    }
		    }
		}
		
		public $cloudProperties = array( 
			'entityId',
			'url',
			'modified',
			'created',
			'version',
			'contentVersion',
			'published',
			'articleFolio',
			'_links ',
		);
				        
    } // END class Entity 
}