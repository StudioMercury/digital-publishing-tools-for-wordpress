<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Content
 * Description: This class contains content specific parameters and functions.
 */
 
namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Content')) { 
    
    class Content extends Entity{
	    
	    /* Entity Specific */
	    public $title = ''; // max 300 chars (Allow spaces)
	    public $shortTitle = ''; // max 200 chars (Allow spaces)
	    public $abstract = ''; // max 1000 chars
	    public $shortAbstract = ''; // max 400 chars
	    public $keywords = array(); // max 40 chars (allow spaces)
	    public $internalKeywords = array(); // max 40 chars (allow spaces)
	    public $productIds = array(); //array of strings (product IDs) Each ID /^(?!adobe\.publish\.free)[a-zA-Z0-9][a-zA-Z0-9._]{0,99}$/
	    public $department = ''; // max 50 characters
	    public $category = ''; // max 40 chars (allow spaces)
	    public $importance = 'normal'; // low, normal, high
	    public $socialShareUrl = ''; // max 2048
	    public $availabilityDate = ''; // date
	    public $collections = array(); // array of collection URLs 
	    public $contentSize = "";

	    /* CMS Specific */
	    public $body = ''; // body of cms entity
	    public $cmsPreview = ''; // CMS preview url
		public $device = ''; // if this entity is associated with a device (rendition)
	    public $socialSharing = ''; // media ID associated with social share image
	    public $thumbnail = ''; // media ID associated with thumbnail image

	    public function __construct($id = 0) {
			parent::__construct($id);
			
			// Contents
			$this->contents['thumbnail'] = 'images/thumbnail';
			$this->contents['socialSharing'] = 'images/socialSharing' ;
			
			// Internal
			array_push($this->readOnly,
				'contentSize'
			);
			
			array_push($this->internal,
				'thumbnail',
				'socialSharing',
				'device',
				'cmsPreview',
				'body'
			);
		}
		
		public function verify() {
			$this->thumbnail = is_numeric($this->thumbnail) ?  (int)$this->thumbnail : null;
			$this->socialSharing = is_numeric($this->socialSharing) ? (int)$this->socialSharing : null;
			$this->title = !empty($this->title) ? substr( (String)$this->title, 0, 300 ) : null;
			$this->shortTitle = !empty($this->shortTitle) ? substr( (String)$this->shortTitle, 0, 200 ) : null;
			$this->abstract = !empty($this->abstract) ? substr( $this->abstract, 0, 1000 ) : null;
			$this->shortAbstract = !empty($this->shortAbstract) ? substr( (String)$this->shortAbstract, 0, 400 ) : null;
			$this->department = !empty($this->department) ? substr( (String)$this->department, 0, 50 ) : null;
			$this->category = !empty($this->category) ? substr( (String)$this->category, 0, 40 ) : null;
			$this->importance = in_array(strtolower($this->importance), array('low','normal','high')) ? (String)$this->importance : 'normal';
			$this->internalKeywords = is_array($this->internalKeywords) ? array_values(array_intersect_key( $this->internalKeywords, array_unique(array_map('strtolower',$this->internalKeywords)) )) : array();
			$this->keywords = is_array($this->keywords) ? array_values(array_intersect_key( $this->keywords, array_unique(array_map('strtolower',$this->keywords)) )) : array();
			parent::verify();
		}
		
		public function push_contents(){
			parent::push_contents();
		}
		
		public function push_content($content){
		    parent::push_content( $content );
		}
						        
    } // END class Content 
}