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
               		
	    public $company = "";
	    public $key = ""; // v1 called key
	    public $secret = ""; // v1 called secret
	    public $device_token = "";
	    public $device_id = "";
	    public $refresh_token = "";
		public $access_token = '';
	    
	    // API
	    public $authentication_endpoint = DPS_API_AUTHENTICATION_END;
	    public $authorization_endpoint = DPS_API_AUTHORIZATION_END;
	    public $ingestion_endpoint = DPS_API_INGESTION_END;
	    public $producer_endpoint = DPS_API_PRODUCER_END;
	    public $product_endpoint = DPS_API_PRODUCT_END;
	    public $portal_url = DPS_PORTAL;
	    public $request_id = ""; // unique to a request (a single, logical "user" request) (([0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12})|([0-9a-zA-Z]{32}))	    
	    
	    // Classic Settings
	    public $tooltips = true; // should tooltips appear
		public $auto_preview_toc = true; // used to be auto-preview-toc
		public $preset_template = ""; // preset template for articles
		public $htmlresources = ""; // default HTML resources to upload
		public $login = "";
		public $password = "";
		
		// plugin 2.0 Settings
		public $appMode = "app"; // `app` or `normal`
		public $apiVersion = 2.0;	
		public $publications = array();	
		public $permissions = array();
		
		public $devices = array();
		public $templates = array();
		
		// DEFAULTS
		public $defaultTemplate = "";
		public $defaultPublication = "";
		public $recipeDefaults = array();
		public $collectionDefaults = array();

		// CMS Settings
		public $importRules = array();
		public $version = DPSFA_VERSION;

		
        public function __construct(){
	        $settings = $this->get_settings();
	        foreach ($settings as $key => $val) {
		    	$this->$key = $val;
		    }
		    $this->update_templates();
		    $this->update_endpoints();
        }
        
        public function update_endpoints(){
	        $this->authentication_endpoint = DPS_API_AUTHENTICATION_END;
			$this->authorization_endpoint = DPS_API_AUTHORIZATION_END;
			$this->ingestion_endpoint = DPS_API_INGESTION_END;
			$this->producer_endpoint = DPS_API_PRODUCER_END;
			$this->product_endpoint = DPS_API_PRODUCT_END;
			$this->portal_url = DPS_PORTAL;
        }
        
        public function update(){
	        $this->refresh();
        }
        
        public function update_templates(){
	        $Templates = new Templates();
		    $this->templates = $Templates->get_templates();
        }
        
        public function has_api_credentials(){
	        // Make sure all credentials have been entered
	        
	        // If nothign has been entered, return false
	        if(empty($this->key) && empty($this->secret) && empty($this->device_token) && empty($this->device_id)){
		        return false;
	        }else{
		        $missing = array();
		        if(empty($this->key)){ array_push($missing, "API Key"); }
		        if(empty($this->secret)){ array_push($missing, "API Secret"); }
		        if(empty($this->device_token)){ array_push($missing, "Device Token"); }
		        if(empty($this->device_id)){ array_push($missing, "Device ID"); }
		        
		        if(empty($missing)){
			        return true;
		        }else{
			        // Throw new error
					$error = new Error("Error", $code);
					$error->setTitle('Missing Credentials');
					$error->setMessage('One of the required API credentials is missing, please fill out: ' . implode(", ", $missing) );
					throw $error;
		        }
	        }
        }
        
        public function is_api_credentials_valid(){
	        // Check if API Credentials are valid
	        $Adobe = new Adobe();
	        $Adobe->refresh_access_token();
        }
        
        public function update_api_permissions(){
	        $Adobe = new Adobe();
	        $Adobe->get_user_permissions();
        }
        
        public function update_api(){
	        // Check for API Credentials
	        if($this->has_api_credentials()){
		        // Make client has a stable valid request ID 
		        if(empty($this->request_id)){
			        $this->request_id = $this->create_guid();
			        $this->save();
		        }
		        // Make sure credentials are valid
		        $this->is_api_credentials_valid();
		    	// Update user permissions
		    	$this->update_api_permissions();
		    	$this->refresh();
	        }else{
		        $this->access_token = "";
				$this->refresh_token = "";
				$this->publications = array();
				$this->permissions = array();
				$this->save();
	        }
        }
        
        public function create_guid(){
		    if (function_exists('com_create_guid') === true){
		        $guid = trim(com_create_guid(), '{}');
		        return strtolower($guid);
		    }
		
		    $guid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
		    return strtolower($guid);
		}

		public function get_settings(){
			$CMS = new CMS();
			return $CMS->get_settings();
		}
		
		public function save(){
			$CMS = new CMS();
			$CMS->save_settings(get_object_vars($this));
		}
		
		public function refresh(){
			$data = $this->get_settings();
			$this->populate_object( $data );
		}
		
		public function populate_object($data = array()){
			$availableKeys = get_object_vars($this);
			foreach ($data as $key => $val) {
		    	if(array_key_exists($key, $availableKeys)){
			    	$this->$key = $val;
			    }
		    }
		}

    } // END class Settings
}
