<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Credentials
 * Description: This class contains credential specific parameters and functions.
 */
 
namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Credentials')) { 
    
    class Credentials {

	    // Credentials
	    private $client_id; // com format string ie: com.adobe.digitalpublish
	    private $client_secret;
	    
	    // Parameters
	    private $access_token;
	    private $client_request_id;
	    private $client_session_id; // CHANGES EVERY LOGIN
	    private $client_version = DPS_API_CLIENT_VERSION;
	    private $publication_path;
	    
	    // Endpoints
	    private $authentication_endpoint = DPS_API_AUTHENTICATION_END;
	    private $authorization_endpoint = DPS_API_AUTHORIZATION_END;
	    private $ingestion_endpoint = DPS_API_INGESTION_END;
	    private $producer_endpoint = DPS_API_PRODUCER_END;
	    
	    public function __construct($credentials, $parameters, $endpoints, $name) {
			parent::__construct($credentials, $parameters, $endpoints);
			$this->entity_name = $name;
			$this->entity_type = 'article';
			
			/*
			$client_request_id	
			- On install create unique ID inside WP for the install: http://php.net/manual/en/function.uniqid.php
			
			$client_session_id
			- Session ID is new per login / per user
			
			Allow endpoints to be overidden 
			
			// GRAB THE FOLLOWING FROM WP SETTINGS
			private $client_id; // com format string ie: com.adobe.digitalpublish
			private $client_secret; // 
			
				
			*/
		}
			        
    } // END class Credentials 
}