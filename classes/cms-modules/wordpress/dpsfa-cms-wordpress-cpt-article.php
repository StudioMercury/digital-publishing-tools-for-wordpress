<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : CMS_Article
 * Description: This class contains settings specific parameters and functions.
 */
 
namespace DPSFolioAuthor;

if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );
	
if(!class_exists('DPSFolioAuthor\CMS_Article')) { 	
	class CMS_Article {
				
		public function __construct(){ }
		
		public function registerHookCallbacks(){
			// Initialization
			add_action( 'init',						array( $this, 'createPostType' ) );
			
			// Add metaboxes
			add_action( 'add_meta_boxes', 			array( $this, 'add_meta_box_template' ) );
			//add_action( 'add_meta_boxes', 			array( $this, 'add_meta_box_metadata' ) );
			
			add_action( 'save_post', 				array( $this, 'meta_box_save' ) );
		}
		
		public function add_meta_box_template(){
			add_meta_box( 'article-template', 'Article Template', array($this,'meta_box_template'), DPSFA_Article_Slug, 'side', 'high' );
		}
		
		public function add_meta_box_metadata(){
			add_meta_box( 'article-metadata', 'Article Publish Metadata', array($this,'meta_box_metadata'), DPSFA_Article_Slug, 'normal', 'high' );
		}
		
		public function meta_box_template($post){
			$articleTemplate = get_post_meta( $post->ID, DPSFA_Article_Slug."_template", true );
			$TemplateService = new Templates();
			$templates = $TemplateService->get_templates();
			?>
		    <p>
		        <label for="my_meta_box_select">Template</label>
		        <select name="<?php echo DPSFA_Article_Slug."_template";?>" id="<?php echo DPSFA_Article_Slug."template";?>">
		            <?php foreach($templates as $template):?>
		            <option value="<?php echo $template['path'];?>" <?php selected( $articleTemplate, $template['path'] ); ?>>
		            	<?php echo $template['type'];?>: <?php echo $template['name']; ?>
		            </option>
		            <?php endforeach; ?>
		        </select>
		        <?php wp_nonce_field( 'article_meta_template', 'article_meta_template' ); ?>
		    </p>
		    <?php    
		}
		
		public function meta_box_save($post_id){
			// Bail if we're doing an auto save
		    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		     
		    // if our nonce isn't there, or we can't verify it, bail
		    if( !isset( $_POST['article_meta_template'] ) || !wp_verify_nonce( $_POST['article_meta_template'], 'article_meta_template' ) ) return;
		     
		    // if our current user can't edit this post, bail
		    if( !current_user_can( 'edit_post' ) ) return;
		    
		    // Save Template
		    if( isset( $_POST[DPSFA_Article_Slug."_template"] ) ){
				update_post_meta( $post_id, DPSFA_Article_Slug."_template", $_POST[DPSFA_Article_Slug."_template"] );    
		    }
			
			// Save Metadata Fields
			if( isset( $_POST[DPSFA_Article_Slug."_metadata"] ) ){
				foreach( $_POST[DPSFA_Article_Slug."metadata"] as $key => $value ){
					update_post_meta( $post_id, DPSFA_Article_Slug.$key, $value );    
				}
			}
		}
		
		public function meta_box_metadata(){
			
		}
		
		public function get_list($filter){
			$articles = array();
			
			$args = array(
				'posts_per_page' => -1,
				'post_type' => DPSFA_Article_Slug
			);
						
			// TODO: FIGURE OUT FILTERS
			$query = new \WP_Query($args);
			while($query->have_posts()){
				$query->the_post();
				array_push($articles, new Article( array('id' => $query->post->ID) ));
			}

			return $articles;
		}
		
		public function get($id){
			$article = new Article($this->get_data($id));
			return $article;
		}
		
		public function get_data($id){
			$post = get_post($id);
			$meta = (array)get_post_meta($id);
			
			$data = array();
			foreach($meta as $key => $value){
				if((@unserialize($value[0]) !== false)){
					$data[str_replace(DPSFA_Article_Slug . '_', '', $key)] = unserialize($value[0]);
				}else{
					$data[str_replace(DPSFA_Article_Slug . '_', '', $key)] = $value[0];
				}
			}
			
			$data["id"] = $id;
			$data["body"] = $post->post_content;
			$data["local_modified"] = $post->post_modified;
			$data["cmsPreview"] = get_permalink($id);
			$data["editUrl"] = get_edit_post_link($id, '');
			return $data;
		}
		
		public function create($article){
			$postArgs = array(
				'post_title' => $article->entityName,
				'post_type' => DPSFA_Article_Slug,
				'post_content' => '',
				'post_excerpt' => '',
				'post_status' => 'publish'
			);
			$post = wp_insert_post($postArgs);
			if($post){
				$date = new \DateTime();
				$article->id = $post;
				$article->date_created = $date->getTimestamp();
				$article->save();
			}else{
				$error = new Error("Error", 400);
				$error->setTitle('Could not create Article');
				$error->setMessage('Wordpress could not create the article');
				$error->setRaw($post);
			}
		}
		
		public function save($article){
			// If article doesn't exist: 
			if(empty($article->id)){
				$this->create($article);
			}else{
				// Update existing article
				foreach( get_object_vars($article) as $key => $value ){
					if( $value === null || $value == ""){
						$wpResponse = delete_post_meta($article->id, DPSFA_Article_Slug . '_' . $key);
					}else{
						if((@unserialize($value) !== false)){
							$wpResponse = update_post_meta($article->id, DPSFA_Article_Slug . '_' . $key, unserialize($value));
						}else{
							$wpResponse = update_post_meta($article->id, DPSFA_Article_Slug . '_' . $key, $value);
						}
					}
				}
			}
		}
		
		public function save_field($article, $key, $value){
			if( $value === null || $value == ""){
				delete_post_meta($article->id, DPSFA_Article_Slug . '_' . $key);
			}else{
				update_post_meta($article->id, DPSFA_Article_Slug . '_' . $key, $value);
			}
		}
		
		public function get_field($article, $key){
			$value = get_post_meta($article->id, DPSFA_Article_Slug . '_' . $key, true);
			if((@unserialize($value) !== false)){
				return unserialize($value);
			}else{
				return $value;
			}
		}
		
		public function delete($article){
			return wp_delete_post($article->id, true);
		}
		
		public function createPostType(){
			if( did_action( 'init' ) !== 1 )
				return;
			if( !post_type_exists( DPSFA_Article_Slug ) ){
				if(DPSFA_DEBUGMODE) log_message("CMS_Article: Registering post type: " . DPSFA_Article_Slug); 
				register_post_type( DPSFA_Article_Slug, $this->getPostTypeParams() );
			}
		}
	
		public function getPostTypeParams(){
			$labels = array(
				'name'					=> DPSFA_Article_Names,
				'singular_name'			=> DPSFA_Article_Name,
				'menu_name'				=> DPSFA_Article_Names,
				'add_new'				=> 'Add New ' . DPSFA_Article_Name,
				'add_new_item'			=> 'Add New '. DPSFA_Article_Name,
				'edit'					=> 'Edit ' . DPSFA_Article_Names,
				'edit_item'				=> 'Edit '. DPSFA_Article_Name,
				'new_item'				=> 'New '. DPSFA_Article_Name,
				'view'					=> 'View '. DPSFA_Article_Names,
				'view_item'				=> 'View '. DPSFA_Article_Name,
				'search_items'			=> 'Search '. DPSFA_Article_Names,
				'not_found'				=> 'No '. DPSFA_Article_Names . ' found',
				'not_found_in_trash'	=> 'No '. DPSFA_Article_Names . ' found in Trash',
				'parent'				=> 'Parent '. DPSFA_Article_Name
			);

			$postTypeParams = array(
				'labels'				=> $labels,
				'description'			=> DPSFA_Article_Name . ' post type for the ' . DPSFA_NAME . ' plugin',
				'show_ui' 				=> true,
				'singular_label'		=> DPSFA_Article_Name,
				'public'				=> true,
				'show_in_menu'          => DPSFA_SLUG,
				'publicly_queryable' 	=> true,
				'exclude_from_search' 	=> false,
				'hierarchical'			=> true,
				'can_export'			=> true,
				'capability_type'		=> 'post',
				'show_in_admin_bar'		=> true,
				'show_in_nav_menus' 	=> true,
				'rewrite' 				=> false,
				'query_var' 			=> true,
				'has_archive' 			=> false,
				'taxonomies' 			=> array('post_tag', 'category'),
				'supports'				=> array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'excerpt', 'page-attributes' )
			);

			return apply_filters( DPSFA_PREFIX . 'post-type-params', $postTypeParams );
		}

		public static function addMetaBoxes($post_type, $post){

			if( did_action( 'add_meta_boxes' ) !== 1 )
				return;

            if($post_type !== DPSFA_Article_Slug)
                return;
                
            $CMS = DPSFolioAuthor_CMS::getInstance();
            $article = $CMS->get_article_by_id($post->ID);
			
			// Meta box for non-renditions (parents)
            if(!$article->is_rendition()){
                add_meta_box(
    				DPSFA_PREFIX . 'article-meta',
    				'Article Metadata',
    				array(__CLASS__, 'markupMetaBoxes'),
    				DPSFA_Article_Slug,
    				'side',
    				'high',
					array("article" => $article)
    			);
            }
            
            // Meta box for renditions list
            if( $article->is_rendition() ){
                add_meta_box(
    				DPSFA_PREFIX . 'article-status',
    				'Renditions',
    				array(__CLASS__, 'markupMetaBoxes'),
    				DPSFA_Article_Slug,
    				'side',
    				'high',
					array("article" => $article)
    			);
            }
            
            // Meta box for Article action		
			add_meta_box(
				DPSFA_PREFIX . 'article-action',
				'Action',
    			array(__CLASS__, 'markupMetaBoxes'),
				DPSFA_Article_Slug,
				'side',
				'high',
				array("article" => $article)
			);
			
			// Meta box for template
			add_meta_box(
				DPSFA_PREFIX . 'article-template',
				'Template',
    			array(__CLASS__, 'markupMetaBoxes'),
				DPSFA_Article_Slug,
				'side',
				'high',
				array("article" => $article)
			);
			
			// Meta box for Article Settings
			add_meta_box(
				DPSFA_PREFIX . 'article-settings',
				'Article Settings',
    			array(__CLASS__, 'markupMetaBoxes'),
				DPSFA_Article_Slug,
				'side',
				'high',
				array("article" => $article)
			);	

		}

		public static function removeMetaBoxes(){
            //remove_meta_box( 'postimagediv', DPSFA_Article_Slug, 'side' );
            remove_meta_box( 'submitdiv', DPSFA_Article_Slug, 'side' ); // remove submit button
            remove_meta_box( 'slugdiv', DPSFA_Article_Slug, 'normal'); // remove slug
            remove_meta_box( 'authordiv', DPSFA_Article_Slug, 'normal' ); // remove author
            if(!DPSFA_DEBUGMODE) remove_meta_box( 'titlediv', DPSFA_Article_Slug, 'normal' ); // remove title
            if(!DPSFA_DEBUGMODE) remove_post_type_support( DPSFA_Article_Slug, 'title'); // remove title
		}

		public static function markupMetaBoxes( $post, $args ){
			$article = $args['args']["article"];
            $fieldSlug = DPSFA_Article_Slug;
			$view = DPSFA_DIR . "/views/admin/meta-boxes/" . $args[ 'id' ] . ".php";
			if( is_file( $view ) )
				require_once( $view );
			else
				throw new Exception( __METHOD__ . " error: ". $view ." doesn't exist." );
		}

		public static function setFormType(){
            echo ' enctype="multipart/form-data"';
		}
		
	} // end CMS_Article
}
?>