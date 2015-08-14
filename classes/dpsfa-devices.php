<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Device
 * Description: This class contains device specific parameters and functions.
 */
 
namespace DPSFolioAuthor;

if(!class_exists('DPSFolioAuthor\Device')) { 

	class Device {
                		
	    // Generic
	    public $id;
	    public $name; 
	    public $deviceClass;
	    public $os;
	    public $width;
	    public $height;
		
        public function __construct($id = null){ 
	        if(!empty($id)){
		        $data = $this->get($id);
		        foreach ($data as $key => $val) {
			      $this->$key = $val;
			    }
	        }
        }	
        
        public function get($id){
	        
        }
        
        public function save(){
	        
        }	
        
        public function delete(){
	        
        }
        
        public function update(){
	        
        }
        
        public function get_all(){
	        
        }

    } // END class Device
}
