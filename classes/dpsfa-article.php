<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Article
 * Description: This class contains article specific parameters and functions.
 */
 
namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Article')) { 
    
    class Article extends Content{
	    
	    /* Entity Specific */
	    public $author = ''; // max 100 chars (allow spaces)
	    public $authorUrl = ''; // max 2048
	    public $articleText = ''; // max 2000 chars
	    public $isAd = FALSE; // true, false
	    public $adType = 'Static'; // Static, EFT
	    public $adCategory = ''; // max 40 chars (allow spaces)
	    public $advertiser = ''; // max 40 chars (allow spaces)
	    public $accessState = 'metered'; // free, metered, protected
	    public $hideFromBrowsePage = FALSE; // true, false
	    public $isTrustedContent = FALSE; // true, false
	    public $isMigrated = FALSE; // true, false

	    /* CMS Specific */
	    public $template; // Template of article

	    public function __construct($id = 0) {
			// Call parent  constructor
			parent::__construct($id);
			
			// Set entity type to Article
			$this->entityType = 'article';
			
			// Internal
			array_push($this->internal,
				'template'
			);			
		}
		
		public function verify() {
			// Verify Article Attributes
			$this->author = !empty($this->author) ? substr( $this->author, 0, 100 ) : null;
			$this->authorUrl = !empty($this->authorUrl) ? substr( $this->authorUrl, 0, 2048 ) : null;
			$this->authorText = !empty($this->authorText) ? substr( $this->authorText, 0, 2000 ) : null;
			$this->isAd = filter_var($this->isAd, FILTER_VALIDATE_BOOLEAN);
			$this->adType = ($this->isAd) ? (in_array($this->adType, array('static','eft')) ? $this->adType : 'static') : null;
			$this->adCategory = ($this->isAd) ? (!empty($this->adCategory) ? substr( $this->adCategory, 0, 40 ) : null) : null;
			$this->advertiser = ($this->isAd) ? (!empty($this->advertiser) ? substr( $this->advertiser, 0, 40 ): null) : null;
			$this->accessState = in_array(strtolower($this->accessState), array('free', 'metered', 'protected')) ? $this->accessState : 'metered';
			$this->hideFromBrowsePage = filter_var($this->hideFromBrowsePage, FILTER_VALIDATE_BOOLEAN);
			$this->isTrustedContent = filter_var($this->isTrustedContent, FILTER_VALIDATE_BOOLEAN);
			$this->isMigrated = filter_var($this->isMigrated, FILTER_VALIDATE_BOOLEAN);
			
			// TEMPLATE
			if(empty($this->template)){
		    	$templates = new Templates();
				$defaultTemplate = $templates->get_default();
				$this->template = $defaultTemplate['path'];
	    	}
	    	
	    	parent::verify();
		}
		
		public function bundle_article(){
			// Bundle article into .article file and return tmp file
			$bundlr = new Bundlr();
			return $bundlr->bundle($this);
		}
		
		public function push_article(){
			// Push article to adobe's cloud
			$adobe = new Adobe();
			$adobe->upload_article_folio($this, $this->bundle_article());
		}
		
		/* PUSH SPECIFIC CONTENT FOR ENTITY */
	    public function push_content($content){
		    switch ($content) {
			    case 'articleFolio':
			    	$this->push_article(); // push article
			    	break;
			    default:
			    	parent::push_content($content); // push rest of contents
			    	break;
			}
		}
		
		/* PUSH ALL CONTENTS FOR ENTITY */
		public function push_contents(){
		    // Push other contents
			parent::push_contents(); // push rest of contents
		}
			        
    } // END class Article 
}