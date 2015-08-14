<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Templates
 * Description: This class handles templates for the plugin.
 */
 
namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Templates')) { 
    
    class Templates {
	    
	    private $templateFolder = "publish-templates";
		
	    public function __construct() { }
		
		public function get_templates(){
			$customTemplates = $this->get_custom_templates();
			return !empty($customTemplates) ? $customTemplates : $this->get_plugin_templates();
		}
		
		public function get_default(){
			$Settings = new Settings();
			$templates = $this->get_templates();
			foreach($templates as $template){
				if($template['path'] == $Settings->defaultTemplate){
					return $template;
				}
			}
			return (count($templates) > 0) ? $templates[0] : false;
		}
		
		public function get_template($name){
			$template = $this->get_templates();
			foreach($template as $template){
				if($template['name'] == $name){
					return $template;
				}
			}
			return false;
		}
		
		// Look for templates in the CMS 
		public function get_custom_templates(){
			$CMS = new CMS();
			$templates = $CMS->get_custom_templates($this->templateFolder);
			return $templates;
		}
		
		// Look for templates in the plugin folder
		public function get_plugin_templates(){
			$directory = DPSFA_DIR . "/" . $this->templateFolder;
			$files = scandir($directory);
			$templates = array();
			foreach($files as $file){
				if(strlen($file) > 4){
					$fileParts = pathinfo($file);
					if( isset($fileParts['extension']) && $fileParts['extension'] == "php"){
						array_push($templates, array(
							"name" => $file, 
							"path" => $directory . "/" . $file, 
							"modified" => date("F d Y H:i:s", filemtime($directory . "/" . $file))
						));
					}
				}
			}
			return $templates;
		}
					        
    } // END class Templates 
}