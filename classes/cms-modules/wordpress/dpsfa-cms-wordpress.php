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
	    	if(DPSFA_DEBUGMODE) log_message("CMS: Initializing WordPress CMS Module.");
			
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
    		
    		// Register Issue (folio) Posttype
    		//$Issue = new CMS_Issue();
    		//$Issue->registerHookCallbacks();
    		
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
	        //$this->setImageSizes();
	        //$this->loadResources();
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
					$templates = $Template->get_templates();
					if(empty($templates) || !file_exists($templates[0]['path'])){
						die("There are no valid templates available.");
					}else{
						return $templates[0]['path'];
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
		public function get_cms_entity($entityType){
			if($entityType == 'article'){
				return new CMS_Article();
			}else if($entityType == 'collection'){
				return new CMS_Collection();
			}else if($entityType == 'folio'){
				return new CMS_Folio();
			}else{
				die("CAN'T FIND ENTITY TYPE: $entityType");
			}
		}
		
		public function get_entity($entity){
			$cmsEntity = $this->get_cms_entity($entity->entityType);
			return $cmsEntity->get($entity->id);
		}
		
		public function get_entity_data($entity){
			$cmsEntity = $this->get_cms_entity($entity->entityType);
			$data = $cmsEntity->get_data($entity->id);
			return is_array($data) ? $data : array();
		}
		
		public function entity_list($entityType, $filter){
			$cmsEntity = $this->get_cms_entity($entityType);
			return $cmsEntity->get_list($filter);
		}
		
		public function create_entity($entity){
			$cmsEntity = $this->get_cms_entity($entity->entityType);
			$cmsEntity->create($entity);
		}
		
		public function save_entity($entity){
			$cmsEntity = $this->get_cms_entity($entity->entityType);
			$cmsEntity->save($entity);
		}
		
		public function save_field($entity, $field){
			$cmsEntity = $this->get_cms_entity($entity->entityType);
			$cmsEntity->save_field($entity, $field, $entity->$field);
		}
		
		public function delete_entity($entity){
			$cmsEntity = $this->get_cms_entity($entity->entityType);
			$cmsEntity->delete($entity);
		}
		
		public function update_entity($entity){
			$cmsEntity = $this->get_cms_entity($entity->entityType);
			$cmsEntity->update($entity);
		}
		
		public function handle_file_upload($entity, $FILE){
			return media_handle_upload(key($FILE), $entity->id, array(), array( 'test_form' => false ));
		}
		
		public function add_entity_content($entity, $type, $attachment_id){
			if(!is_wp_error($attachment_id)){
				$attachmentThumb = wp_get_attachment_image_src($attachment_id, array(500,500));
				$entity->contents[$type] = array(
					'id' => $attachment_id,
					'thumbnail' => $attachmentThumb[0],
					'original' => wp_get_attachment_url($attachment_id),
					'path' => get_attached_file($attachment_id)
				);
				$entity->$type = $entity->contents[$type]['original'];
				$typeId = $type . "Id";
				$entity->$typeId = $attachment_id;
				$entity->save();
				return $attachment_id;
			}else{
				return FALSE;
			}
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
                        
            // Register Devices options
            add_option(DPSFA_PREFIX . 'devices', array());
            
            // Register Publications options
            add_option(DPSFA_PREFIX . 'publications', array());

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
				update_option( DPSFA_PREFIX . $key, $value );
			}
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
				if($fileParts['extension'] == "php"){
					array_push($templates, array(
						"name" => $file, 
						"path" => $directory . "/" . $file, 
						"modified" => date("F d Y H:i:s", filemtime($directory . "/" . $file)),
						"type" => "Custom"));
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
		public function sync_from_origin($entity){
			
			// Get Post to Import
			if(!empty($entity->origin)){
				$id = $entity->origin;

				$post = get_post( $id );
				
				$toUpdate = array(
					'ID'           	 => $entity->id,
					'comment_status' => $post->comment_status,
					'ping_status'    => $post->ping_status,
					'post_author'    => $post->post_author,
					'post_content'   => $post->post_content,
					'post_excerpt'   => $post->post_excerpt,
					'post_password'  => $post->post_password,
					'post_status'    => 'publish',
					'post_title'     => $post->post_title,
					'to_ping'        => $post->to_ping,
					'menu_order'     => $post->menu_order
				);
				// Update the post into the database
				wp_update_post( $toUpdate );
				
				$customFields = get_post_meta( $id );
				foreach( $customFields as $key => $value ){
					foreach($value as $data){
						add_post_meta($entity->id, $key, $data);
					}
				}
				
				// Bring over all taxonomies as internal keywords
				$postType = get_post_type_object( $post->post_type );
				$internalKeywords = array(
					$post->post_type,
					$postType->labels->singular_name,
					$postType->labels->name
				);
								
				$taxonomy_names = get_object_taxonomies( $post->post_type );
				foreach($taxonomy_names as $taxonomy){
					$terms = get_the_terms( $post->ID, $taxonomy );
					if($terms){
						foreach($terms as $term){
							array_push($internalKeywords, $term->name);
							array_push($internalKeywords, $term->slug);
						}
					}
				}
				
				// Remove any duplicates with the same string just different cases
				$internalKeywords = array_intersect_key(
			        $internalKeywords,
			        array_unique(array_map("StrToLower",$internalKeywords))
			    );
				
				$entity->title = $post->post_title;
				$entity->abstract = $post->post_excerpt;
				$entity->socialShareUrl = get_permalink($id);
				$entity->url = get_permalink($id);
				$entity->articleText = $post->post_excerpt;
				$entity->internalKeywords = array_values($internalKeywords);

				$entity->save();

				// Duplciate taxonomies
				$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
				foreach ($taxonomies as $taxonomy) {
					$post_terms = wp_get_object_terms($id, $taxonomy, array('fields' => 'slugs'));
					wp_set_object_terms($entity->id, $post_terms, $taxonomy, false);
				}
				
				$postThumbID = get_post_thumbnail_id($id);
				$this->add_entity_content($entity, 'thumbnail', $postThumbID);
			}
		}
				
		public function import($id, $entity = ""){

			// Get Post to Import
			$post = get_post( $id );
			
			// Create New Entity
			if(is_object($entity)){
				$entityType = $entity->entityType;
			}else{
				$entityType = $entity;
				$class = "DPSFolioAuthor\\".ucwords($entityType);
				$entity = new $class();
				$entity->entityName = sanitize_title($post->post_title, "untitled");
				$entity->origin = $id;
			}

			// Bring over all taxonomies as internal keywords
			$postType = get_post_type_object( $post->post_type );
			$internalKeywords = array(
				$post->post_type,
				$postType->labels->singular_name,
				$postType->labels->name
			);
		    
			$taxonomy_names = get_object_taxonomies( $post->post_type );
			foreach($taxonomy_names as $taxonomy){
				$terms = get_the_terms( $id, $taxonomy );
				if($terms) {
					foreach($terms as $term){
						array_push($internalKeywords, $term->name);
					}
				}
			}
			
			// Remove any duplicates with the same string just different cases
			$internalKeywords = array_intersect_key(
		        $internalKeywords,
		        array_unique(array_map("StrToLower",$internalKeywords))
		    );
			
			$entity->title = $post->post_title;
			$entity->abstract = $post->post_excerpt;
			$entity->socialShareUrl = get_permalink($id);
			$entity->url = get_permalink($id);
			$entity->articleText = $post->post_excerpt;
			$entity->internalKeywords = $internalKeywords;
			
			$entity->save();
			
			$toUpdate = array(
				'ID'           	 => $entity->id,
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $post->post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_password'  => $post->post_password,
				'post_status'    => 'publish',
				'post_title'     => $post->post_title,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);
			
			// Update the post into the database
			wp_update_post( $toUpdate );
						
			$customFields = get_post_meta( $id );
			foreach( $customFields as $key => $value ){
				foreach($value as $data){
					add_post_meta($entity->id, $key, $data);
				}
			}
			
			// Duplciate taxonomies
			$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
			foreach ($taxonomies as $taxonomy) {
				$post_terms = wp_get_object_terms($id, $taxonomy, array('fields' => 'slugs'));
				wp_set_object_terms($entity->id, $post_terms, $taxonomy, false);
			}
						
			// If Article, set featured image as thumbnail
			if($entityType == "article"){
				$postThumbID = get_post_thumbnail_id($id);
				$this->add_entity_content($entity, 'thumbnail', $postThumbID);
			}
			
			// TODO:
/*
			$attachmentThumb = wp_get_attachment_image_src($postThumbID, array(500,500));
			$contents = array();
			
			$upload_dir = wp_upload_dir();
			$filename = basename( get_attached_file( $postThumbID ) ); // Just the file name
			$metadata = wp_get_attachment_metadata( $postThumbID );
			$uploadDatePath =  str_replace($filename, "", $metadata["file"]);
			$renditionName = $metadata["sizes"]["publish-thumb-recipe"]["file"];
			// TODO REPLACE PUBLISH THUMB WITH ONE FROM SETTINGS
			
			$contents["thumbnail"] = array(
				'id' => $postThumbID,
				'thumbnail' => $attachmentThumb[0],
				'original' => wp_get_attachment_url($postThumbID),
				'path' => $upload_dir["basedir"] . "/" . $uploadDatePath . $renditionName
			);
*/
			
		}
		
		
// ===== CUSTOM WORDPRES FUNCIONALITY functions ===== //
		public function add_bulk_import(){
			global $post_type;
			if($post_type != DPSFA_Article_Slug && $post_type != DPSFA_Folio_Slug && $post_type != DPSFA_Collection_Slug) { ?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('<option>').val('importArticle').text('Import as DPS Article').appendTo("select[name='action']");
				});
			</script><?php
			}
		}
		
		public function bulk_import(){

			$wp_list_table = _get_list_table('WP_Posts_List_Table');
			$action = $wp_list_table->current_action();

			switch($action) {
				case 'importArticle':
					check_admin_referer('bulk-posts');

					$imported = 0;
					$post_ids = $_REQUEST["post"];
					foreach( $post_ids as $post_id ) {
						try{
							$this->import( $post_id, 'article');
						}catch(Error $error){
							//wp_die( __('Error importing as Article.') );
						}
						$imported++;
					}
					
					// build the redirect url
					$sendback = add_query_arg( array('imported' => $imported, 'ids' => join(',', $post_ids) ), $sendback );
				
					break;
				default: return;
			}
			
			// 4. Redirect client
			wp_redirect($sendback);
			
			exit();
		}
		 
		public function bulk_import_notice() {
			global $post_type, $pagenow;
			if($pagenow == 'edit.php' && $post_type != DPSFA_Article_Slug && $post_type != DPSFA_Folio_Slug && $post_type != DPSFA_Collection_Slug &&
				isset($_REQUEST['imported']) && (int) $_REQUEST['imported']) {
				$message = number_format_i18n( $_REQUEST['imported'] ) . " $post_type(s) imported as DPS Article.";
				echo "<div class=\"updated\"><p>{$message}</p></div>";
			}
		}
		

    } // END CMS
}