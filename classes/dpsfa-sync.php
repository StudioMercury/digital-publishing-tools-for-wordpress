<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Sync
 * Description: This class contains functions for handling entity syncing.
 */
 
namespace DPSFolioAuthor;

if(!class_exists('DPSFolioAuthor\Sync')) { 

	class Sync {
	    
        public function __construct(){ }	
        
        public function import($id, $entityType){
	        $CMS = new CMS();
	        $CMS->import($id, $entityType);
        }
        
        public function sync_status(){
	        
        }
        
        public function sync_from_origin(){
	        
        }
        
        public function sync($entity){
	        
        }

    } // END class Sync
}
