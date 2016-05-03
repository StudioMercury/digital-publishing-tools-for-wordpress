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
			add_action( 'wp_ajax_sync_entity', 						array( $this, 'sync_entity' ) );
			// register settings calls
			add_action( 'wp_ajax_get_settings', 					array( $this, 'get_settings' ) );
			add_action( 'wp_ajax_save_settings', 					array( $this, 'save_settings' ) );
			add_action( 'wp_ajax_refresh_settings', 				array( $this, 'refresh_settings' ) );
		}
		
		public function create_entity(){
			$this->capture_messages('start');
			
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
				$response['entity'] = $entity->to_array();
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
			$this->capture_messages('start');
			
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();

			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				$entity->link($data['link']);
				$response["code"] = 200;
				$response["message"] = "Linked " . $entity->entityType;
				$response['entity'] = $entity->to_array();
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
			$this->capture_messages('start');
			
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();

			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				$entity->unlink();
				$response["code"] = 200;
				$response["message"] = "Unlinked " . $entity->entityType;
				$response['entity'] = $entity->to_array();
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
			$this->capture_messages('start');
			
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();

			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				$entity->publish();
				$response["code"] = 200;
				$response["message"] = "Published " . $entity->entityType;
				$response['entity'] = $entity->to_array();
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
			$this->capture_messages('start');
			
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$cloud = isset($data['cloud']) ? filter_var($data['cloud']) : null;
			$entity = $this->construct_entity($data, TRUE);
			if(!empty($entity->entityId)){
				// update cloud entity if entityId exists
				$entity->update(true);
			}

			$response = array();
			try{
				$response['entity'] = $cloud ? $entity->get() : $entity->to_array();
				$response["code"] = 200;
				$response["message"] = "Here is the " . $entity->entityType;
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
			$this->capture_messages('start');
			
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
				$response['entity'] = $entity->to_array();
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
			$this->capture_messages('start');
			
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
				$response['entity'] = $entity->to_array();
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
			$this->capture_messages('start');
			
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
			$this->capture_messages('start');
			
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$filters = isset($data['filters']) ? $data['filters'] : null;
			$cloud = isset($data['cloud']) ? $data['cloud'] : null;
			$entity = $this->construct_entity($data);
			
			$response = array();
			try{
				$entities = $entity->get_list($filters, $cloud);
				$response['entities'] = array();
				foreach($entities as $entity){
					array_push($response['entities'], $entity->to_array());
				}
				$response["code"] = 200;
				$response["message"] = "List of " . $entity->entityType . " entities";
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
			$this->capture_messages('start');
			
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
				$response['entity'] = $entity->to_array();
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
			$this->capture_messages('start');
			
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			// Get the entity
			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				if(empty($entity->entityId)){
					$entity->create(true);
				}
				$entity->push();
				$entity->push_contents();
				$response["code"] = 200;
				$response["message"] = "Pushed " . $entity->entityType;
				$response['entity'] = $entity->to_array();
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
			$this->capture_messages('start');
			
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				$entity->push();
				$response["code"] = 200;
				$response["message"] = "Pushed " . $entity->entityType;
				$response['entity'] = $entity->to_array();
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
			$this->capture_messages('start');
			
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$entity = $this->construct_entity(array('entityType'=>'article','id'=>$data['entity']['id']));
			
			$response = array();
			try{
				$entity->push_article();
				$response["code"] = 200;
				$response["message"] = "Pushed " . $entity->entityType;
				$response['entity'] = $entity->to_array();
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
			$this->capture_messages('start');
			
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$content = isset($data['content']) ? $data['content'] : null;
			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				if(!empty($content)){
					$entity->push_content($content);
				}else{
					$entity->push_contents();
				}
				$response["code"] = 200;
				$response["message"] = "Contents of " . $entity->entityType . " pushed";
				$response['entity'] = $entity->to_array();
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
			
			try{
				$bundlr->download_zip($entity, true);
			}catch(Error $error){
				echo "<h1>".$error->getTitle()."</h1>";
				echo "<p>".$error->getMessage()."</p>";
				die();
			}
		}
		
		public function sync_entity(){
			$this->capture_messages('start');
			
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			$data = $this->get_response_data();
			
			$entity = $this->construct_entity($data['entity']);
			
			$response = array();
			try{
				$entity->sync($data['presetName']);
				$entity->refresh();
				$response["code"] = 200;
				$response["message"] = "Entity Synced";
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
			$this->capture_messages('start');
			
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			
			$settings = new Settings();
			
			$response = array();
			try{
				$settings->update_api();
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
			$this->capture_messages('start');
			
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			
			$settings = new Settings();
			
			$response = array();
			try{
				$settings->update_api();
				$response["code"] = 200;
				$response['settings'] = $settings;
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
		
		public function save_settings(){
			$this->capture_messages('start');
			
			$errorLogging = new ErrorLogging(); // Capture PHP errors
			$this->verify_nonce(); // Verify NONCE
			
			$data = $this->get_response_data();
			$settingsData = $data["settings"];

			$settings = new Settings($settingsData);

			$response = array();
			try{
				$settings->save();
				$settings->refresh();
				$settings->update_api();
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
		
		private function construct_entity($data = array(), $update = FALSE){
			if(is_array($data) && !empty($data['entityType'])){
				$className = 'DPSFolioAuthor\\' .  ucwords($data['entityType']);
				return new $className($data, $update);
			}else{
				$response = array(
					'code' => 400,
					'message' => 'Could not get the entity: Invalid data to create entity (' . $data['entityType'] . ').',
				);
				$this->return_json($response['code'], $response);
			}			
		}
		
		private function get_response_data(){
			return json_decode(file_get_contents('php://input'),true);
		}
		
		public function generate_nonce(){
			return wp_create_nonce( DPSFA_NONCE_KEY );
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
		
		// Stop PHP from printing errors / messages before sending JSON
		private function capture_messages($action = "start"){
			if($action == "start"){
				ob_start();
			}else{
				return ob_get_clean();
			}
		}
		
		private function return_json($code = 200, $data = array()){
			$data["serverErrors"] = $this->capture_messages('stop');
			
			http_response_code($code);
			header( 'Content-Type: application/json; charset=UTF-8' );
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