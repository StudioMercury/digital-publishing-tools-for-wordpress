<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : CMS
 * Description: This class contains settings specific parameters and functions.
 */
 
namespace DPSFolioAuthor;
 
 // TODO in UPGRADE:
 // Update id to hostedID
 // update folio to folio Id
    
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
    die( 'Access denied.' );

// Load all wordpress classes:
require_once(  DPSFA_DIR . '/classes/cms-modules/wordpress/dpsfa-cms-wordpress-cpt-article.php' ); // Class for WP Article Post Type
require_once(  DPSFA_DIR . '/classes/cms-modules/wordpress/dpsfa-cms-wordpress-ajax.php' );	// Class for WP Ajax Calls	

if(!class_exists('DPSFolioAuthor\CMS')) { 
	
    class CMS {
                
        public function __construct() { }
        
// ===== CMS INIT functions ===== //
		public function init(){
	    	log_message("CMS: Initializing WordPress CMS Module.");
			
			// Register CMS callbacks
            $this->registerHookCallbacks();
	    }
                
        public function get_cms_version(){
	        return get_bloginfo('version');
        }
                        
        public function registerHookCallbacks(){	
	        // Register CMS callbacks 
    		add_action( 'init', array( $this, 'initcms' ) );
    		
    		// Activate / Deactivate / Uninstall
    		register_activation_hook( DPSFA_FILE, array( 'DPSFolioAuthor\CMS', 'activate' ) );
			register_deactivation_hook( DPSFA_FILE, array( 'DPSFolioAuthor\CMS', 'deactivate' ) );
			register_uninstall_hook( DPSFA_FILE, array( 'DPSFolioAuthor\CMS', 'uninstall') );
			
    		// Register Article Posttype
    		$Article = new CMS_Article();
    		$Article->registerHookCallbacks();
    		
    		// Register Collection Posttype
    		//$Collection = new CMS_Collection();
    		//$Collection->registerHookCallbacks();  
    		
    		// Register AJAX Calls for WordPress
    		$Ajax = new CMS_Ajax();
    		$Ajax->registerHookCallbacks(); 
    		
    		// Register Plugin Page
    		add_action('admin_menu', array( $this, 'register_plugin_page' ), 5 );
    		
    		// Templates
    		add_filter( 'template_include', array( $this, 'template_override') );
    		
    		//Bulk Importing
    		add_action('admin_footer-edit.php', array($this, 'add_bulk_import') );
    		add_action('load-edit.php', array($this, 'bulk_import') );
			add_action('admin_notices',  array($this,'bulk_import_notice') );
        }
        
        public function initcms(){
	        
        }
		
		static function activate(){}
		static function deactivate(){}
		static function uninstall(){}
		
		
// ===== WP PAGE functions ===== //
		
		public function register_plugin_page(){
			add_menu_page( DPSFA_NAME, DPSFA_SHORT_NAME, 'manage_options', DPSFA_SLUG, array($this, 'plugin_page'), 'dashicons-book', 58.1234 );
			//add_submenu_page( DPSFA_SLUG, DPSFA_NAME, 'Settings', 'manage_options', DPSFA_SLUG."_home", array($this, 'plugin_home_page') ); // For native view instead of full app
		}
		
		public function template_override($template){
			global $post;

			if($post->post_type == DPSFA_Article_Slug){
				// If post type is article
				$article = new Article(array('id' => $post->ID));
				if(file_exists($article->template)){
					return $article->template;
				}else{
					$Template = new Templates();
					if(empty($Template->templates) || !file_exists($Template->templates[0]['path'])){
						die("There are no valid templates available.");
					}else{
						return $Template->templates[0]['path'];
					}
				}
			}

            return $template;
		}
		
		public function plugin_home_page(){
			echo "Settings PAGE";
		}
		
		public function plugin_page(){
			// TODO: Compress these into two files: one script one css
			
			// APP SCRIPTS
			wp_enqueue_script( 'DPSFolioAuthor-angular', plugins_url( DPSFA_DIR_NAME . '/app/bower_components/angular/angular.min.js' ));
			wp_enqueue_script( 'DPSFolioAuthor-angular-animate', plugins_url( DPSFA_DIR_NAME . '/app/bower_components/angular-animate/angular-animate.min.js') );
			wp_enqueue_script( 'DPSFolioAuthor-angular-sanitize', plugins_url( DPSFA_DIR_NAME . '/app/bower_components/angular-sanitize/angular-sanitize.min.js') );
			wp_enqueue_script( 'DPSFolioAuthor-angular-ui-router', plugins_url( DPSFA_DIR_NAME . '/app/bower_components/angular-ui-router/release/angular-ui-router.min.js') );
			wp_enqueue_script( 'DPSFolioAuthor-underscore', plugins_url( DPSFA_DIR_NAME . '/app/bower_components/underscore/underscore-min.js') );
			wp_enqueue_script( 'DPSFolioAuthor-angular-bootstrap', plugins_url( DPSFA_DIR_NAME . '/app/bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js') );
			wp_enqueue_script( 'DPSFolioAuthor-ng-tags-input', plugins_url( DPSFA_DIR_NAME . '/app/bower_components/ng-tags-input/ng-tags-input.min.js') );
			wp_enqueue_script( 'DPSFolioAuthor-angular-file-upload', plugins_url( DPSFA_DIR_NAME . '/app/bower_components/angular-file-upload/angular-file-upload.min.js') );
			wp_enqueue_script( 'DPSFolioAuthor-angular-infinite-scroll', plugins_url( DPSFA_DIR_NAME . '/app/bower_components/ngInfiniteScroll/build/ng-infinite-scroll.min.js') );

			wp_enqueue_script( 'DPSFolioAuthor-app', plugins_url( DPSFA_DIR_NAME . '/app/scripts/app.js') );
			wp_enqueue_script( 'DPSFolioAuthor-controllers', plugins_url( DPSFA_DIR_NAME . '/app/scripts/controllers.js') );
			wp_enqueue_script( 'DPSFolioAuthor-configuration', plugins_url( DPSFA_DIR_NAME . '/app/scripts/configuration.js') );
			wp_enqueue_script( 'DPSFolioAuthor-services', plugins_url( DPSFA_DIR_NAME . '/app/scripts/services.js') );
			wp_enqueue_script( 'DPSFolioAuthor-directives', plugins_url( DPSFA_DIR_NAME . '/app/scripts/directives.js') );

			// APP STYLES
			wp_enqueue_style( 'DPSFolioAuthor-bootstrap-coral-ui', plugins_url( DPSFA_DIR_NAME . '/app/bower_components/adobe-coral-bootstrap-theme/css/bootstrap-coral-ui.css') );
			wp_enqueue_style( 'DPSFolioAuthor-theme', plugins_url( DPSFA_DIR_NAME . '/app/bower_components/adobe-coral-bootstrap-theme/css/theme.css') );
			wp_enqueue_style( 'DPSFolioAuthor-ui-bootstrap-csp', plugins_url( DPSFA_DIR_NAME . '/app/bower_components/angular-bootstrap/ui-bootstrap-csp.css') );
			wp_enqueue_style( 'DPSFolioAuthor-ng-tags', plugins_url( DPSFA_DIR_NAME . '/app/bower_components/ng-tags-input/ng-tags-input.bootstrap.min.css') );
			
			// LOCALIZE SCRIPT
			$WPAjax = new CMS_Ajax();
			wp_localize_script( 'DPSFolioAuthor-configuration', 'DPSFolioAuthorOverride', array( 
				'name'		=> 'Publishing for WordPress',
				'endpoint' 	=> esc_url_raw(admin_url('admin-ajax.php')),
				'nonce' 		=> $WPAjax->generate_nonce(),
				'viewPath' 	=> plugins_url( DPSFA_DIR_NAME ) . "/app/",
				'CMSUrl' 	=> get_admin_url()
			));
			echo file_get_contents(DPSFA_DIR . '/app/wp-index.html', FILE_USE_INCLUDE_PATH);
		}
		
	
// ===== ENTITY functions ===== //
		public function create_entity($entityType = "article", $data = array()){
			$id = $this->create_cms_entity($entityType);
			
			// Populate entity with data
			if(!empty($data)){ 
				$this->save_entity($id, $entityType, $data); 
			}
			
			return $id;
		}
		
		public function get_entity_data($id){
			// Get the post
			$post = get_post($id);
			
			// Get the post Metadata (custom fields)
			$meta = get_post_meta($id);
			
			// Get the entity type from the post
			$entityType = $this->get_entity_type($post->post_type);
			
			// Assemble data (custom fields)
			$data = array();
			foreach($meta as $rawKey => $value){
				$key = str_replace($post->post_type . '_', '', $rawKey);
				if(is_array($value) && !empty($value)){
					$data[$key] = maybe_unserialize(reset($value));
				}
			}
			
			// Upgrade entity (if not current version)
			if(empty($data['entityVersion']) || $data['entityVersion'] < DPSFA_Entity_Version){
				$data = $this->upgrade_entity($id, $entityType, $data);
			}
			
			// If entityName is blank, make sure it has one
			if(empty($data['entityName'])){
				$this->save_field($id, $entityType, 'entityName', $post->post_name);
				$data['entityName'] = $this->get_field($id, $entityType, 'entityName');
			}			
			
			// Add other post data
			$data["id"] = $id;
			$data["body"] = $post->post_content;
			$data["local_modified"] = $post->post_modified;
			$data["cmsPreview"] = get_permalink($id);
			$data["editUrl"] = get_edit_post_link($id, '');
						
			// Return entity data
			return $data;
		}
		
		public function upgrade_entity($id, $entityType, $data){
			// If no entity Version || version <= 2.1
			if(empty($version) || $version < 2.2){
				// As of 2.1, contents are handled differently:
				foreach($data['contents'] as $key => $value){
					if(is_array($value) && array_key_exists('id',$value)){
						$this->save_field($id, $entityType, $key, $value['id']);
						$data[$key] = $value['id'];
					}
				}
				$this->save_field($id, $entityType, 'entityVersion', DPSFA_Entity_Version);
			}
			return $data;
		}
		
		/*  FILTERS 
			limit: 30 - the number of entities to return
			page: 1 - the page based on the limit
			metadata: array() - an array of metadata to filter by
		*/
		public function get_entity_list($entityType, $filters = array()){
			$entities = array();
			
			$args = array(
				'posts_per_page' => isset($filters['limit']) ? $filters['limit'] : 30,
				'paged' => isset($filters['page']) ? $filters['page'] : 1,
				'post_type' => $this->get_entity_slug($entityType),
			);

			if( isset($filters['orderby']) ){
				$args['orderby']  = 'meta_value';
				$args['order']  = isset($filters['order']) ? $filters['order'] : "ASC";
				$args['meta_key'] = $this->get_entity_slug($entityType) . '_' . $filters['orderby'];
			}

			$query = new \WP_Query($args);
			if($query->have_posts()){
				while($query->have_posts()){
					$query->the_post();
					array_push($entities, $query->post->ID);
				}
			}
			return $entities;
		}
		
		public function save_entity($id, $entityType, $data){
			foreach( $data as $key => $value ){
				$this->save_field($id, $entityType, $key, $value);
			}
		}

		public function save_field($id, $entityType, $field, $value){
			$slug = $this->get_entity_slug($entityType);

			// Exception for EntityName, push this to the title to make it easier to read
			if($field == 'entityName'){
				// If value is empty, use the post slug
				if(empty($value)){
					$post = get_post($id);
					$value = $post->post_name;
				}
				// Verify entity name doesn't exist, if it does, make it unique
				$exists = $this->entity_name_exists($entityType, $value);
				if($exists !== FALSE && $exists != $id){
					$value = $value . "-1";
					while($this->entity_name_exists($entityType, $value)){
						$value++;
					}
				}
			}
			
			if( $value === null || $value == "" ){
				return delete_post_meta($id, $slug . '_' . $field);
			}else{
				return update_post_meta($id, $slug . '_' . $field, $value);
			}
		}
				
		public function delete_field($id, $entityType, $field){
			$slug = $this->get_entity_slug($entityType);
			return delete_post_meta($id, $slug . '_' . $field);
		}
		
		public function get_field($id, $entityType, $field){
			$slug = $this->get_entity_slug($entityType);
			return get_post_meta($id, $slug . '_' . $field);
		}
		
		public function delete_entity($id){
			return wp_delete_post($id, true);
		}
				
		public function duplicate_entity($id, $entityType = "article"){
			// Create entity
			$entityId = $this->create_entity($entityType);
			
			// Sync from origin
			$this->sync_from_origin($id, $entityId);
			
			// Return new entity id
			return $entityId;
		}
		
		public function handle_file_upload($id, $FILE){
			return media_handle_upload(key($FILE), $id, array(), array( 'test_form' => false ));
		}
				
		public function get_entity_content($contentId){
			$content = array(
				'id' => $contentId,
				'path' => get_attached_file($contentId),
				'url' => wp_get_attachment_url($contentId),
			);
			
			if(wp_attachment_is_image($contentId)){
				$sizes = get_intermediate_image_sizes();
				$uploads = wp_upload_dir();
				$content["sizes"] = array();
				foreach($sizes as $size){
					$image = image_get_intermediate_size($contentId, $size);
					$url = wp_get_attachment_image_src($contentId, $size);
					$path = $uploads['basedir'] . "/" . $image['path'];
					$content["sizes"][$size] = array(
						'path' => $path,
						'url' => $url[0],
					);
				}
			}
			
			return $content;
		}
		
		public function origin_exists($id){
			$origin = get_post($id);
			return empty($origin) ? false : true;
		}
				
		private function get_entity_slug($entityType){
			return DPSFA_PREFIX . $entityType;
		}
		
		private function get_entity_type($postType){
			return str_replace(DPSFA_PREFIX, '', $postType);
		}
		
		private function create_cms_entity($entityType = null, $entityName = null){			
			$args = array(
				'post_title' => "$entityType created at " . time(),
				'post_type' => $this->get_entity_slug($entityType),
				'post_content' => '',
				'post_excerpt' => '',
				'post_status' => 'publish'
			);
			
			$postID = wp_insert_post($args);
			
			if(!is_wp_error($postID)){
				return $postID;
			}else{
				$error = new Error("Error", 400);
				$error->setTitle('Could not create entity');
				$error->setMessage('Wordpress could not create the entity');
				$error->setRaw($post);
				throw $error;
			}
			return $id;
		}
		
		private function entity_name_exists($entityType, $name){
			$postType = $this->get_entity_slug($entityType);
			$entityExistsArgs = array(
				'numberposts'	=> 1,
				'post_type'		=> $postType,
				'meta_key'		=> $postType . '_entityName',
				'meta_value'	=> $name
			);
			$postsExist = new \WP_Query( $entityExistsArgs );
			wp_reset_postdata();
			return ($postsExist->found_posts > 0) ? $postsExist->posts[0]->ID : FALSE;
		}


       
// ===== SETTINGS functions ===== //
		private function registerSettings(){
			# only adds an empty option if one does not exist yet
            # helps to fix double validation when inserting new option

            // Register Settings options
            $settings_fields = $this->get_settings_fields();
            foreach($settings_fields as $key => $value){
	            add_option(DPSFA_PREFIX . $key, $value);
	        }
		}
		
		static function register_wp_settings_page(){
			
		}
		
		private function get_settings_fields(){
			return get_class_vars('DPSFolioAuthor\Settings');
		}
		
		public function get_settings(){
            $settings = $this->get_settings_fields();
            foreach($settings as $key => $value){
	            $settings[$key] = get_option(DPSFA_PREFIX . $key, $value);
            }
            return $settings;
		}
		
		private function get_option($option){
			return get_option(DPSFA_PREFIX . $option);
		}
		
		public function save_settings($settings){
			foreach($settings as $key => $value){
				$this->save_setting($key, $value);
			}
		}
		
		public function save_setting( $key, $value ){
			update_option( DPSFA_PREFIX . $key, $value );
		}
		
		private function decrypt($string) {
            $output = false;
            
            $secret_key = wp_salt();
            $secret_iv = wp_salt('secure_auth');
            // hash
            $key = hash(DPSFA_HASH_ALGO, $secret_key);

            // iv
            $iv = substr(hash(DPSFA_HASH_ALGO, $secret_iv), 0, DPSFA_ENCRYPTION_BYTES);
            $output = openssl_decrypt(base64_decode($string), DPSFA_ENCRYPTION_METHOD, $key, 0, $iv);

            return $output;
        }

        private function decryptData($data) {
	        foreach($data as $key => $value){
                $data[$key] = $this->decrypt($value);
			}
            return $data;
        }

        private function encrypt($string) {
            $output = false;
            
            $secret_key = wp_salt();
            $secret_iv = wp_salt('secure_auth');

            // hash
            $key = hash(DPSFA_HASH_ALGO, $secret_key);

            // iv
            $iv = substr(hash(DPSFA_HASH_ALGO, $secret_iv), 0, DPSFA_ENCRYPTION_BYTES);

            $output = openssl_encrypt($string, DPSFA_ENCRYPTION_METHOD, $key, 0, $iv);
            $output = base64_encode($output);

            return $output;
        }

        private function encryptData($data) {
	        foreach($data as $key => $value){
	            $data[$key] = $this->encrypt($value);
	        }
            return $data;
        }
        
		
// ===== TEMPALTE functions ===== //
		// Return an array of template files.
		// CMS might need to run shortcodes and include extra files during bundle
		// Now is the time before it gets packaged.
		public function get_custom_templates($templateFolder){
			$cmsPath = rtrim(get_template_directory(), '/\\');
			$directory = $cmsPath . "/" . $templateFolder;
			$files = file_exists($directory) ? @scandir($directory) : array();
			$templates = array();
			foreach($files as $file){
				$fileParts = pathinfo($file);
				if(isset($fileParts['extension']) && $fileParts['extension'] == "php"){
					array_push($templates, array(
						"name" => $file, 
						"path" => $directory . "/" . $file, 
						"modified" => date("F d Y H:i:s", filemtime($directory . "/" . $file)),
						"location" => "Theme"));
				}
			}
			return $templates;
		}
		
		public function get_template_files($entity){
			return apply_filters( 'dpsfa_bundle_article', $entity );
		}
		
		public function get_entity_url($entity){
    		$URL = get_permalink( $entity->id );
    		return $URL;
		}
		
// ===== CUSTOM WORDPRES FUNCIONALITY functions ===== //
		public function sync_from_origin($originId, $entityId){
			$post = get_post( $originId );
				
			$toUpdate = array(
				'ID'           	 => $entityId,
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $post->post_author,
				'post_content'   => $post->post_content,
				'post_date'   	 => $post->post_date,
				'post_excerpt'   => $post->post_excerpt,
				'post_password'  => $post->post_password,
				'post_status'    => 'publish',
				'post_title'     => $post->post_title,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);
			
			// Update the post into the database
			wp_update_post( $toUpdate );
			
			// Save all custom fields
			$customFields = get_post_meta( $originId );
			foreach( $customFields as $key => $value ){
				foreach($value as $data){
					add_post_meta($entityId, $key, $data);
				}
			}				
			
			// Duplciate all taxonomies
			$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
			foreach ($taxonomies as $taxonomy) {
				$post_terms = wp_get_object_terms($originId, $taxonomy, array('fields' => 'slugs'));
				wp_set_object_terms($entityId, $post_terms, $taxonomy, false);
			}
		}
				
				
// ===== IMPORTING functions ===== //
		public function import( $id, $presetName = "" ){
			
			// Get current settings
			$settings = new Settings();
			
			// Get Preset
			$preset = $settings->importPresets[0]; // Default to the first one if nothing is selected
			foreach($settings->importPresets as $singlePreset){
				if($singlePreset["name"] == $presetName){
					$preset = $singlePreset;
				}
			}
			
			// Verify we have an entity type
			$entityType = isset($preset["entityType"]) ? $preset["entityType"] : FALSE;
			
			// Get original post to Import
			$post = get_post( $id );
			
			// Duplicate the original post
			$entityId = $this->duplicate_entity($id, $entityType);
			
			// Get post data from preset
			$data = $this->generate_preset_data($id, $preset['presets']);
			
			// set origin of post
			$data['origin'] = $id; 
			$data['time_synced'] = time(); 
			
			// save entity data
			$this->save_entity($entityId, $entityType, $data); 		
			
			// return entity Id
			return $entityId;
		}
		
		public function sync($id, $origin, $presetName = ""){			
			if(!empty($presetName)){
				// Get Preset
				$settings = new Settings();
				$preset = $settings->importPresets[0]; // Default to the first one if nothing is selected
				foreach($settings->importPresets as $singlePreset){
					if($singlePreset["name"] == $presetName){
						$preset = $singlePreset;
					}
				}
				
				// Get the post
				$post = get_post($id);
			
				// Get the entity type from the post
				$entityType = $this->get_entity_type($post->post_type);

				// Get data to sync from origin
				$data = $this->generate_preset_data($origin, $preset['presets']);
				$data['time_synced'] = time(); 

				// save entity data
				$this->save_entity($id, $entityType, $data); 
			}
		}

		public static function import_tags() {
			return array(
				"%ID%" 							=> "Post's ID",
				"%post_title%" 					=> "Post's Title",
				"%post_excerpt%" 				=> "Post's Excerpt",
				"%post_author%" 				=> "Post's Author's ID",
				"%post_author_first_name%" 		=> "Post's Author's First Name",
				"%post_author_last_name%" 		=> "Post's Author's Last Name",
				"%post_date%" 					=> "Post's Date",
				"%post_name%" 					=> "Post's Name (slug)",
				"%post_type%" 					=> "Post's Type",
				"%featured_image_id%" 			=> "Post's Featured Image ID (only works on thumbnail and social media image fields)",
				"%content%" 					=> "Post's Content",
				"%tags%" 						=> "Post's Tags",
				"%categories%" 					=> "Post's Categories",
				"%permalink%" 					=> "Post's Permalink",
				"%custom_FIELDNAME%"			=> "Custom field name. Replace FIELDNAME with the custom field's slug"
			);
		}
		
		public function get_import_tags_from_entity($id){
			// IMPORT TAGS
			$tags = array();
			
			// BASIC POST DATA
			$post = get_post($id, ARRAY_A);
			foreach($post as $key => $value){
				$tags["%".$key."%"] = $value;
			}
			
			// POST META
			$post_meta = get_post_meta($id);
			foreach($post_meta as $key => $value){
				$tags["%custom_" . $key . "%"] = reset($value);
			}
			
			// POST AUTHOR
			$tags['%post_author_first_name%'] = get_user_meta($tags['%post_author%'], 'first_name', true);
			$tags['%post_author_last_name%'] = get_user_meta($tags['%post_author%'], 'last_name', true);
			
			// FEATURED IMAGE
			$tags['%featured_image_id%'] = get_post_thumbnail_id($id);
			
			// CONTENT
			$tags['%content%'] = $post["post_content"];
			
			// TAGS
			$tags['%tags%'] = wp_get_post_tags( $id, array('fields' => 'names') );
			
			// CATEGORIES
			$tags['%categories%'] = wp_get_post_categories( $id, array('fields' => 'names') );
			
			// PERMALINK
			$tags['%permalink%'] = get_permalink($id);

			return $tags;
		}
		
		public function generate_preset_data($id, $presets){
			// Start with new data array
			$data = array();
			
			// Grab import tags from an entity
			$tags = $this->get_import_tags_from_entity($id);
			
			// Add presets to data array
	        $find = array_keys($tags);
	        $replace = array_values($tags);
			
			$arrayFind = array();
			$arrayReplace = array();
	        foreach($replace as $key=>$value){
		        if(is_array($value)){
			        array_push($arrayFind, $find[$key]);
			        array_push($arrayReplace, $replace[$key]);
			        unset($find[$key]);
			        unset($replace[$key]);
		        }
	        }
	        
	        foreach($presets as $key => $template){
		        if(!empty($template)){
			        if(!is_string($template)){
						$data[$key] = $template;
			        }else if(array_key_exists($template, $tags)){
						// If it's an array or 
						$data[$key] = $tags[$template];
			        }else{
						
						$data[$key] = str_replace($find, $replace, $template);
				        
				        // Find array values
				        foreach($arrayFind as $index => $tofind){
					        if(strpos($template, $tofind) !== FALSE){
						        if(!isset($data[$key]) || !is_array($data[$key])){
							        $data[$key] = array();
						        }
						        if(is_array($arrayReplace[$index])){
							        foreach($arrayReplace[$index] as $toEnter){
								        array_push($data[$key], $toEnter);
							        }
						        }
					        }
				        }
				        
			        }
		        }
	        }
	        	        
	        // return data array
	        return $data;
		}
			
// ===== CUSTOM WORDPRES FUNCIONALITY functions ===== //		
		public function add_bulk_import(){
			global $post_type;
			if($post_type != DPSFA_Article_Slug) { 
				$Settings = new Settings();
				?>
				
				<script type="text/javascript">
					jQuery(document).ready(function() {
						var bulkSelector = "select[name='action']";
						var presetSelector = "[name='entity_preset_name']"
						
						jQuery('<option>').val('importEntity').text('Import into Digital Publishing Tools').appendTo(bulkSelector);
						jQuery('<input type="hidden" name="entity_import_preset" value="default">').insertAfter("select[name='action']");
						jQuery('<select name="entity_preset_name"></select>').insertAfter(bulkSelector).hide();
						
						<?php foreach($Settings->importPresets as $preset):?>
							jQuery('<option>').val('<?php echo $preset["name"]; ?>').text('<?php echo $preset["name"]; ?>').appendTo(presetSelector);
						<?php endforeach; ?>
						
						jQuery(bulkSelector).change(function(event){
							var value = jQuery(this).find(":selected").val();
							if(value == "importEntity"){
								jQuery(presetSelector).show();
								jQuery("[name='entity_import_preset']").val(jQuery(this).find(":selected").val());
							}else{
								jQuery(presetSelector).hide();
							}
						});
						
						jQuery(presetSelector).change(function(event){
							var value = jQuery(this).find(":selected").val();
							jQuery("[name='entity_import_preset']").val(jQuery(this).find(":selected").val());
						});
					});
				</script>
				
				<?php
			}
		}
		
		public function bulk_import(){
			$wp_list_table = _get_list_table('WP_Posts_List_Table');
			$action = $wp_list_table->current_action();

			switch($action) {
				case 'importEntity':
					check_admin_referer('bulk-posts');

					$imported = 0;
					$ids = array();
					$post_ids = isset($_REQUEST["post"]) ? $_REQUEST["post"] : array();
					foreach( $post_ids as $post_id ) {
						try{
							$id = $this->import( $post_id, $_REQUEST['entity_preset_name']);
							if(!empty($id)){ array_push($ids, $id); }
						}catch(Error $error){
							//wp_die( __('Error importing as Article.') );
						}
						$imported++;
					}
					
					// build the redirect url
					$sendback = add_query_arg( array('imported' => $imported, 'ids' => join(',', $ids)), "" );
				
					break;
				default: return;
			}
			
			wp_redirect($sendback);
			exit();
		}
		 
		public function bulk_import_notice() {
			global $post_type, $pagenow;
			if($pagenow == 'edit.php' && $post_type != DPSFA_Article_Slug && isset($_REQUEST['imported'])) {
				$message = number_format_i18n( $_REQUEST['imported'] ) . " $post_type(s) imported into Digital Publishing Tools.";
				echo "<div class=\"updated\"><p>{$message}</p></div>";
			}
		}
		

    } // END CMS
}
