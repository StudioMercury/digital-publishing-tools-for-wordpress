<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Error Logging
 * Description: We don't want PHP errors display before we send the ajax request (which will result in an improper JSON feed)
 * Capture all PHP errors and display them in the AJAX response (if possible)
 */


namespace DPSFolioAuthor;
 
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\ErrorLogging')) { 
    
    class ErrorLogging {

	    private $errors = array();
	    
	    public function __construct($hideErrors = true){ 
			set_error_handler( array($this, 'errorHandler') );
			if($hideErrors){
				ini_set( "display_errors", "off" ); // Turn off showing of errors
			}
	    }
		
		public function errorHandler($errno, $errstr, $errfile, $errline){
			array_push($this->errors, array(
				'errno' => $errno, 
				'errstr' => $errstr, 
				'errfile' => $errfile, 
				'errline' => $errline
			));
		}
				
		public function hasErrors(){
			return empty($this->errors) ? FALSE : TRUE;
		}
		
		public function getErrors(){
			return $this->errors;
		}
				        
    } // END class ErrorLogging 
    
}