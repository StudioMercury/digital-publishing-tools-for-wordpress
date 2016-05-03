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
			add_action( 'add_meta_boxes', 			array( $this, 'add_meta_box_template' ) ); // TEMPLATE
			
			// Saving metaboxes
			add_action( 'save_post', 				array( $this, 'meta_box_save' ) );
		}
		
		public function add_meta_box_template(){
			add_meta_box( 'article-template', 'Article Template', array($this,'meta_box_template'), DPSFA_Article_Slug, 'side', 'high' );
		}
		
		public function meta_box_template($post){
			$articleTemplate = get_post_meta( $post->ID, DPSFA_Article_Slug."_template", true );
			$TemplateService = new Templates();
			$templates = $TemplateService->templates;
			?>
		    <p>
		        <label for="my_meta_box_select">Template</label>
		        <select name="<?php echo DPSFA_Article_Slug."_template";?>" id="<?php echo DPSFA_Article_Slug."template";?>">
		            <?php foreach($templates as $template):?>
		            <option value="<?php echo $template['path'];?>" <?php selected( $articleTemplate, $template['path'] ); ?>>
		            	<?php echo $template['name']; ?>
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
		    if( !current_user_can( 'edit_post', $post_id ) ) return;
		    
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
		
		public function createPostType(){
			if( did_action( 'init' ) !== 1 )
				return;
			if( !post_type_exists( DPSFA_Article_Slug ) ){
				log_message("CMS_Article: Registering post type: " . DPSFA_Article_Slug); 
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
				//'show_in_menu'          => DPSFA_SLUG, // For native view
				'show_in_menu'          => false,
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
				'supports'				=> array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'excerpt', 'page-attributes', 'custom-fields', )
			);

			return apply_filters( DPSFA_PREFIX . 'post-type-params', $postTypeParams );
		}
		
	} // end CMS_Article
}
?>