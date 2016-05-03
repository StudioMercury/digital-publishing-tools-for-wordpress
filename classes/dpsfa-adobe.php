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
	    
	    private $settings;
	    
	    const MIME_ALL = 'application/json, text/plain, */*'; 
	    const MIME_ENTITY = 'application/vnd.adobe.entity+json'; 
	    const MIME_ARTICLE = 'application/vnd.adobe.article+zip'; 
	    const MIME_ZIP = 'application/zip'; 
	    const MIME_FILE = 'multipart/form-data'; 
	    const MIME_JSON = 'application/json'; 
	    const MIME_SYMLINK = 'application/vnd.adobe.symboliclink+json'; 
	    const MIME_URLENCODED = 'application/x-www-form-urlencoded'; 
	    
	    public function __construct(){
		    $this->settings = new Settings();
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
			$headers = $this->set_headers();
						
			// CONSTRUCT URL: $endpoint/publication/$publication/$entityType/$entityName
			$url = sprintf("%s/publication/%s/%s/%s",
				$this->settings->producer_endpoint, // $endpoint
				!empty($publicationId) ? $publicationId : $this->default_publication(), // $publication
				$entity->entityType, // $entityType
				$entity->entityName // $entityName
			);
			
			// EXECUTE
		    $curl = new Curl('PUT', $url, $headers, $this->prepEntity($entity));		    

		    // VERIFY RESPONSE
		    $this->verify_response($curl, $entity);
			
			// RETURN RESPONSE
			return $this->collect_data($curl->getResponseBody());
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
			$headers = $this->set_headers();
						
			// CONSTRUCT URL: $endpoint/publication/$publication/$entityType/$entityName;version=$version
			$url = sprintf("%s/publication/%s/%s/%s;version=%s",
				$this->settings->producer_endpoint, // $endpoint
				!empty($publicationId) ? $publicationId : $this->default_publication(), // $publication
				$entity->entityType, // $entityType
				$entity->entityName, // $entityName
				$entity->version // $version
			);
						
			// EXECUTE
		    $curl = new Curl('DELETE', $url, $headers);
		    
		    // VERIFY RESPONSE
			$this->verify_response($curl, $entity);
	    }
	    
	    public function update_entity($entity, $publicationId = null, $overrides = array()){
			// [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
			// [+] Content-Type: application/json
			
		    // CONSTRUCT HEADER
			$headers = $this->set_headers();
						
			// CONSTRUCT URL: $endpoint/publication/$publication/$entityType/$entityName;version=$version
			$url = sprintf("%s/publication/%s/%s/%s;version=%s",
				$this->settings->producer_endpoint, // $endpoint
				!empty($publicationId) ? $publicationId : $this->default_publication(), // $publication
				$entity->entityType, // $entityType
				$entity->entityName, // $entityName
				$entity->version // $version
			);

			// EXECUTE
		    $curl = new Curl('PUT', $url, $headers, $this->prepEntity($entity, $overrides));

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
			$headers = $this->set_headers();
			
			// CONSTRUCT URL: $endpoint/publication/$id
			$url = sprintf("%s/publication/%s",
				$this->settings->producer_endpoint, // $endpoint
				$id // $id
			);

			// EXECUTE
		    $curl = new Curl('GET', $url, $headers); 		    
		    
		    // VERIFY RESPONSE
			$this->verify_response($curl, $entity);
			
			// RETURN RESPONSE
			return $curl->getResponseBody();
	    }
	    
	    public function get_entity($entity, $publicationId = null){
			// [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
		    
		    // CONSTRUCT HEADER
			$headers = $this->set_headers();
			
			// CONSTRUCT URL: $endpoint/publication/$publication/$entityType/$entityName;version=$version
			$url = sprintf("%s/publication/%s/%s/%s",
				$this->settings->producer_endpoint, // $endpoint
				!empty($publicationId) ? $publicationId : $this->default_publication(), // $publication
				$entity->entityType, // $entityType
				$entity->entityName // $entityName
			);
			
			// Include version if there's a version
			if(!empty($this->version)){
				$url .= ";version=" . $this->version;
			}

			// EXECUTE
		    $curl = new Curl('GET', $url, $headers); 		    
		    
		    // VERIFY RESPONSE
			$this->verify_response($curl, $entity);
			
			$version = $this->get_version($curl->getResponseBody());
			$contentVersion = $this->get_content_version($curl->getResponseBody());
			$entity->version = empty($version) ? $entity->version: $version;
			$entity->contentVersion = empty($contentVersion) ? $entity->contentVersion: $contentVersion;
						
			// RETURN DATA
			$data = $curl->getResponseBody();
			return empty($data) ? array() : $data;
	    }
	    
	    /* 
			Query parameters: An optional query string defines what subset of entities are returned. For example, "q=keyword==cars" would return all entities with the keyword 'cars'. 
			1. A semi-colon separated list of keywords will return only entites that have all the keywords (AND), e.g. "q=keyword==cars;keyword==BMW" would only include BMW cars. 
			2. Multiple entity types are specified in a comma separated list, e.g. "q=entityType==article,entityType==collection" would find all articles and collections.
			3. Field values may be required with a double equals, e.g. author==Fred.
			Relative values for numeric or date types are specified with '=gt=', '=lt=', e.g. "q=publishedDate=lt=2014-10-14T19:09:00Z". 
			4. 'sortField' and 'descending' control the order of results    
		*/
	    public function get_entity_list($entityType, $publicationId = null, $query = "", $pageSize = 25, $page = 0, $sortField = "modified", $descending = true){
			// [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
			
		    // CONSTRUCT HEADER
			$headers = $this->set_headers();
			
			// CONSTRUCT URL: $endpoint/publication/$publication/$entityType?pageSize=$pageSize&page=$page&sortField=$sortField&descending=$descending&q=$query
			$url = sprintf("%s/publication/%s/%s?pageSize=%s&page=%s&sortField=%s&descending=%s&q=%s",
				$this->settings->producer_endpoint, // $endpoint
				!empty($publicationId) ? $publicationId : $this->default_publication(), // $publication
				$entityType, // $entityType
				$pageSize, // $pageSize
				$page, // $page
				$sortField, // $sortField
				$descending, // $descending
				$query // $query
			);

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
			$headers = $this->set_headers(self::MIME_JSON, self::MIME_JSON, $uploadID);
			
			// CONSTRUCT URL: $endpoint/publication/$publication/$entityType/$entityName;version=$version/contents
			$url = sprintf("%s/publication/%s/%s/%s;version=%s/contents",
				$this->settings->producer_endpoint, // $endpoint
				!empty($publicationId) ? $publicationId : $this->default_publication(), // $publication
				$entity->entityType, // $entityType
				$entity->entityName, // $entityName
				$entity->version // $version
			);
						
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
			$headers = $this->set_headers();
			
			// CONSTRUCT URL
			$url = $this->settings->producer_endpoint . "/job";
			
			// CONSTRUCT DATA
			$data = array(
				'workflowType' => 'publish',
				'entities' => array(),
			);
			
			if(!empty($schedule_date)){
				$data['scheduled'] = $this->formatDate($schedule_date);
			}
			
			$publication = !empty($publicationId) ? $publicationId : $this->settings->defaultPublication;
			
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
	    }
	    
	    // Entity can be single or array of entities
	    public function workflow_job($type, $entity, $list = null, $schedule_date = null, $publicationId = null){
			// [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
			// [+] Content-Type: application/json
			
			// CONSTRUCT HEADER
			$headers = $this->set_headers();
			
			// CONSTRUCT URL
			$url = $this->settings->producer_endpoint . "/job";
			
			// CLEANUP WORKFLOW TYPE
		    switch ($type) {
			   
			    // PREVIEW
				case 'preview':
					// triggers the preview for the publication, should only be called by the publication
					$workflow = 'publicationId';
					$workflow_entry = $this->entityId;
					break;
				
				// PUBLISH
				case 'publish':
					// fail-safe: autocorrects the publish namespace for layout & cardTemplate to "layout"
					if ($this->entityType === 'layout' || $this->entityType === 'cardTemplate') {
						$workflow_type = 'layout';
						$workflow = 'entities';
					}else if($this->entityType === 'collection'){
						$workflow = 'collectionUrl';
					}else{
						$workflow = 'entities';
					}
					break;
				
				// UNPUBLISH
				case 'unpublish':
					if ($this->entityType === 'layout' || $this->entityType === 'cardTemplate') {
						$type = 'unpublishLayout';
					}
					$workflow = 'entities';
					break;
				
				// NO RECOGNIZED JOB
				default:
					break;
				
		    }
			
			// CONSTRUCT DATA
			$data = array(
				'workflowType' => $type,
				$workflow => $workflow_entry,
			);
			
			// SET SCHEDULE DATE
			if(!empty($schedule_date)){
				$data['scheduled'] = $this->formatDate($schedule_date);
			}
			
			$publication = !empty($publicationId) ? $publicationId : $this->settings->defaultPublication;
			
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
	    }
	    
	    public function get_status($entity, $publicationId = null){
		    // [+] X-DPS-Client-Id: {client-id}
			// [+] X-DPS-Client-Version: {double-dot style notation}
			// [+] X-DPS-Client-Request-Id: {UUID}
			// [+] X-DPS-Client-Session-Id: {UUID}
			// [+] X-DPS-Api-Key: {base-64 encoded}
			// [+] Authorization: bearer {base-64 encoded}
			// [+] Accept: application/json
		    
		    // CONSTRUCT HEADER
			$headers = $this->set_headers();
			
			// CONSTRUCT URL: $endpoint/publication/$publication/$entityType/$entityName/contents;contentVersion=$contentVersion/
			$url = sprintf("%s/status/%s/%s/%s",
				$this->settings->producer_endpoint, // $endpoint
				!empty($publicationId) ? $publicationId : $this->default_publication(), // $publication
				$entity->entityType, // $entityType
				$entity->entityName // $entityName
			);

			// EXECUTE
		    $curl = new Curl('GET', $url, $headers); 
		    
		    // VERIFY RESPONSE
			$this->verify_response($curl, $entity);
			
			// RETURN RESPONSE
			return $curl->getResponseBody();
		
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
			$headers = $this->set_headers();
			
			// CONSTRUCT URL: $endpoint/publication/$publication/$entityType/$entityName/contents;contentVersion=$contentVersion/
			$url = sprintf("%s/publication/%s/%s/%s/contents;contentVersion=%s/",
				$this->settings->producer_endpoint, // $endpoint
				!empty($publicationId) ? $publicationId : $this->default_publication(), // $publication
				$entity->entityType, // $entityType
				$entity->entityName, // $entityName
				$entity->contentVersion // $contentVersion
			);

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
			$headers = $this->set_headers(self::MIME_JSON, $content_type);
									
			// CONSTRUCT URL: $endpoint/publication/$publication/$entityType/$entityName/contents;contentVersion=$contentVersion/$content_path
			$url = sprintf("%s/publication/%s/%s/%s/contents;contentVersion=%s/$content_path",
				$this->settings->producer_endpoint, // $endpoint
				!empty($publicationId) ? $publicationId : $this->default_publication(), // $publication
				$entity->entityType, // $entityType
				$entity->entityName, // $entityName
				$entity->contentVersion, // $contentVersion
				ltrim($content_path, '/') // $content_path
			);
			
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
			// WAS: 'application/zip'
			$headers = $this->set_headers(self::MIME_JSON, self::MIME_ZIP);
			
			// CONSTRUCT URL: $endpoint/publication/$publication/$entityType/$entityName;version=$version/contents/folio
			$url = sprintf("%s/publication/%s/%s/%s;version=%s/contents/folio",
				$this->settings->ingestion_endpoint, // $endpoint
				!empty($publicationId) ? $publicationId : $this->default_publication(), // $publication
				$entity->entityType, // $entityType
				$entity->entityName, // $entityName
				$entity->version // $version
			);
				
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
			$uploadID = empty($uploadID) ? $this->create_guid() : $uploadID;
			$headers = $this->set_headers(self::MIME_JSON, $content_type, $uploadID);
			
			// ADD IMAGE SIZES
			$imageSizes = $this->settings->get_image_sizes();
			if(!empty($imageSizes)){
				$headers[] = 'X-DPS-Image-Sizes: ' . $imageSizes;
			}
			
			// CONSTRUCT URL: $endpoint/publication/$publication/$entityType/$entityName/contents;contentVersion=$contentVersion/$content_path
			$url = sprintf("%s/publication/%s/%s/%s/contents;contentVersion=%s/%s",
				$this->settings->producer_endpoint, // $endpoint
				!empty($publicationId) ? $publicationId : $this->default_publication(), // $publication
				$entity->entityType, // $entityType
				$entity->entityName, // $entityName
				$entity->contentVersion, // $contentVersion
				ltrim($content_path, '/') // $content_path
			);
			
			// CONSTRUCT FILE PATH 
			$file_path = realpath($asset);
			
			// EXECUTE
		    $curl = new Curl('PUT', $url, $headers, $file_path, TRUE);
			
			// VERIFY RESPONSE
			$this->verify_response($curl, $entity);
			
			// RETURN RESPONSE
			return $curl->getResponseBody();		
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
			$headers[] = "X-DPS-Client-Version: ". $this->settings->client_version;
			$headers[] = "X-DPS-Client-Request-Id: ". $this->settings->request_id;
			$headers[] = "X-DPS-Client-Session-Id: ". $this->create_guid();
			$headers[] = "X-DPS-Api-Key: ". $this->settings->key;
			$headers[] = "Authorization: bearer ". $this->get_access_token();
			
			// CONSTRUCT URL
			$url = $this->settings->authorization_endpoint . "/permissions";
			
			// EXECUTE
		    $curl = new Curl('GET', $url, $headers);
		    
			// VERIFY RESPONSE
			try{
				$this->verify_response($curl);
				
				// Extract current publications and permissions (if available)
				$this->settings->publications = $this->get_publications_from_response($curl->getResponseBody());
				$this->settings->permissions = $this->get_permissions_from_response($curl->getResponseBody());
				$this->settings->save();
				
			}catch(Error $error){
				// Permissions don't exist or updated / remove them
		        $error->setTitle('Could not get user\'s permissions from Adobe\'s API');
		        
				$this->settings->publications = array();
				$this->settings->publications = array();
				$this->settings->save();
				throw $error;
			}
	    }
	    
	    private function collect_data($data = array()){
		    if(!empty($data)){
			    // Add content version
			    $version = $this->get_content_version($data);
			    if($version){
				    $data['contentVersion'] = $version;
				}
				
			    // Add version
			    $version = $this->get_version($data);
			    if($version){
				    $data['version'] = $version;
				}				
			}
			return $data;
	    }
	    	    
		private function save_response($body = array(), $entity){
			if(!empty($body)){
				// Save all fields
				foreach ($body as $key => $value){
					$entity->$key = $value;
					$entity->save_field($key);
				}
				
				// Save content version
				$contentVersion = $this->get_content_version($body);
				if($contentVersion){
					$entity->contentVersion = $contentVersion;
					$entity->save_field('contentVersion');
				}
				
				// Save version
				$version = $this->get_version($body);
				if($version){
					$this->version = $version;
					$entity->save_field('version');
				}
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
		
		private function get_content_version($data){
			$contentVersion = null;
			if(isset($data['_links'])){
				if(isset($data['_links']['contentUrl'])){
					$contentHref = $data['_links']['contentUrl']['href'];
					$index = strrpos($contentHref, '=');
					$contentVersion = substr($contentHref, $index + 1, -1);
				}
			}
			return $contentVersion;
		}
		
		private function get_version($data){
			if (isset($data['message'])){
				$index = strrpos($data['message'], 'currentVersion=');
				return substr($data['message'], $index + strlen('currentVersion='));
			}else if(isset($data['version'])){
				return $data['version'];
			}else{
				return FALSE;
			}
		}
		
		private function get_access_token(){
			if( empty($this->settings->access_token) ){
				$this->refresh_access_token();
			}
			return $this->settings->access_token;
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
		
		private function default_publication(){
			return (!empty($this->settings->defaultPublication)) ? $this->settings->defaultPublication : $this->publications[0]['id'];
		}
		
		// Check response
		private function verify_response($curl, $entity = null){
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
						
						// 400 Errors can have multiple errors:
						if(isset($curl->getResponseBody()['error'])){
							if( $curl->getResponseBody()['error']  == 'ride_AdobeID_acct_terms'){
								$error->setTitle('Adobe Account has not accepted the new Adobe Terms of Use');
								$error->setMessage('Go to https://accounts.adobe.com and login with the AdobeID you create the device_id and device_token for and accept the new Adobe Terms of Use.');
							}
						}else{
							$error->setTitle('Bad Request');
							$error->setMessage('One of the parameters was invalid.');
						}
						throw $error;
						break;
					case 401: // OAuth token invalid or expired
						// Refresh auth token and try again
						$this->refresh_access_token();
						$error->setTitle('Token Invalid or Expired');
						$error->setMessage('Your OAuth token is invalid. Please try refresh the page and trying again. If that does not work please update your API credentials in the plugin settings.');
						throw $error;
						break;
					case 403: // Forbidden - user's quota exceeded.
						$error->setTitle('Quota Exceeded');
						$error->setMessage('User\'s quota has been exceeded. Please wait before trying another request.');
						throw $error;
						break;
					case 404: // Not Found - specified entityName does not exist
						$error->setTitle('Not Found');
						$error->setMessage('The specific entity name: ' . $entity->entityName . ' does not exist.');
						// TODO: OFFER TO RE-Link or UNLINK
						throw $error;
						break;
					case 409: // Conflict - specified version is not the latest.
						$version = $this->get_version($curl->getResponseBody());
						$contentVersion = $this->get_content_version($curl->getResponseBody());
						
						$entity->version = empty($version) ? $entity->version: $version;
						$entity->save_field('version');
						$entity->contentVersion = empty($contentVersion) ? $entity->contentVersion: $contentVersion;
						$entity->save_field('contentVersion');
						
						$error->setTitle('Version Conflict');
						$error->setMessage('The version trying to update is not the latest version. Please refresh the page and try updating again.');
						throw $error;
						break;
					case 410: // Gone - Specified entity was deleted
						$error->setTitle('Entity Deleted');
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
						$error->setMessage('The specified entity version to publish is not the latest version.');
						throw $error;
						break;
					case 503: // Service Unavailable - if any of the third party services are unavailable.
						$error->setTitle('Service Unavailable');
						$error->setMessage('The API is unavailable.');
						throw $error;
						break;
					default: // Unknown
						$error->setTitle('Unknown Error');
						$error->setMessage('This is embarrassing, something unexpected happened and we do not have an answer right now.');
						throw $error;
						break;
				}
			}
		}	
			    
		private function set_headers( $accept_type = self::MIME_JSON, $content_type = self::MIME_JSON, $client_upload_id = null){
			$headers = array();
			$headers[] = 'X-DPS-Client-Id: ' . DPS_API_CLIENT_ID;
			$headers[] = "X-DPS-Client-Version: ". DPS_API_CLIENT_VERSION;
			$headers[] = "X-DPS-Client-Request-Id: ". $this->settings->request_id;
			$headers[] = "X-DPS-Client-Session-Id: ". $this->create_guid();
			$headers[] = "X-DPS-Api-Key: ". $this->settings->key;
			$headers[] = "Authorization: bearer ". $this->get_access_token();
			$headers[] = "Accept: $accept_type";
			$headers[] = "Content-Type: $content_type";
			$headers[] = "Connection: Close";
			
			// set optional request headers
			if ($client_upload_id){
				$headers[] = "X-DPS-Upload-Id: $client_upload_id";
			}
				
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
				'Accept: ' . self::MIME_JSON,
				'Content-Type: Content-Type: ' . self::MIME_URLENCODED
			);
			
			// CONSTRUCT URL: $endpoint/ims/token/v1?grant_type=device&client_id=$clientID&client_secret=$clientSecret&scope=$scope&device_token=$deviceToken&device_id=$deviceID
			$url = sprintf("%s/ims/token/v1?grant_type=device&client_id=%s&client_secret=%s&scope=%s&device_token=%s&device_id=%s",
				$this->settings->authentication_endpoint, // $endpoint
				$this->settings->key, // $clientID
				$this->settings->secret, // $clientSecret
				$scope, // $scope
				$this->settings->device_token, // $deviceToken
				$this->settings->device_id // $deviceID
			);
						
			// EXECUTE
			$curl = new Curl( 'POST', $url, $headers );
			
			// PARSE RESPONSE
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
				
				// Reset Access Token
				$this->settings->access_token = "";
				$this->settings->refresh_token = "";
				$this->settings->publications = array();
				$this->settings->permissions = array();
				$this->settings->save();
				
				throw $error;
			}else{
				// Save access token
				$this->settings->access_token = (isset($data['access_token'])) ? $data['access_token'] : null;
				$this->settings->refresh_token = (isset($data['refresh_token'])) ? $data['refresh_token'] : null;
				$this->settings->save();
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
		
		/* Filter out only attributes allowed by the API */
		private function prepEntity($entity, $overrides = array()){
			
			$entityAttributes = array();
			
			if(!empty($entity->entityId)){
				$entityAttributes = $this->get_entity($entity);
			}
			
			$filterOut = array_merge($entity->readOnly, $entity->internal);
			foreach(get_object_vars($entity) as $key => $value){
				if(!in_array($key, $filterOut) && !empty($value)){ 
					$entityAttributes[$key] = $value; 
				}else if(in_array($key, $filterOut) && $key !== "_links"){
					unset($entityAttributes[$key]);
				}else if(empty($value)){
					unset($entityAttributes[$key]);
				}
			}
									
			$entityAttributes["entityName"] = $entity->entityName;
			$entityAttributes["entityType"] = $entity->entityType;
			
			return array_merge($entityAttributes,$overrides);
		}
					        
    } // END class Adobe 
    
}