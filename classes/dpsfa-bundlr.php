<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Bundlr
 * Description: This class handles the bundling of entities to submit to Adobe.
 */


namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );


require_once( DPSFA_DIR . '/libs/Mustache/Autoloader.php' );
require_once( DPSFA_DIR . '/libs/simple_html_dom/simple_html_dom.php' );

if(!class_exists('DPSFolioAuthor\Bundlr')) { 
    
    class Bundlr {
	                            
        public function __construct() { }
        
        /*
        * Create a bundle ( zip / folio ) of a given entity
        *
        * @param    object        $entity object
        * @return	string        returns the path of the created bundle
        *
        */
        public function bundle( $entity ){
			if(DPS_API_VERSION > 1){ // If API is Publish bundle an article
				$bundle = $this->bundle_article($entity);
			}else{ // otherwise bundle a folio
				$bundle = $this->bundle_folio($entity);
			}
            
            /* return path of created bundle */
    		return $bundle;
        }
        
        // A function for bundling an Adobe Classic Folio
        private function bundle_folio($entity){
            $collectedFiles = $this->collect_folio_files($entity); 
			$bundle = $this->create_zip( $collectedFiles, ".folio", dirname(__DIR__)."/assets/classic/folio/folioStarter.zip" );
			return $bundle;
        }
        
        // A function for bundling an Adobe Publish Article
        private function bundle_article($entity){
            $collectedFiles = $this->collect_article_files($entity); 
	        $bundle = $this->create_zip( $collectedFiles, ".article", false, $entity->entityName."_FROM_".DPSFA_CMS."-");
			return $bundle;
        }
        
	    /* Bring together all folio files */
        private function collect_folio_files($entity){
            /* FOLIO THUMBS/PREVIEWS */
            $files = array();
            $files["toc.png"] = $this->create_toc_png($entity);
            //$files["scrubber_p.png"] = $this->create_scrubber_image();
            //$files["thumb_p.png"] = $this->create_thumb();
            
			/* COLLECT ADDITIONAL FILES FROM A TEMPLATE (CMS) */
            $files = array_merge($files, $this->get_files_from_template( $entity ));
            
            /* HTML OF ARTICLE */
            $html = $this->get_html_content($entity);                 
            /* COLLECT LINKED IMAGES / MEDIA / CSS / JS FILES */
            $collected = $this->get_linked_assets_from_html($html);
            
            $files = array_merge( $files, $collected["assets"] );
            $files["index.html"] = $this->make_file("index", $this->pretty_html($collected["html"]));

            /* ADD FOLIO SPECIFIC FILES */
            $files["Folio.xml"] = $this->create_folio_xml($entity);
            $files["META-INF/pkgproperties.xml"] = $this->create_meta_pkg_properties($files);
			
			return $files;
        }

	    /* Bring together all article files */
        private function collect_article_files($entity){
			$files = array();

			/* COLLECT ADDITIONAL FILES FROM A TEMPLATE (CMS) */
            $files = $this->get_files_from_template( $entity );
            
            /* HTML OF ARTICLE */
            $html = $this->get_html_content($entity);  
            
            /* COLLECT LINKED IMAGES / MEDIA / CSS / JS FILES */
			$collected = $this->get_linked_assets_from_html($html);
            $files = array_merge( $files, $collected["assets"] );
            $files["index.html"] = $this->make_file("index", (string)$collected["html"]);

			/* CREATE ARTICLE MANIFEST */ 
			$files["manifest.xml"] = $this->make_file("manifest", $this->create_article_manifest($files));
			
            return $files;           
        }
    	
    	private function create_article_manifest($files, $options = array()){
	    	
	    	// generate the manifest XML
	        $manifest = new \SimpleXMLElement('<manifest/>');
	        $manifest->addAttribute('dateModified', date('Y-m-d\TH:i:s\Z'));
	        $manifest->addAttribute('targetViewer', '33.0.0');
	        $manifest->addAttribute('version', '3.0.0');
	        
	        // generate the inner-child: index
	        $index = $manifest->addChild('index');
	        
	        // generate the inner-child: resources
	        $resources = $manifest->addChild('resources');
	        
	        // generate individual resources
			foreach($files as $path => $file ){
		        
				$pathInfo = pathinfo($path);

				if(empty($pathInfo['extension'])){
					continue;
				}else{
		        	$extension = $pathInfo['extension'];
				}
				
				$filename = $pathInfo['basename'];
		        $size = filesize($file);
		        
		        switch ($extension) {
		            case 'css':
		                $type = 'text/css'; break;
		            case 'flv':
		                $type = 'video/x-flv'; break;
		            case 'fvt':
		                $type = 'video/vnd.fvt'; break;
		            case 'f4v':
		                $type = 'video/x-f4v'; break;
		            case 'gif':
		                $type = 'image/gif'; break;
		            case 'html':
		                $type = 'text/html'; break;
		            case 'h261':
		                $type = 'video/h261'; break;
		            case 'h263':
		                $type = 'video/h263'; break;
		            case 'h264':
		                $type = 'video/h264'; break;
		            case 'ico':
		                $type = 'image/x-icon'; break;
		            case 'jpeg':
		                $type = 'image/jpeg'; break;
		            case 'jpg':
		                $type = 'image/jpeg'; break;
		            case 'jpgv':
		                $type = 'video/jpeg'; break;
		            case 'js':
		                $type = 'application/javascript'; break;
		            case 'json':
		                $type = 'application/json'; break;
		            case 'mpeg':
		                $type = 'video/mpeg'; break;
		            case 'mpga':
		                $type = 'audio/mpeg'; break;
		            case 'mp4':
		                $type = 'video/mp4'; break;
		            case 'mp4a':
		                $type = 'audio/mp4'; break;
		            case 'mxml':
		                $type = 'application/xv+xml'; break;
		            case 'm4v':
		                $type = 'video/x-m4v'; break;
		            case 'pdf':
		                $type = 'application/pdf'; break;
		            case 'png':
		                $type = 'image/png'; break;
		            case 'psd':
		                $type = 'image/vnd.adobe.photoshop'; break;
		            case 'svg':
		                $type = 'image/svg+xml'; break;
		            case 'txt':
		                $type = 'text/plain'; break;
		            case 'xml':
		                $type = 'text/xml';
		                break;
		            case 'zip':
		                //error_log(__METHOD__ . '() zip files are not allowed: ' . $filename, 0);
		                return;
		            case '':
		                //error_log(__METHOD__ . '() no file extension: ' . $filename, 0);
		                return;
		            default:
		                $type = 'application/octet-stream'; break;
		        }
		        
		        $contents = file_get_contents($file);
				$md5 = base64_encode(md5($contents, true));
				
												
		        // if in root directory and file is either article.xml or index.html,
		        // store file as <index> tag within the manifest
		        if ( (strtolower($filename) === 'article.xml' || strtolower($filename) === 'index.html')) {
		            $index->addAttribute('type', $type);
		            $index->addAttribute('href', $path);
		        }
		       
		        // store file as <resource> tag within the manifest
		        $resource = $resources->addChild('resource');
		        $resource->addAttribute('type', $type);
		        $resource->addAttribute('href', $path);
		        $resource->addAttribute('length', $size);
		        $resource->addAttribute('md5', $md5);

		    }

	        // remove the <xml?\> tag before storing into the XML file
	        $manifest_raw = $manifest->asXML();
	        $manifest_data = substr($manifest_raw, strpos($manifest_raw, '?>') + 3);
	        	        
	        return $manifest_data; // returns manifest XML as string
    	}
    	
    	private function get_files_from_template( $entity ){
            /* Call filter for getting additional files from a custom template */
            $CMS = new CMS();
            $templateFiles = $CMS->get_template_files($entity);            
            return is_array($templateFiles) ? $templateFiles : array();
    	}
    	
    	// Create Table of Content PNG for an entity
    	private function create_toc_png( $entity ){
            return $this->save_file( "dps-", $entity->thumbnail );
    	}
        
        /* TODO: MAKE THIS DYNAMIC */
    	private function create_scrubber_image(){
    	    // scrubber image is 125x166
            $scrubberImage = tempnam(DPSFA_TMPDIR,"scrubber");
            copy( dirname(__DIR__)."/assets/classic/folio/scrubber_p.png" , $scrubberImage);
            return $scrubberImage;
    	}
    	
        /* TODO: MAKE THIS DYNAMIC */
    	private function create_thumb(){
    	    // thumb_p is full size of device
            $previewThumb = tempnam(DPSFA_TMPDIR,"thumb");
            copy( dirname(__DIR__)."/assets/classic/folio/previewThumbs/thumb_p.png" , $previewThumb);
            return $previewThumb;
    	}
    	
    	private function create_zip( $files, $extension = ".zip", $existingZip = false, $prefix = "dpsarticle"){
	    	$bundle = tempnam(DPSFA_TMPDIR,$prefix);
	    	rename($bundle, $bundle .= $extension );
	    	
            if($existingZip){
           		copy($existingZip, $bundle);
            }
            
            // Create new Zip archive.
            $zip = new \ZipArchive();
            $zip->open( $bundle, \ZipArchive::CREATE );
            
            // Add files one by one
            foreach($files as $filename => $filepath){
                if($filepath){ $zip->addFile( $filepath, $filename ); }
            }
            
            // Close and return the folio
            $zip->close();
            return $bundle;
    	}
    	
    	public function download_zip( $entity, $withSidecar = false, $withContents = true){
	    	// Verify name:
	    	$entity->entityName = empty($entity->entityName) ? time() : $entity->entityName;
            // Collect article files
            if(DPS_API_VERSION > 1){ // If API is Publish bundle an article
				$collectedFiles = $this->collect_article_files($entity);
			}else{ // otherwise bundle a folio
				$collectedFiles = $this->collect_folio_files($entity);
			}
			            
            /* Combine files into zip format */
            $toZip = array();
    		$toZip[$entity->entityName.'.article'] = $this->create_zip( $collectedFiles );
    		
    		/* Add Sidecar */
    		if($withSidecar){
	    		$sidecar = new Sidecar();
    			$toZip['sidecar.xml'] = $this->make_file('sidecar', $sidecar->export($entity));
    		}
    		
    		/* Add Contents */
    		if($withContents){
	    		foreach($entity->contents as $content => $id){
		    		$filename = basename($content);
		    		$toZip["contents/$content/$filename"] = $this->save_file($filename,$content);
	    		}	
    		}
    		
    		$bundle = $this->create_zip( $toZip );
			
    		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
	        header("Content-Type: application/zip");
	        header("Content-Transfer-Encoding: Binary");
	        header("Content-Length: ".filesize($bundle));
	        header("Content-Disposition: attachment; filename=\"".basename($entity->entityName)."_".$entity->entityType.".zip\"");
	       
			readfile($bundle);
			exit;
    	}
    	 	    
    	private function create_folio_xml( $entity ){
    	    // Get article + folio meta and merge together
            $theMeta = array_merge( $folioMeta, $articleMeta );
            
            // Based on metadata set up variables for folio.xml
            $theMeta["paginated"] = ($theMeta["smoothScrolling"] == "Never") ? "true" : "false";
            $theMeta["htmlFileName"] = "index.html";
            $theMeta["folioVersion"] = "2.0.0";
            //$theMeta["folioDescription"] = ($theMeta["folioDescription"] == "") ? "NEEDS DESCRIPTION!" : $theMeta["folioDescription"];
            $theMeta["articleID"] = $article["localID"];
            
            // Determine if there should be a horizontal or vertical layer (or both)
            if($theMeta["folioIntent"] == "LandscapeOnly" ){
                $theMeta["layout"] = "horizontal";
                $theMeta["isHorizontal"] = true;
                $theMeta["isVertical"] = false;
                $theMeta["orientation"] = "landscape";
            }else if($theMeta["folioIntent"] == "PortraitOnly" ){
                $theMeta["layout"] = "vertical";
                $theMeta["isVertical"] = true;
                $theMeta["isHorizontal"] = false;
                $theMeta["orientation"] = "portrait";
            }else{
                $theMeta["isHorizontal"] = true;
                $theMeta["isVertical"] = true;
                $theMeta["layout"] = "both";
                $theMeta["orientation"] = "both";
            }
            
            $theMeta["smoothScrolling"] = strtolower($theMeta["smoothScrolling"]);
            
            // use metadata to contruct the folio.xml file
            if( !class_exists('Mustache_Autoloader') ){ Mustache_Autoloader::register(); }
            $m = new \Mustache_Engine;
            $contents = $m->render(file_get_contents( dirname( __DIR__ ) . "/views/templates/folio-xml.mustache" ), $theMeta );
            
            $file = tempnam(DPSFA_TMPDIR,"dps-");
            $result = file_put_contents($file, $contents);
            return $result ? $file : false;
    	}   	
    
    	private function create_meta_pkg_properties($files){
    	    $metaFiles = array();
    	    foreach( $files as $key => $value ){
    	        $metaFiles[] = array(
    		        "path" => $key,
    		        "date" => date("Y-m-d\TH:i:s\Z")
    		    );
    	    }
    	    
            if( !class_exists('Mustache_Autoloader') ){ Mustache_Autoloader::register(); }
    		$m = new Mustache_Engine;
            $contents = $m->render( file_get_contents( dirname( __DIR__ ) . "/views/templates/pkgproperties.mustache" ), array("file" => $metaFiles) );
    
            $file = tempnam(DPSFA_TMPDIR,"dps-");
            $result = file_put_contents($file, $contents);
            return $result ? $file : false;
    	}
    	
    	private function get_html_content( $entity ){
    		$file = tempnam(DPSFA_TMPDIR,"dps-");
    		$CMS = new CMS();
    		
    		// If entity is associated with a device add it to the URL for the template
	    	$width = !empty($entity->device) ? $entity->device->width : "";
    		
    		// Append folioBuilder attribute in URL
    		$URL = $CMS->get_entity_url($entity);
    		$URL = parse_url($URL, PHP_URL_QUERY) ? $URL . "&folioBuilder=true&width=$width" : $URL . "?folioBuilder=true&width=$width";
    		
            if(empty($URL)){ return ""; }
    	    
    	    $HTML = file_get_contents( $URL );
            return str_get_html($HTML);
    	}
    
    	private function pretty_html( $htmlString = "" ){            
            // Tidy the HTML
    	    if(extension_loaded('tidy')){
    	    	$config = array(
				           	'indent'         => true,
						   	'output-xhtml'   => true,
						   	'wrap'           => 200
				          );
	    	    $tidy = new \tidy;
				$tidy->parseString($htmlString, $config, 'utf8');
				$tidy->cleanRepair();
				return $tidy->value;
    	    }    	    
    	}
        
        private function save_file( $name = "", $url = "" ){
	        $contents = @file_get_contents($url);
    	    $file = $this->make_file($name, $contents);
    	    return $file ? $file : FALSE;
        }
        
        private function make_file( $name = "", $str = "" ){
    	    $file = tempnam(DPSFA_TMPDIR,$name);
    	    file_put_contents($file, $str);
    	    return $file;
        }
		
		/* THIS WILL PARSE THE HTML TO FIND EXTERNAL ASSETS */
		/* ANY ASSETS FOUND WILL BE SAVED / RETURNED AS FILES */
		/* THE HTML WILL ALSO BE UPDATED WITH THESE NEW LINKS */
        private function get_linked_assets_from_html($htmlStr, $toFind = array("image,script,link,video,audio")) {
	        
	        if(empty($htmlStr)){
		        return array(
	                "html" => $html,
	                "assets"  => array()
	            );
	        }
	        
            $html = str_get_html($htmlStr);
            $assets = array();
            
            $images = $html->find('img');
            $mediaSources = $html->find('source');
            
            $styles = $html->find('link');
            $scripts = $html->find('script');
            
            // update image to local assets folder
            foreach($images as $image) {
                if( isset($image->src) && pathinfo($image->src, PATHINFO_EXTENSION)){
	                $path = "resources". $this->get_asset_path($image->src);
	                $assets[$path] = $this->save_file(basename($path), $image->src);
                    $image->src = $path;
                }
            }
            
            // update audio / video source to local assets folder
            foreach($mediaSources as $media) {
                if( isset($media->src) && pathinfo($media->src, PATHINFO_EXTENSION)){
	                $path = "resources". $this->get_asset_path($media->src);
	                $assets[$path] = $this->save_file(basename($path), $media->src);
                    $media->src = $path;
                }
            }
            
            // update css to local assets folder
            foreach($styles as $style) {
                if( isset($style->href) && $style->rel == 'stylesheet' && pathinfo($style->href, PATHINFO_EXTENSION)){
	                $path = "resources". $this->get_asset_path($style->href);
	                $assets[$path] = $this->save_file(basename($path), $style->href);
                    $style->href = $path;
                }
            }
    
            // update javascript to local assets folder
            foreach($scripts as $script) {
                if( isset($script->src) && pathinfo($script->src, PATHINFO_EXTENSION)){
	                $path = "resources". $this->get_asset_path($script->src);
	                $assets[$path] = $this->save_file(basename($path), $script->src);
                    $script->src = $path;
                }
            }
            // return the modified HTML + array of assets         
            return array(
                "html" => $html,
                "assets"  => $assets
            );
        }
        
        /* HELPER TO DISECT ASSET URL */
        private function get_asset_path( $url ){
	        $url = $this->make_absolute($url, "http://www.foo.com/");
	        $url_parts = parse_url($url);
	        $path = isset($url_parts["path"]) ? $url_parts["path"] : "";
	        //$query = isset($url_parts["query"]) ? "?" . $url_parts["query"] : "";
	        //$frag = isset($url_parts["fragment"]) ? "#" . $url_parts["fragment"] : "";
	        return $path;
        }
        
        // HELPER TO MAKE ABS URL
        private function make_absolute($url, $base) {
		    // Return base if no url
		    if( ! $url) return $base;
		
		    // Return if already absolute URL
		    if(parse_url($url, PHP_URL_SCHEME) != '') return $url;
		    
		    // Urls only containing query or anchor
		    if($url[0] == '#' || $url[0] == '?') return $base.$url;
		    
		    // Parse base URL and convert to local variables: $scheme, $host, $path
		    extract(parse_url($base));
		
		    // If no path, use /
		    if( ! isset($path)) $path = '/';
		 
		    // Remove non-directory element from path
		    $path = preg_replace('#/[^/]*$#', '', $path);
		 
		    // Destroy path if relative url points to root
		    if($url[0] == '/') $path = '';
		    
		    // Dirty absolute URL
		    $abs = "$host$path/$url";
		 
		    // Replace '//' or '/./' or '/foo/../' with '/'
		    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
		    for($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {}
		    
		    // Absolute URL is ready!
		    return $scheme.'://'.$abs;
		}

    } // END Bundlr
}
