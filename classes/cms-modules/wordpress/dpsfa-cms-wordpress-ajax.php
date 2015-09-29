<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : CMS_Ajax
 * Description: This class contains ajax specific parameters and functions for WordPress.
 */
 
namespace DPSFolioAuthor;

if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );
	
if(!class_exists('DPSFolioAuthor\CMS_Ajax')) { 	
	class CMS_Ajax {
				
		public function __construct(){ }
		
		public function registerHookCallbacks(){
		// BACKEND	
			// register entity calls
			add_action( 'wp_ajax_get_entity', 						array( $this, 'get_entity' ) );
			add_action( 'wp_ajax_create_entity', 					array( $this, 'create_entity' ) );
			add_action( 'wp_ajax_link_entity', 						array( $this, 'link_entity' ) );
			add_action( 'wp_ajax_unlink_entity', 					array( $this, 'unlink_entity' ) );
			add_action( 'wp_ajax_update_entity', 					array( $this, 'update_entity' ) );
			add_action( 'wp_ajax_save_entity', 						array( $this, 'save_entity' ) );
			add_action( 'wp_ajax_delete_entity', 					array( $this, 'delete_entity' ) );
			add_action( 'wp_ajax_entity_list', 						array( $this, 'entity_list' ) );
			add_action( 'wp_ajax_publish_entity', 					array( $this, 'publish_entity' ) );
			add_action( 'wp_ajax_push_entity', 						array( $this, 'push_entity' ) );
			add_action( 'wp_ajax_push_entity_metadata', 			array( $this, 'push_entity_metadata' ) );
			add_action( 'wp_ajax_push_entity_contents', 			array( $this, 'push_entity_contents' ) );
			add_action( 'wp_ajax_push_article_folio', 				array( $this, 'push_article_folio' ) );
			add_action( 'wp_ajax_download_article', 				array( $this, 'download_article' ) );
			add_action( 'wp_ajax_add_entity_content', 				array( $this, 'add_entity_content' ) );
			add_action( 'wp_ajax_search_entities', 					array( $this, 'search_entities' ) );
			add_action( 'wp_ajax_filter_entities', 					array( $this, 'filter_entities' ) );
			add_action( 'wp_ajax_sync_article', 					array( $this, 'sync_article' ) );

			// register settings calls
			add_action( 'wp_ajax_get_settings', 					array( $this, 'get_settings' ) );
			add_action( 'wp_ajax_save_settings', 					array( $this, 'save_settings' ) );
			add_action( 'wp_ajax_refresh_settings', 				array( $this, 'refresh_settings' ) );

		// NON BACKEND
			// register entity calls
			add_action( 'wp_ajax_nopriv_get_entity', 				array( $this, 'get_entity' ) );
			add_action( 'wp_ajax_nopriv_create_entity', 			array( $this, 'create_entity' ) );
			add_action( 'wp_ajax_nopriv_link_entity', 				array( $this, 'link_entity' ) );
			add_action( 'wp_ajax_nopriv_unlink_entity', 			array( $this, 'unlink_entity' ) );
			add_action( 'wp_ajax_nopriv_update_entity', 			array( $this, 'update_entity' ) );
			add_action( 'wp_ajax_nopriv_save_entity', 				array( $this, 'save_entity' ) );
			add_action( 'wp_ajax_nopriv_delete_entity', 			array( $this, 'delete_entity' ) );
			add_action( 'wp_ajax_nopriv_entity_list', 				array( $this, 'entity_list' ) );
			add_action( 'wp_ajax_nopriv_publish_entity', 			array( $this, 'publish_entity' ) );
			add_action( 'wp_ajax_nopriv_push_entity', 				array( $this, 'push_entity' ) );
			add_action( 'wp_ajax_nopriv_push_entity_metadata', 		array( $this, 'push_entity_metadata' ) );
			add_action( 'wp_ajax_nopriv_push_entity_contents', 		array( $this, 'push_entity_contents' ) );
			add_action( 'wp_ajax_nopriv_push_article_folio', 		array( $this, 'push_article_folio' ) );
			add_action( 'wp_ajax_nopriv_download_article', 			array( $this, 'download_article' ) );
			add_action( 'wp_ajax_nopriv_add_entity_content', 		array( $this, 'add_entity_content' ) );
			add_action( 'wp_ajax_nopriv_search_entities', 			array( $this, 'search_entities' ) );
			add_action( 'wp_ajax_nopriv_filter_entities', 			array( $this, 'filter_entities' ) );
			add_action( 'wp_ajax_nopriv_sync_article', 				array( $this, 'sync_article' ) );
			
			// register settings calls
			add_action( 'wp_ajax_nopriv_get_settings', 				array( $this, 'get_settings' ) );
			add_action( 'wp_ajax_nopriv_save_settings', 			array( $this, 'save_settings' ) );
			add_action( 'wp_ajax_nopriv_refresh_settings', 			array( $this, 'refresh_settings' ) );
		}
		
		public function create_entity(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();

			$cloud = isset($data['cloud']) ? $data['cloud'] : null;
			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				$entity->create($cloud);
				$response["code"] = 200;
				$response["message"] = "Created " . $entity->entityType;
				$response['entity'] = $entity;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function link_entity(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$entity = $this->construct_entity($data['entity']);
			$cloudEntity = $this->construct_entity($data['link']);
			
			$response = array();
			try{
				$entity->link($cloudEntity);
				$response["code"] = 200;
				$response["message"] = "Linked " . $entity->entityType;
				$response['entity'] = $entity;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function unlink_entity(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();

			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				$entity->unlink();
				$response["code"] = 200;
				$response["message"] = "Unlinked " . $entity->entityType;
				$response['entity'] = $entity;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function publish_entity(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();

			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				$entity->publish();
				$response["code"] = 200;
				$response["message"] = "Published " . $entity->entityType;
				$response['entity'] = $entity;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function get_entity(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$cloud = isset($data['cloud']) ? $data['cloud'] : null;			
			$response = array();
			try{
				$entity = $this->construct_entity($data);
				if($cloud){ $entity->get($cloud); }
				$response["code"] = 200;
				$response["message"] = "Here is the " . $entity->entityType;
				$response['entity'] = $entity;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function update_entity(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();

			$cloud = isset($data['cloud']) ? $data['cloud'] : null;
			$entity = $this->construct_entity($data);
			
			$response = array();
			try{
				$entity->update($cloud);
				$response["code"] = 200;
				$response["message"] = "Updated " . $entity->entityType;
				$response['entity'] = $entity;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function save_entity(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$cloud = isset($data['cloud']) ? $data['cloud'] : null;
			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				$entity->save($cloud);
				$response["code"] = 200;
				$response["message"] = "Saved " . $entity->entityType;
				$response['entity'] = $entity;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function delete_entity(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$cloud = isset($data['cloud']) ? $data['cloud'] : null;
			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				$entity->delete($cloud);
				$response["code"] = 200;
				$response["message"] = "Deleted " . $entity->entityType;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function entity_list(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$filter = isset($data['filter']) ? $data['filter'] : null;
			$cloud = isset($data['cloud']) ? $data['cloud'] : null;
			
			$entity = $this->construct_entity($data);
			
			$response = array();
			try{
				$entities = $entity->get_list($filter, $cloud);
				$response["code"] = 200;
				$response["message"] = "List of " . $entity->entityType . "s";
				$response['entities'] = $entities;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function add_entity_content(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			
			// Check for files
			if(empty($_FILES)){ $this->return_json(415, array('code' => 415, 'message' => 'Trying to add content but missing the file.')); }

			// Get the entity
			$entity = $this->construct_entity(json_decode(stripcslashes($_REQUEST['entity']),true));
			
			$response = array();
			try{
				$entity->add_content($_REQUEST['contentType'], $_FILES);
				$response["code"] = 200;
				$response["message"] = "Added contents for " . $entity->entityType;
				$response['entity'] = $entity;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function push_entity(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			// Get the entity
			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				$entity->push();
				$entity->push_content();
				if($entity->entityType == "article"){
					$entity->push_article();
				}
				

				$response["code"] = 200;
				$response["message"] = "Pushed " . $entity->entityType;
				$response['entity'] = $entity;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function push_entity_metadata(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				$entity->push();
				$response["code"] = 200;
				$response["message"] = "Pushed " . $entity->entityType;
				$response['entity'] = $entity;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function push_article_folio(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$entity = $this->construct_entity(array('entityType'=>'article','id'=>$data['entity']['id']));
			
			$response = array();
			try{
				$entity->push_article();
				$response["code"] = 200;
				$response["message"] = "Pushed " . $entity->entityType;
				$response['entity'] = $entity;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function push_entity_contents(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$content = isset($data['content']) ? $data['content'] : null;
			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				$entity->push_content($content);
				$response["code"] = 200;
				$response["message"] = "Contents of " . $entity->entityType . "pushed";
				$response['entity'] = $entity;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function download_article(){
			if(!is_user_logged_in()){
				$this->return_json(401, array('message'=>'You must be logged in to download the article.'));
			}
			$entity = $this->construct_entity(array('id' => $_REQUEST['id'], 'entityType' => 'article' ));
			$bundlr = new Bundlr();
			$bundlr->download_zip($entity, true);
			
		}
		
		public function search_entities(){
			
		}
		
		public function filter_entities(){
			
		}
		
		public function sync_article(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$entity = $this->construct_entity(
				array(
					'id' => $data['id'], 
					'entityType' => 'article'
				)
			);
			
			$response = array();
			try{
				$entity->sync();
				$entity->refresh();
				$response["code"] = 200;
				$response["message"] = "Synced article";
				$response["entity"] = $entity;
			}catch(Error $error){
				$response["code"] = $error->getCode();
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response['code'], $response);
		}
		
		public function refresh_settings(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			
			$settings = new Settings();
			
			$response = array();
			try{
				$settings->update_api();
				$settings->refresh();
				
				$response["message"] = "Settings Saved";
				$response['settings'] = $settings;
				$response["code"] = 200;
			}catch(Error $error){
				$response["code"] = 300;
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
				$response['settings'] = $settings;
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response["code"], $response);
			
		}
		
		public function get_settings(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$settings = new Settings();
			
			$response = array(
				"settings" => $settings, 
				"phpErrors" => $errorLogging->getErrors()
			);
			
			$this->return_json(200, $response);
		}
		
		public function save_settings(){
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			
			$data = $this->get_response_data();
			$settingsData = $data["settings"];

			$settings = new Settings();
			foreach ( $settingsData as $key => $val) { $settings->$key = $val; }
			$settings->save();
			
			$response = array();
			try{
				$settings->update_api();
				$settings->refresh();
				$response["message"] = "Settings Saved";
				$response['settings'] = $settings;
				$response["code"] = 200;
			}catch(Error $error){
				$response["code"] = 300;
				$response["message"] = $error->getMessage();
				$response["options"] = $error->getOptions();
				$response["raw"] = $error->getRaw();
				$response['settings'] = $settings;
			}
			$response['phpErrors'] = $errorLogging->getErrors(); 
			$this->return_json($response["code"], $response);
		}
		
		private function construct_entity($data = array()){
			if(is_array($data)){
				if($data['entityType'] == "article"){
					$entity = new Article($data);
				}else if($data['entityType'] == "collection"){
					$entity = new Collection($data);
				}else if($data['entityType'] == "folio"){
					$entity = new Folio($data);
				} 
			}			
			return $entity;
		}
		
		public function generate_nonce(){
			return wp_create_nonce( DPSFA_NONCE_KEY );
		}
		
		private function get_response_data(){
			return json_decode(file_get_contents('php://input'),true);
		}
		
		private function verify_nonce(){
			$headers = array();
			foreach ($_SERVER as $name => $value){
				if (substr($name, 0, 5) == 'HTTP_'){
				   $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}

			foreach($headers as $key => $value){
				if(strtolower($key) == "wp-nonce"){
					$nonce = $value;
				}
			}
			
			$check = !empty($nonce) ? wp_verify_nonce( $nonce, DPSFA_NONCE_KEY ) : false;
			if(!$check){ 
				$this->return_json(401, array('message' => 'Unauthorized access, NONCE wrong'));
			}
		}
		
		private function return_json($code = 200, $data = array()){
			http_response_code($code);
			header("Content-Type: application/json; charset=UTF-8");
			if(!empty($_REQUEST['callback'])){
				echo $_REQUEST['callback'] . "(" . json_encode($data) . ")";
			}else{
				echo json_encode($data);
			}
			die();
		}
				
	} // end CMS_Ajax
}
?>