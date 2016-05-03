<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Settings
 * Description: This class contains settings specific parameters and functions.
 */
 
namespace DPSFolioAuthor;

if(!class_exists('DPSFolioAuthor\Settings')) { 

	class Settings {
        // Generic
		public $version = DPSFA_VERSION;
		
        // API (v 2.0)
		public $apiVersion = 2.0;	
		public $publications = array();	
	    public $company = '';
	    public $key = '';
	    public $secret = '';
	    public $device_token = '';
	    public $device_id = '';
	    public $refresh_token = '';
		public $access_token = '';
	    public $authentication_endpoint = DPS_API_AUTHENTICATION_END;
	    public $authorization_endpoint = DPS_API_AUTHORIZATION_END;
	    public $ingestion_endpoint = DPS_API_INGESTION_END;
	    public $producer_endpoint = DPS_API_PRODUCER_END;
	    public $product_endpoint = DPS_API_PRODUCT_END;
	    public $portal_url = DPS_PORTAL;
	    public $request_id = ''; // unique to a request (a single, logical "user" request) (([0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12})|([0-9a-zA-Z]{32}))	
	    public $client_app_id = DPS_API_CLIENT_ID;
	    public $client_version = DPS_API_CLIENT_VERSION;
		public $defaultPublication = '';
		public $has_api_credentials = FALSE;
		public $is_api_authenticated = FALSE;
		public $readOnly = array(
			'apiVersion',
			'version',
			'client_app_id',
			'client_version',
			'templates',
			'importTags',
			'is_api_authenticated',
			'has_api_credentials',
			'readOnly'
		);
		
		// SYSTEM STATUS 
		public $hasZipArchive = FALSE;
		public $hasSimpleXMLElement = FALSE;
		public $hasDateTime = FALSE;
		public $hasException = FALSE;
		
		// DEVICE RENDITIONS  
		public $dps_devices = array(
			array(
				"title" => "Phone",
				"width" => 480
			),
			array(
				"title" => "Tablet",
				"width" => 768
			)
		);
		
		// IMAGE RENDITIONS 
		public $dps_images = array();
		
		// TEMPLATES
		public $templates = array();
		public $defaultTemplate = '';

		// IMPORTING
		public $importTags = array();
		public $importPresets = array(
			array(
				"name" => "Default Article",
				"entityType" => "article",
				"presets" => array(
					"entityName" => "%post_name%",
					"title" => "%post_title%",
					"abstract" => "%post_excerpt%",
					"author" => "%post_author_first_name% %post_author_last_name%",
					"keywords" => "%categories%",
					"thumbnail" => "%featured_image_id%",
					"socialShareUrl" => "%permalink%",
					"articleUrl" => "%permalink%",
					"articleText" => "%post_excerpt%",
				)
			)
		);
		
		// USERS & PERMISSIONS
		public $permissions = array();
	    	    
	    // Classic Settings (v 1.0)
	    public $tooltips = TRUE; // should tooltips appear
		public $auto_preview_toc = TRUE; // used to be auto-preview-toc
		public $preset_template = ''; // preset template for articles
		public $htmlresources = ''; // default HTML resources to upload
		public $login = '';
		public $password = '';

        public function __construct($data = array()){
			$this->refresh( array_merge($this->get_settings(),$data) );
        }       
        
        public function update($overrides = array()){
	        $this->refresh($overrides);
        }
        
        public function update_templates(){
	        $Templates = new Templates();
		    $this->templates = $Templates->templates;
		    $this->templates = empty($this->templates) ? array() : $this->templates;
		    
		    if(empty($this->defaultTemplate)){ 
			    $this->defaultTemplate = !empty($this->templates) ? $this->templates[0]["path"] : null;
			    $this->save_field( 'defaultTemplate', $this->defaultTemplate );
		    }else{
			    if(!$Templates->template_exists($this->defaultTemplate)){
				    $this->defaultTemplate = !empty($this->templates) ? $this->templates[0]["path"] : null;
					$this->save_field( 'defaultTemplate', $this->defaultTemplate );
			    }
		    }
        }
        
        public function has_api_credentials(){
	        // Make sure all credentials have been entered
	        if( empty($this->key) && empty($this->secret) && empty($this->device_token) && empty($this->device_id) ){
		        return false; // If no credentials have been entered, return false immediately
	        }else{
		        $missing = array();
		        if(empty($this->key)){ array_push($missing, "API Key"); }
		        if(empty($this->secret)){ array_push($missing, "API Secret"); }
		        if(empty($this->device_token)){ array_push($missing, "Device Token"); }
		        if(empty($this->device_id)){ array_push($missing, "Device ID"); }

		        if(empty($missing)){
			        return TRUE;
		        }else{
			        // Reset the API
					$this->reset_api();
					return FALSE;
		        }
	        }
        }
        
        public function check_api_credentials(){
		    // Check if API Credentials are valid
	        try{
				$Adobe = new Adobe();
				$Adobe->refresh_access_token();
				$this->is_api_authenticated = TRUE;
			}catch(Error $error){
				$this->is_api_authenticated = FALSE;
			}
        }
        
        public function update_api_permissions(){
	        // Check if API Credentials are valid
	        try{
				$Adobe = new Adobe();
				$Adobe->get_user_permissions();
			}catch(Error $error){
				$this->is_api_authenticated = FALSE;
			}
        }
        
        public function reset_api(){
	        // remove access token
	        $this->access_token = "";
	        $this->save_field('access_token', $this->access_token);
			
			// remove refresh token
			$this->refresh_token = "";
			$this->save_field('refresh_token', $this->refresh_token);
			
			// remove publications
			$this->publications = array();
			$this->save_field('publications', $this->publications);
			
			// remove saved permissions
			$this->permissions = array();
			$this->save_field('permissions', $this->permissions);
			
			// remove default publication
			$this->defaultPublication = "";
			$this->save_field('defaultPublication', $this->defaultPublication);
			
			// remove flag that api has been authenticated
			$this->is_api_authenticated = false;
			$this->save_field('is_api_authenticated', $this->is_api_authenticated);
        }
        
        public function update_api(){
	        // Check for API Credentials
	        if($this->has_api_credentials()){
		        // Make sure client has a stable valid request ID 
		        if(empty($this->request_id)){
			        $this->request_id = $this->create_guid();
			        $this->save();
		        }
		        // Make sure credentials are valid
		        $this->check_api_credentials();
		        
		        if($this->is_api_authenticated){
			        // Update user permissions
					$this->update_api_permissions();
		        }
	        }
        }
        
        public function create_guid(){
		    if (function_exists('com_create_guid') === true){
		        $guid = trim(com_create_guid(), '{}');
		    }else{
		    	$guid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
		    }
			return strtolower($guid);
		}

		public function get_settings(){
			$CMS = new CMS();
			return $CMS->get_settings();
		}
		
		public function save_field( $key, $value ){
			$CMS = new CMS();
			$CMS->save_setting( $key, $value );
		}
		
		public function save(){
			$CMS = new CMS();
			$CMS->save_settings($this->to_array($this->get_writeable_fields()));
		}
		
		public function refresh($overrides = array()){
			$this->populate_object( array_merge($this->get_settings(), $overrides) );
			$this->update_templates();
		    $this->version = DPSFA_VERSION;
		    $this->importTags = CMS::import_tags();
		    $this->has_api_credentials = $this->has_api_credentials();

			$this->hasZipArchive = class_exists('ZipArchive');
		    $this->hasSimpleXMLElement = class_exists('SimpleXMLElement');
		    $this->hasDateTime = class_exists('DateTime');
		    $this->hasException = class_exists('Exception');
		}
		
		public function get_image_sizes(){
			$images = array();
			foreach($this->dps_images as $image){
				array_push($images, $image['width']);
			}
			return implode(",", $images);
		}
				
		private function get_writeable_fields(){
		    // Grab fields that are not readonly
		    $data = get_object_vars($this);
		    $keys = array_diff(array_keys($data), $this->readOnly);
		    return $keys;
	    }
	    
	    public function to_array($keys = array()){
			$keys = empty($keys) ? array_keys(get_object_vars($this)) : $keys;
			$export = array();
			
			// Export all keys
			foreach($keys as $key){
				$export[$key] = $this->$key;
			}
			return $export;
		}
		
		public function populate_object($data = array()){
		    $keys = array_diff(array_keys($data), $this->readOnly);
			foreach ($data as $key => $val) {
				if($key == "dps_devices" && empty($val)){
					// Devices (if empty, populate with defaults)
					continue;
				}else if($key == "templates"){
					// Templates (if empty, populate with defaults)
					continue;
				}else if($key == "importPresets" && empty($val)){
					// Import Rules (if empty, populate with defaults)
					continue;
				}else if($key == "readOnly"){
					// ReadOnly (don't populate)
					continue;
				}else if($key == "producer_endpoint" && empty($val)){
					// Endpoint: producer_endpoint (if empty, populate with defaults)
					continue;
				}else if($key == "authentication_endpoint" && empty($val)){
					// Endpoint: authentication_endpoint (if empty, populate with defaults)
					continue;
				}else if($key == "authorization_endpoint" && empty($val)){
					// Endpoint: authorization_endpoint (if empty, populate with defaults)
					continue;
				}else if($key == "ingestion_endpoint" && empty($val)){
					// Endpoint: ingestion_endpoint (if empty, populate with defaults)
					continue;
				}else if($key == "product_endpoint" && empty($val)){
					// Endpoint: product_endpoint (if empty, populate with defaults)
					continue;
				}else if($key == "portal_url" && empty($val)){
					// Endpoint: portal_url (if empty, populate with defaults)
					continue;
				}else if($key == "defaultPublication"){
					// Default Publication
					$this->$key = empty($val) ? (!empty($this->publications) ? reset($this->publications)['id'] : $val) : $val;
				}else{
				    $this->$key = $val;
				}				
		    }
		}
		
		

    } // END class Settings
}
