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
	    public $templates = array();
		
	    public function __construct() { 
		    $this->get_templates();
	    }
		
		public function get_templates(){
			$this->templates = array_merge($this->get_custom_templates(), $this->get_plugin_templates());
		}
		
		public function get_default(){
			$Settings = new Settings();
			foreach($this->templates as $template){
				if($template['path'] == $Settings->defaultTemplate){
					return $template;
				}
			}
			return (count($templates) > 0) ? $templates[0] : false;
		}
		
		public function get_template($name){
			foreach($this->templates as $template){
				if($template['name'] == $name){
					return $template;
				}
			}
			return false;
		}
		
		public function template_exists($templatePath){
			$exists = false;
		    foreach($this->templates as $template){
			    if($template["path"] == $templatePath){
				    $exists = true;
				}
		    }
		    return $exists;
		}
		
		// Look for templates in the CMS 
		public function get_custom_templates(){
			$CMS = new CMS();
			return $CMS->get_custom_templates($this->templateFolder);
		}
		
		// Look for templates in the plugin folder
		public function get_plugin_templates(){
			$templates = array();
			$directory = DPSFA_DIR . "/" . $this->templateFolder;
			$files = file_exists($directory) ? @scandir($directory) : array();
			foreach($files as $file){
				if(strlen($file) > 4){
					$fileParts = pathinfo($file);
					if( isset($fileParts['extension']) && $fileParts['extension'] == "php"){
						array_push($templates, array(
							"name" => $file, 
							"path" => $directory . "/" . $file, 
							"modified" => date("F d Y H:i:s", filemtime($directory . "/" . $file)),
							"location" => "Plugin"
						));
					}
				}
			}
			
			return $templates;
		}
					        
    } // END class Templates 
}