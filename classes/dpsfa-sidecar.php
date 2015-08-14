<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Sidecar
 * Description: This class handles the importing and exporting sidecar files.
 */
 
namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Sidecar')) { 
    
    class Sidecar {
		
	    public function __construct() { }
		
		public function export($toExport = array(), $asFile = false){
			$entities = (!is_array($toExport)) ? array($toExport) : $toExport;
			return (!$asFile) ? $this->build_sidecar($entities) : $this->build_sidecar($entities); // todo, create saving function later for saving the XML
		}
		
		public function import($sidecar){
			// TODO: Port over import functions
		}
		
		private function build_sidecar($entities = array()){
			$xml  = "";
			$xml .= "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\n";
			$xml .= "<sidecar>\n";
			foreach($entities as $entity){
				$properties = array();
				foreach(get_object_vars($entity) as $key => $value){
					if(in_array($key, $this->articleProperties) && !empty($value)){ 
						$properties[$key] = $value; 
					}
				}
			
				$xml .= "\t<entry>\n";
				$xml .= "\t\t<folderName>" . $properties['entityName'] . "</folderName>\n";
				foreach($properties as $key => $value){
					if(is_array($value)){
						$xml .= "\t\t<$key>" . json_encode($value) . "</$key>\n";
					}else{
						$xml .= "\t\t<$key>" . $value . "</$key>\n";
					}
				}
				$xml .= "\t</entry>\n";
			}
			$xml .= "</sidecar>";
			return $xml;
		}
		
		private $articleProperties = array(
		    'author', 
		    'authorUrl', 
		    '$articleText', 
		    'isAd', 
		    'adType', 
		    'adCategory', 
		    'advertiser', 
		    'accessState', 
		    'hideFromBrowsePage', 
		    'isTrustedContent', 
		    'entityType', 
		    'entityName', 
		    'entityId', 
		    'url', 
		    'title', 
		    'shortTitle', 
		    'abstract', 
		    'shortAbstract', 
		    'keywords',  // TODO: tags coming from angular - serialize
		    'internalKeywords', 
		    'department', 
		    'category', 
		    'importance', 
		    'socialShareUrl', 
		    'availabilityDate'
		);
		
					        
    } // END class Sidecar 
}