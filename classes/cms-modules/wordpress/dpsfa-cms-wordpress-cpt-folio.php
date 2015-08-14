<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : CMS_Issue
 * Description: This class contains issue specific parameters and functions.
 */
 
namespace DPSFolioAuthor;

if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );
	
if(!class_exists('DPSFolioAuthor\CMS_Issue')) { 	
	class CMS_Issue {
		
		public $posttype;
		const NAME_SINGULAR = "Issue";
		const NAME_PLURAL = "Issues";
		
		public function __construct(){
			$this->posttype = DPSFA_PREFIX . strtolower(CMS_Issue::NAME_SINGULAR);
		}
		
		public function registerHookCallbacks(){
			// on initialization
			add_action( 'init',						array( $this, 'createPostType' ) );

			// on save post
			//add_action( 'save_post',				array( $this, 'savePost' ), 10, 2 );
			
			// set the form post type
    		//add_action('post_edit_form_tag',        array( $this, 'setFormType' ));
    		
    		// format rendition tabs
            //add_action( 'edit_form_after_title',    array( $this, 'rendition_tabs' ) );
            
            // add meta boxes			
			//add_action( 'add_meta_boxes', array($this, 'addMetaBoxes'), 10, 2 );
            
            // remove unnecessary metaboxes
			//add_action( 'admin_head', array($this, 'removeMetaBoxes') );
		}
		
		public function createPostType(){
			if( did_action( 'init' ) !== 1 )
				return;
			if( !post_type_exists( $this->posttype ) ){
				if(DPSFA_DEBUGMODE) log_message("CMS_Issue: Registering post type: " . $this->posttype); 
				register_post_type( $this->posttype, $this->getPostTypeParams() );
			}
		}
	
		public function getPostTypeParams(){
			$labels = array(
				'name'					=> CMS_Issue::NAME_PLURAL,
				'singular_name'			=> CMS_Issue::NAME_SINGULAR,
				'menu_name'				=> CMS_Issue::NAME_PLURAL,
				'add_new'				=> 'Add New ' . CMS_Issue::NAME_SINGULAR,
				'add_new_item'			=> 'Add New '. CMS_Issue::NAME_SINGULAR,
				'edit'					=> 'Edit ' . CMS_Issue::NAME_PLURAL,
				'edit_item'				=> 'Edit '. CMS_Issue::NAME_SINGULAR,
				'new_item'				=> 'New '. CMS_Issue::NAME_SINGULAR,
				'view'					=> 'View '. CMS_Issue::NAME_PLURAL,
				'view_item'				=> 'View '. CMS_Issue::NAME_SINGULAR,
				'search_items'			=> 'Search '. CMS_Issue::NAME_PLURAL,
				'not_found'				=> 'No '. CMS_Issue::NAME_PLURAL . ' found',
				'not_found_in_trash'	=> 'No '. CMS_Issue::NAME_PLURAL . ' found in Trash',
				'parent'				=> 'Parent '. CMS_Issue::NAME_SINGULAR
			);

			$postTypeParams = array(
				'labels'				=> $labels,
				'description'			=> CMS_Issue::NAME_SINGULAR . ' post type for the ' . DPSFA_NAME . ' plugin',
				'show_ui' 				=> true,
				'singular_label'		=> CMS_Issue::NAME_SINGULAR,
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
				'supports'				=> array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'excerpt', 'page-attributes' )
			);

			return apply_filters( DPSFA_PREFIX . 'post-type-params', $postTypeParams );
		}

		public static function removeMetaBoxes(){
            //remove_meta_box( 'postimagediv', $this->posttype, 'side' );
            remove_meta_box( 'submitdiv', $this->posttype, 'side' ); // remove submit button
            remove_meta_box( 'slugdiv', $this->posttype, 'normal'); // remove slug
            remove_meta_box( 'authordiv', $this->posttype, 'normal' ); // remove author
            if(!DPSFA_DEBUGMODE) remove_meta_box( 'titlediv', $this->posttype, 'normal' ); // remove title
            if(!DPSFA_DEBUGMODE) remove_post_type_support( $this->posttype, 'title'); // remove title
		}

		
	} // end CMS_Issue
}
?>