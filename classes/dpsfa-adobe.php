<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Adobe
 * Description: This class handles the HTTPS requests for interacting with the Adobe API.
 */


namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Adobe')) { 
    
    class Adobe {
	    
	    private $client_id;
	    private $client_secret;
	    private $client_version;
	    private $request_id;
	    private $session_id;
	    private $refresh_token;
	    private $access_token;
	    private $device_token;
	    private $device_id;
	    
	    private $producer_endpoint;
	    private $product_endpoint;
	    private $authentication_endpoint;
	    private $authorization_endpoint;
	    private $ingestion_endpoint;
	    
	    private $defaultPublication;
	    private $response;
	    
	    public function __construct(){
		    
		    $settings = new Settings();
		    
		    // API ENDPOINTS
			$this->producer_endpoint =  rtrim($settings->producer_endpoint, '/\\');
			$this->authentication_endpoint = rtrim($settings->authentication_endpoint, '/\\');
			$this->authorization_endpoint = rtrim($settings->authorization_endpoint, '/\\');
			$this->ingestion_endpoint = rtrim($settings->ingestion_endpoint, '/\\');
			$this->product_endpoint = rtrim($settings->product_endpoint, '/\\');
			
			// API CREDENTIALS
		    $this->client_app_id = DPS_API_CLIENT_ID;
		    $this->client_id = $settings->key;
		    $this->client_secret = $settings->secret;
		    $this->client_version = DPS_API_CLIENT_VERSION;
			$this->api_key = $settings->key;
			
			$this->refresh_token = $settings->refresh_token;
			$this->access_token = $settings->access_token;
			$this->device_token = $settings->device_token;
			$this->device_id = $settings->device_id;

		    $this->request_id = $settings->request_id;
			$this->session_id = "87654321-0cba-9efg-4321-987650fedcba"; //(([0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12})|([0-9a-zA-Z]{32}))			
						
			$this->defaultPublication = !empty($settings->defaultPublication) ? $settings->defaultPublication : $settings->publications[0]['id'];
	    }
	    
	    public function create_entity($entity, $publicationId = null){
			// [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
			// [+] Content-Type: application/json
			
		    // CONSTRUCT HEADER
			$headers = $this->set_headers("application/json", "application/json");
			
			// CONSTRUCT URL
			$endpoint = $this->producer_endpoint;
			$publication = !empty($publicationId) ? $publicationId : $this->defaultPublication;
			$publication = (!empty($this->defaultPublication)) ? $this->defaultPublication : $this->publications[0]['id'];
			$entityType = $entity->entityType;
			$entityName = $entity->entityName;
			
			$url = "$endpoint/publication/$publication/$entityType/$entityName";
			
			// EXECUTE
		    $curl = new Curl('PUT', $url, $headers, $this->prepEntity($entity));		    
		    
		    // VERIFY RESPONSE
			$this->verify_response($curl, $entity);
			
			// SAVE RESPONSE
			$this->save_response($curl->getResponseBody(), $entity);

	    }
	    
	    public function delete_entity($entity, $publicationId = null){
		    // set request header:
			// [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
			
			 // CONSTRUCT HEADER
			$headers = $this->set_headers("application/json");
			
			// CONSTRUCT URL
			$endpoint = $this->producer_endpoint;
			$publication = !empty($publicationId) ? $publicationId : $this->defaultPublication;
			$entityType = $entity->entityType;
			$entityName = $entity->entityName;
			$version = $entity->version;
			
			$url = "$endpoint/publication/$publication/$entityType/$entityName;version=$version";
			
			// EXECUTE
		    $curl = new Curl('DELETE', $url, $headers);
		    
		    // VERIFY RESPONSE
			$this->verify_response($curl, $entity);
	    }
	    
	    public function update_entity($entity, $publicationId = null){
			// [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
			// [+] Content-Type: application/json
			
		    // CONSTRUCT HEADER
			$headers = $this->set_headers("application/json", "application/json");
			
			// TODO: ALWAYS GRAB CURRENT ENTITY AND MERGE CHANGES
			
			// CONSTRUCT URL
			$endpoint = $this->producer_endpoint;
			$publication = !empty($publicationId) ? $publicationId : $this->defaultPublication;
			$entityType = $entity->entityType;
			$entityName = $entity->entityName;
			$version = $entity->version;
			
			$url = "$endpoint/publication/$publication/$entityType/$entityName;version=$version";

			// EXECUTE
		    $curl = new Curl('PUT', $url, $headers, $this->prepEntity($entity));
		   	
		    // VERIFY RESPONSE
			$this->verify_response($curl, $entity);
			
			// SAVE RESPONSE
			$this->save_response($curl->getResponseBody(), $entity);
	    }
	    
	    public function get_publication($id = ""){
		    // [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
		    
		    // CONSTRUCT HEADER
			$headers = $this->set_headers("application/json");
			
			// CONSTRUCT URL
			$endpoint = $this->producer_endpoint;
			$url = "$endpoint/publication/$id";

			// EXECUTE
		    $curl = new Curl('GET', $url, $headers); 		    
		    
		    // VERIFY RESPONSE
			$this->verify_response($curl, $entity);
			
			// RETURN RESPONSE
			return $curl->getResponseBody();
	    }
	    
	    public function get_entity($entity, $version = null, $publicationId = null){
			// [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
		    
		    // CONSTRUCT HEADER
			$headers = $this->set_headers("application/json");
			
			// CONSTRUCT URL
			$endpoint = $this->producer_endpoint;
			$publication = !empty($publicationId) ? $publicationId : $this->defaultPublication;
			$entityType = $entity->entityType;
			$entityName = $entity->entityName;
			$version = $entity->version;
			
			$url = "$endpoint/publication/$publication/$entityType/$entityName";
			$url .= !empty($version) ? ";version=$version" : "";

			// EXECUTE
		    $curl = new Curl('GET', $url, $headers); 		    
		    
		    // VERIFY RESPONSE
			$this->verify_response($curl, $entity);
			
			// UPDATE ENTITY
			$entity->refresh($curl->getResponseBody());
	    }
	    
	    public function get_entity_list($publicationId = null, $filter = "", $pageSize = 25, $page = 0, $sortField = "modified", $descending = true){
			// [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
			
		    // CONSTRUCT HEADER
			$headers = $this->set_headers("application/json");
			
			// CONSTRUCT URL
			$endpoint = $this->producer_endpoint;
			$publication = !empty($publicationId) ? $publicationId : $this->defaultPublication;
			$entityType = $entity->entityType;
			$entityName = $entity->entityName;
			
			$url = "$endpoint/publication/$publication/$entityType?q=$filter&pageSize=$pageSize&page=$page&sortField=$sortField&descending=$descending";

			// EXECUTE
		    $curl = new Curl('GET', $url, $headers);
		    
		    // VERIFY RESPONSE
			$this->verify_response($curl, $entity);
			
			// RETURN LIST OF ENTITIES
			return $curl->getResponseBody();
	    }
	    
	    public function seal_entity($entity, $uploadID = null, $publicationId = null){
			// [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Upload-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
		    
		    // CONSTRUCT HEADER
			$headers = $this->set_headers("application/json","application/json", $uploadID);
			
			// CONSTRUCT URL
			$endpoint = $this->producer_endpoint;
			$publication = !empty($publicationId) ? $publicationId : $this->defaultPublication;
			$entityType = $entity->entityType;
			$entityName = $entity->entityName;
			$version = $entity->version;
			
			$url = "$endpoint/publication/$publication/$entityType/$entityName;version=$version/contents";
			
			// EXECUTE
		    $curl = new Curl('PUT', $url, $headers, $this->prepEntity($entity));

		    // VERIFY RESPONSE
			$this->verify_response($curl, $entity);
			
			// SAVE RESPONSE
			$this->save_response($curl->getResponseBody(), $entity);
	    }
	    
	    // Entity can be single or array of entities
	    public function publish_entity($entity, $schedule_date = null, $publicationId = null){
			// [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
			// [+] Content-Type: application/json
			
			// CONSTRUCT HEADER
			$headers = $this->set_headers("application/json", "application/json");
			
			// CONSTRUCT URL
			$endpoint = $this->producer_endpoint;
			$url = "$endpoint/job";
			
			// CONSTRUCT DATA
			$data = array(
				'workflowType' => 'publish',
				'entities' => array(),
			);
			
			if(!empty($schedule_date)){
				$data['scheduled'] = $this->formatDate($schedule_date);
			}
			
			$publication = !empty($publicationId) ? $publicationId : $this->defaultPublication;
			
			if(is_array($entity)){
				foreach($entity as $single){
					$toPublish = '/publication/' . $publication . '/' . $single->entityType . '/' . $single->entityName . ';version=' . $single->version;
					array_push($data['entities'], $toPublish);
				}
			}else{
				$toPublish = '/publication/' . $publication . '/' . $entity->entityType . '/' . $entity->entityName . ';version=' . $entity->version;
				array_push($data['entities'], $toPublish);
			}
			
			// EXECUTE
		    $curl = new Curl('POST', $url, $headers, $data);
		    
		    // VERIFY RESPONSE
			$this->verify_response($curl, $entity);
			
			// SAVE RESPONSE
			//$this->save_response($curl->getResponseBody(), $entity);
	    }
	    
	    // Retrieves the manifest of all committed assets associated with the specified version of content bucket for this entity
	    public function get_entity_manifest($entity, $publicationId = null){
			// [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
		    
		    // CONSTRUCT HEADER
			$headers = $this->set_headers("application/json", "application/json");
			
			// CONSTRUCT URL
			$endpoint = $this->producer_endpoint;
			$publication = !empty($publicationId) ? $publicationId : $this->defaultPublication;
			$entityType = $entity->entityType;
			$entityName = $entity->entityName;
			$contentVersion = $entity->contentVersion;
			
			echo "$endpoint/publication/$publication/$entityType/$entityName/contents;contentVersion=$contentVersion/";

			// EXECUTE
		    $curl = new Curl('GET', $url, $headers); 
		    
		    // VERIFY RESPONSE
			$this->verify_response($curl, $entity);
			
			// SAVE RESPONSE
			$this->save_response($curl->getResponseBody(), $entity);
	    }
	    
	    public function delete_asset($entity, $content_path, $publicationId = null){
			// [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Upload-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
			// [+] Content-Type: {ASSET TYPE}
			
			// CONSTRUCT HEADER
			$headers = $this->set_headers("application/json", $content_type);
			
			// CONSTRUCT URL
			$endpoint = $this->producer_endpoint;
			$publication = !empty($publicationId) ? $publicationId : $this->defaultPublication;
			$entityType = $entity->entityType;
			$entityName = $entity->entityName;
			$contentVersion = $entity->contentVersion;
			$content_path = ltrim($content_path, '/');
			
			$url = "$endpoint/publication/$publication/$entityType/$entityName/contents;contentVersion=$contentVersion/$content_path";
			
			// EXECUTE
		    $curl = new Curl('DELETE', $url, $headers, $file_path, TRUE);
			
			// VERIFY RESPONSE
			$this->verify_response($curl, $entity);
	    }
	    	    
	    public function upload_article_folio($entity, $asset, $publicationId = null){
		    // [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Upload-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
			// [+] Content-Type: {ASSET TYPE}

			// CONSTRUCT HEADER
			$headers = $this->set_headers("application/json", 'application/zip');

			// CONSTRUCT URL
			$endpoint = $this->ingestion_endpoint;
			$publication = !empty($publicationId) ? $publicationId : $this->defaultPublication;
			$entityType = $entity->entityType;
			$entityName = $entity->entityName;
			$version = $entity->version;
			
			$url = "$endpoint/publication/$publication/$entityType/$entityName;version=$version/contents/folio";
			
			// CONSTRUCT FILE PATH 
			$file_path = realpath($asset);
			
			// EXECUTE
		    $curl = new Curl('PUT', $url, $headers, $file_path, TRUE);
			
			// VERIFY RESPONSE
			$this->verify_response($curl, $entity);
	    }
	    
	    public function upload_asset($entity, $asset, $content_path, $content_type = null, $uploadID = null, $publicationId = null){
		    // [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Upload-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
			// [+] Content-Type: {ASSET TYPE}
			
			// GET ASSET CONTENT TYPE IF NOT SUPPLIED
			if(empty($content_type)){
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$content_type = finfo_file($finfo, $asset);
				finfo_close($finfo); 
			}

			// CONSTRUCT HEADER
			$uploadId = empty($uploadID) ? $this->create_guid() : $uploadID;
			$headers = $this->set_headers("application/json", $content_type, $uploadId);

			// CONSTRUCT URL
			$endpoint = $this->producer_endpoint;
			$publication = !empty($publicationId) ? $publicationId : $this->defaultPublication;
			$entityType = $entity->entityType;
			$entityName = $entity->entityName;
			$contentVersion = $entity->contentVersion;
			$content_path = ltrim($content_path, '/');
			
			$url = "$endpoint/publication/$publication/$entityType/$entityName/contents;contentVersion=$contentVersion/$content_path";
			
			// CONSTRUCT FILE PATH 
			$file_path = realpath($asset);
			
			// EXECUTE
		    $curl = new Curl('PUT', $url, $headers, $file_path, TRUE);
			
			// VERIFY RESPONSE
			$this->verify_response($curl, $entity);			
		}

	    public function upload_thumbail($entity, $image){
		    $uploadID = $this->create_upload_id();
		    $this->upload_asset($entity, $image, 'images/thumbnail', null, $uploadID);
		    $entity->_links['thumbnail']['href'] = 'contents/images/thumbnail';
		    $entity->update_entity($entity);
		    $this->seal_entity($entity, $uploadID);
	    }
	    
	    public function upload_background($entity, $image){
		    $uploadID = $this->create_upload_id();
		    $this->upload_asset($entity, $image, 'images/background', null, $uploadID);
		    $entity->_links['background']['href'] = 'contents/images/thumbnail';
		    $entity->update_entity($entity);
		    $this->seal_entity($entity, $uploadID);
	    }
	    
	    public function upload_socialShare($entity, $image){
		    $uploadID = $this->create_upload_id();
		    $this->upload_asset($entity, $image, 'images/socialSharing', null, $uploadID);
		    $entity->_links['socialSharing']['href'] = 'contents/images/socialSharing';
		    $entity->update_entity($entity);
		    $this->seal_entity($entity, $uploadID);
	    }
	    
	    /**
		 * This method will get the list of user permissions.
		 *
		 */
	    public function get_user_permissions(){
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
			
		    // CONSTRUCT HEADER
			$headers = array();
			$headers[] = "X-DPS-Client-Version: ". $this->client_version;
			$headers[] = "X-DPS-Client-Request-Id: ". $this->request_id;
			$headers[] = "X-DPS-Client-Session-Id: ". $this->create_guid();
			$headers[] = "X-DPS-Api-Key: ". $this->client_id;
			$headers[] = "Authorization: bearer ". $this->get_access_token();
			
			// CONSTRUCT URL
			$endpoint = $this->authorization_endpoint;
			$url = "$endpoint/permissions";
			
			// EXECUTE
		    $curl = new Curl('GET', $url, $headers);
		    
			// VERIFY RESPONSE
			try{
				$this->verify_response($curl);
				
				// Extract current publications and permissions (if available)
				$publications = $this->get_publications_from_response($curl->getResponseBody());
				$permissions = $this->get_permissions_from_response($curl->getResponseBody());
				
				$settings = new Settings();
				$settings->publications = $publications;
				$settings->permissions = $permissions;
				$settings->save();
				
			}catch(Error $error){
				// Permissions don't exist or updated / remove them
		        $error->setTitle('Could not get user\'s permissions from Adobe\'s API');
				$settings = new Settings();
				$settings->publications = array();
				$settings->permissions = array();
				$settings->save();
				throw $error;
			}
			
	    }
	    	    
/* HELPERS */
		private function save_response($body = array(), $entity){
			if(!empty($body)){
				
				foreach ($body as $key => $value){
					$entity->$key = $value;
				}
				
				$entity->contentVersion = $this->get_content_version($entity->_links);
				$entity->save();
			}
		}

		/**
		 * This method will convert the given date (mm/dd/yyyy hh:mm:ss) into EPOCH.
		 * @param {date} $date - The time in the following: mm/dd/yyyy hh:mm:ss
		 * @return {date} $epoch - The time in EPOCH if successful, 0 otherwise.
		 */
		private function formatDate($date) {
			$has_time = explode(' ', $date);
			$hour = 0;
			$minute = 0;
			$second = 0;
	
			if (count($has_time) === 2) {
				$date_array = explode('/', $has_time[0]);
				$time_array = explode(':', $has_time[1]);
				if (count($time_array) === 3) {
					$hour = $time_array[0];
					$minute = $time_array[1];
					$second = $time_array[2];
				} else { // Wrong date + time format: mm/dd/yyyy hh:mm:ss;
					return 0;
				}
			} else {
				$date_array = explode('/', $date);
			}
	
			if (count($date_array) === 3) {
				$month = $date_array[0];
				$day = $date_array[1];
				$year = $date_array[2];
			} else { // Wrong date format: mm/dd/yyyy;
				return 0;
			}
	
			return mktime($hour, $minute, $second, $month, $day, $year) * 1000;
		}
		
		private function get_content_version($_links){
			if(isset($_links['contentUrl'])){
				$contentHref = $_links['contentUrl']['href'];
				$index = strrpos($contentHref, '=');
				$contentVersion = substr($contentHref, $index + 1, -1);
				return $contentVersion;
			}else{
				return null;
			}
		}
		
		private function get_access_token(){
			if( empty($this->access_token) ){
				$this->refresh_access_token();
			}
			return $this->access_token;
		}
		
		private function get_publications_from_response($response){
			$publications = array();
			if(isset($response['masters'])){
				foreach($response['masters'] as $master){
					if(isset($master['publications'])){
						foreach($master['publications'] as $publication){
							array_push($publications, $publication);
						}
					}
				}
			}
			return $publications;
		}
		
		private function get_permissions_from_response($response){
			$permissions = array();
			if(isset($response['masters'])){
				foreach($response['masters'] as $master){
					if(isset($master['permissions'])){
						foreach($master['permissions'] as $permission){
							array_push($permissions, $permission);
						}
					}
				}
			}
			return $permissions;
		}
		
		public function create_upload_id(){
			return $this->create_guid();
		}
		
		// Check response
		private function verify_response($curl, $entity = null, $retry = TRUE){
			
			// Get error code 
			$code = $curl->getHTTPCode();			
			
			// If response is an error handle it appropriately
			if($code >= 300){
				
				// Throw new error
				$error = new Error("Error", $code);
				$error->setRaw(array(
					"headers" => $curl->getResponseHeader(),
					"body" => $curl->getResponseBody(),
					"url" => $curl->getRequestUrl(),
					"entity" => $entity
				));
				
				switch ($code) {
					case 400: // Bad Request - one of the parameters was invalid; more detail in the response body
						$error->setTitle('Bad Request');
						$error->setMessage('One of the parameters was invalid.');
						throw $error;
						break;
					case 401: // OAuth token invalid or expired
						// Refresh auth token and try again
						$this->refresh_access_token();
						$error->setTitle('Token Invalid or Expired');
						$error->setMessage('Your OAuth token is invalid. Please try refresh the page and trying again. If that doesn\'t work please update your API credentials in the plugin settings.');
						throw $error;
						break;
					case 403: // Forbidden - user's quota exceeded.
						$error->setTitle('Quota Exceeded');
						$error->setMessage('User\'s quota has been exceeded.');
						throw $error;
						break;
					case 404: // Not Found - specified entityName does not exist
						$error->setTitle('Not Found');
						$error->setMessage('The specific entityName: ' . $entity->entityName . ' does not exist.');
						// TODO: OFFER TO RE-Link or UNLINK
						throw $error;
						break;
					case 409: // Conflict - specified version is not the latest.
						$version = $curl->getVersionId();
						$contentVersion = $curl->getContentVersion();

						$entity->version = !empty($version) ? $version : $entity->version;
						$entity->contentVersion = !empty($contentVersion) ? $contentVersion : $entity->contentVersion;
						$entity->save();
						
						$error->setTitle('Version Conflict');
						$error->setMessage('The version trying to update is not the latest version. Please refresh the page and try updating again.');
						throw $error;
						break;
					case 410: // Gone - Specified entity was deleted
						$error->setTitle('Deleted');
						$error->setMessage('The entity was deleted.');
						throw $error;
						// TODO: OFFER TO UNLINK the entity
						break;
					case 415: // UnsupportedType: Uploaded file is not a .article file.	
						$error->setTitle('Unsupported Type');
						$error->setMessage('Uploaded file is not a .article file');
						throw $error;
						break;
					case 500: // Internal Server Error: a problem within the service prevented the call from succeeding.
						$error->setTitle('Internal Server Error');
						$error->setMessage('The specified version to publish is not the latest version.');
						throw $error;
						break;
					case 503: // Service Unavailable - if any of the third party services are unavailable.
						$error->setTitle('Service Unavailable');
						$error->setMessage('The API is unavailable.');
						throw $error;
						break;
					default: // Unknown
						$error->setTitle('Unknown Error');
						$error->setMessage('This is embarrassing, something unexpected happened and we don\'t have an answer.');
						throw $error;
						break;
				}
			}
		}	
		
		private function set_headers( $accept_type = "application/json", $content_type = "application/json", $client_upload_id = null){
			$headers = array();
			$headers[] = 'X-DPS-Client-Id: ' . $this->client_app_id;
			$headers[] = "X-DPS-Client-Version: ". $this->client_version;
			$headers[] = "X-DPS-Client-Request-Id: ". $this->request_id;
			$headers[] = "X-DPS-Client-Session-Id: ". $this->create_guid();
			$headers[] = "X-DPS-Api-Key: ". $this->client_id;
			$headers[] = "Authorization: bearer ". $this->get_access_token();
			
			// set the optional reqquest header
			if ($client_upload_id)
				$headers[] = "X-DPS-Upload-Id: $client_upload_id";
			if ($accept_type)
				$headers[] = "Accept: $accept_type";
			if ($content_type)
				$headers[] = "Content-Type: $content_type";
			return $headers;
		}

		/**
		 * This method will get the access token.
		 *
		 * @param {String} $refresh_token - The base-64 encoded refresh token
		 * @param {String} $grant_type - The grant type, optional parameters
		 * @example $grant_type = 'refresh_token';
		 * @param {String} $scope - The scope value, optional parameters
		 * @example $scope = 'AdobeID,openid';
		 */
		public function refresh_access_token($grant_type = 'refresh_token', $scope = 'AdobeID,openid') {
			// set request header:
			// [+] Accept: application/json
			// [+] Content-Type: application/x-www-form-urlencoded
			$headers = array(
				'Accept: application/json',
				'Content-Type: Content-Type: application/x-www-form-urlencoded'
			);

			// set request URL
			$url  = $this->authentication_endpoint . "/ims/token/v1?grant_type=device&client_id=".$this->client_id."&client_secret=".$this->client_secret."&scope=openid&device_token=".$this->device_token."&device_id=".$this->device_id;
			
			// call helper to initiate the cURL request
			$curl = new Curl( 'POST', $url, $headers );
			
			// Parse out token
			$data = $curl->getResponseBody();
			
			if(!empty($data['error'])){
				// Throw new error
				$error = new Error("Error", 401);
				$error->setRaw(array(
					"headers" => $curl->getResponseHeader(),
					"body" => $curl->getResponseBody(),
					"url" => $curl->getRequestUrl(),
					"entity" => ''
				));
				$error->setTitle('API Credentials Not Valid');
				$error->setMessage('The API credentials supplied are not valid. The plugin will not allow you to access any of the cloud functions without proper credentials: ' . $data['error']);
				
				// Return token
				$this->access_token = "";
				$this->refresh_token = "";
				
				// Save access token
				$settings = new Settings();
				$settings->access_token = "";
				$settings->refresh_token = "";
				$settings->publications = array();
				$settings->permissions = array();
				$settings->save();
				
				throw $error;
			}else{
				$access_token = (isset($data['access_token'])) ? $data['access_token'] : null;
				$refresh_token = (isset($data['refresh_token'])) ? $data['refresh_token'] : null;
	
				// Save access token
				$settings = new Settings();
				$settings->access_token = $access_token;
				$settings->refresh_token = $refresh_token;
				$settings->save();
				
				// Return token
				$this->access_token = $access_token;
				$this->refresh_token = $refresh_token;
			}
		}
		
		private function create_guid(){
		    if (function_exists('com_create_guid') === true){
		        $guid = trim(com_create_guid(), '{}');
		        return strtolower($guid);
		    }
		
		    $guid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
		    return strtolower($guid);
		}
		
		private function prepEntity($entity){
			/* Filter out only attributes allowed by the API */
			$attributes = array();
			foreach(get_object_vars($entity) as $key => $value){
				if(in_array($key, $entity->apiAllowed) && !empty($value)){ 
					$attributes[$key] = $value; 
				}
			}
			return $attributes;
		}
					        
    } // END class Adobe 
    
}