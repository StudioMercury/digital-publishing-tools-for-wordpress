<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Error
 * Description: This class extends the built in PHP error handling.
 */


namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Error')) { 
    
    class Error extends \Exception {

	    private $_raw;
	    private $_options;
	    private $_title;
	
	    public function getOptions() { 
		    return $this->_options; 
		}
		
		public function setOptions($options = array()){
		    $this->_options = $options;
		}
	    
	    public function getRaw(){
		    return $this->_raw;
	    }
	    
	    public function setRaw($raw = array()){
		    $this->_raw = $raw;
	    }
	    
	    public function getTitle(){
		    return $this->_title;
	    }
	    
	    public function setTitle($title = ""){
		    $this->_title = $title;
	    }
	    
	    public function setMessage($message = ""){
		    $this->message = $message;
	    }
			        
    } // END class Error 
    
}