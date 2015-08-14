<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Settings
 * Description: This class contains settings specific parameters and functions.
 */
 
namespace DPSFolioAuthor;

if(!class_exists('DPSFolioAuthor\User')) { 

	class User {
                		
	    public $nickname;
	    public $username; 
	    public $password;
	    public $authtoken;
	    public $permissions;
	    
        public function __construct(){ }		

    } // END class User
}
