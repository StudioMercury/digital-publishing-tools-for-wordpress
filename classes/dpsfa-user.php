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
        
        public $id;
        public $username;
	    public $role;
	    public $settings; // Settings override for specific user
	    
        public function __construct(){ }
        
        // Fill the array with this user's roles
        private loadRoles(){
	        
        }
        
        public function has_permission($permission){
	        
        }	
        
        public function get_settings(){
	        
        }

    } // END class User
    
    class Role {
	    protected $permissions;
	    // Default permissions list
	    protected $permissionList = array(
		    "article" => array(
			    "create" => 1,
			    "edit" => 1,
			    "delete" => 1,
			    "public" => 1,
		    ),	    
		    "settings_edit" => 1,
		    "publications" => array() // list of publication IDs
		    //"templates" => array() // list of template IDs
		    //"devices" => array() // list of template IDs
	    )
	    
        public function __construct(){
	    	$this->permissions = array();
	    }
        
        public function get_role_permissions($roleId){
	        
        }
        
        public function has_permission($permission){
	        return isset($this->permissions[$permission]);
        }

    } // END class Role
}


