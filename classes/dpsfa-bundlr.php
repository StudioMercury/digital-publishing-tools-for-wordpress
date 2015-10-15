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
            
            /* HTML OF ARTICLE */
            $html = $this->get_html_content($entity);
            
            /* COLLECT ADDITIONAL FILES FROM A TEMPLATE (CMS) */
            $files = array_merge($files, $this->get_files_from_template( $entity ));
                     
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
            
            /* HTML OF ARTICLE */
            $html = $this->get_html_content($entity);
            
            /* VERIFY TEMPLATE */
            if(file_exists($entity->template)){
	        	$template = $entity->template;
	        }else{
		        $templates = new Templates();
				$defaultTemplate = $templates->get_default();
		        $template = $defaultTemplate['path'];
	        }
            
            /* COLLECT ADDITIONAL FILES FROM A TEMPLATE (CMS) */
            ob_start();
            include_once($template);
			$output = ob_get_clean();
            $files = $this->get_files_from_template( $entity );

            /* COLLECT LINKED IMAGES / MEDIA / CSS / JS FILES */
			$collected = $this->get_linked_assets_from_html($html, $template);
            $files = array_merge( $files, $collected["assets"] );
            $files["index.html"] = $this->make_file("index", (string)$collected["html"]);
			
			/* VERIFY ARTICLE FILES */
			foreach($files as $key => $file){
				$size = filesize($file);
				if($size < 1 || $size === FALSE){
					unset($files[$key]);
				}
			}
			
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
		            case '123':
						$type = 'application/vnd.lotus-1-2-3';
						break;
					case '3dml':
						$type = 'text/vnd.in3d.3dml';
						break;
					case '3ds':
						$type = 'image/x-3ds';
						break;
					case '3g2':
						$type = 'video/3gpp2';
						break;
					case '3gp':
						$type = 'video/3gpp';
						break;
					case '7z':
						$type = 'application/x-7z-compressed';
						break;
					case 'aab':
						$type = 'application/x-authorware-bin';
						break;
					case 'aac':
						$type = 'audio/x-aac';
						break;
					case 'aam':
						$type = 'application/x-authorware-map';
						break;
					case 'aas':
						$type = 'application/x-authorware-seg';
						break;
					case 'abw':
						$type = 'application/x-abiword';
						break;
					case 'ac':
						$type = 'application/pkix-attr-cert';
						break;
					case 'acc':
						$type = 'application/vnd.americandynamics.acc';
						break;
					case 'ace':
						$type = 'application/x-ace-compressed';
						break;
					case 'acu':
						$type = 'application/vnd.acucobol';
						break;
					case 'acutc':
						$type = 'application/vnd.acucorp';
						break;
					case 'adp':
						$type = 'audio/adpcm';
						break;
					case 'aep':
						$type = 'application/vnd.audiograph';
						break;
					case 'afm':
						$type = 'application/x-font-type1';
						break;
					case 'afp':
						$type = 'application/vnd.ibm.modcap';
						break;
					case 'ahead':
						$type = 'application/vnd.ahead.space';
						break;
					case 'ai':
						$type = 'application/postscript';
						break;
					case 'aif':
						$type = 'audio/x-aiff';
						break;
					case 'aifc':
						$type = 'audio/x-aiff';
						break;
					case 'aiff':
						$type = 'audio/x-aiff';
						break;
					case 'air':
						$type = 'application/vnd.adobe.air-application-installer-package+zip';
						break;
					case 'ait':
						$type = 'application/vnd.dvb.ait';
						break;
					case 'ami':
						$type = 'application/vnd.amiga.ami';
						break;
					case 'apk':
						$type = 'application/vnd.android.package-archive';
						break;
					case 'appcache':
						$type = 'text/cache-manifest';
						break;
					case 'application':
						$type = 'application/x-ms-application';
						break;
					case 'apr':
						$type = 'application/vnd.lotus-approach';
						break;
					case 'arc':
						$type = 'application/x-freearc';
						break;
					case 'asc':
						$type = 'application/pgp-signature';
						break;
					case 'asf':
						$type = 'video/x-ms-asf';
						break;
					case 'asm':
						$type = 'text/x-asm';
						break;
					case 'aso':
						$type = 'application/vnd.accpac.simply.aso';
						break;
					case 'asx':
						$type = 'video/x-ms-asf';
						break;
					case 'atc':
						$type = 'application/vnd.acucorp';
						break;
					case 'atom':
						$type = 'application/atom+xml';
						break;
					case 'atomcat':
						$type = 'application/atomcat+xml';
						break;
					case 'atomsvc':
						$type = 'application/atomsvc+xml';
						break;
					case 'atx':
						$type = 'application/vnd.antix.game-component';
						break;
					case 'au':
						$type = 'audio/basic';
						break;
					case 'avi':
						$type = 'video/x-msvideo';
						break;
					case 'aw':
						$type = 'application/applixware';
						break;
					case 'azf':
						$type = 'application/vnd.airzip.filesecure.azf';
						break;
					case 'azs':
						$type = 'application/vnd.airzip.filesecure.azs';
						break;
					case 'azw':
						$type = 'application/vnd.amazon.ebook';
						break;
					case 'bat':
						$type = 'application/x-msdownload';
						break;
					case 'bcpio':
						$type = 'application/x-bcpio';
						break;
					case 'bdf':
						$type = 'application/x-font-bdf';
						break;
					case 'bdm':
						$type = 'application/vnd.syncml.dm+wbxml';
						break;
					case 'bed':
						$type = 'application/vnd.realvnc.bed';
						break;
					case 'bh2':
						$type = 'application/vnd.fujitsu.oasysprs';
						break;
					case 'bin':
						$type = 'application/octet-stream';
						break;
					case 'blb':
						$type = 'application/x-blorb';
						break;
					case 'blorb':
						$type = 'application/x-blorb';
						break;
					case 'bmi':
						$type = 'application/vnd.bmi';
						break;
					case 'bmp':
						$type = 'image/x-ms-bmp';
						break;
					case 'book':
						$type = 'application/vnd.framemaker';
						break;
					case 'box':
						$type = 'application/vnd.previewsystems.box';
						break;
					case 'boz':
						$type = 'application/x-bzip2';
						break;
					case 'bpk':
						$type = 'application/octet-stream';
						break;
					case 'btif':
						$type = 'image/prs.btif';
						break;
					case 'buffer':
						$type = 'application/octet-stream';
						break;
					case 'bz':
						$type = 'application/x-bzip';
						break;
					case 'bz2':
						$type = 'application/x-bzip2';
						break;
					case 'c':
						$type = 'text/x-c';
						break;
					case 'c11amc':
						$type = 'application/vnd.cluetrust.cartomobile-config';
						break;
					case 'c11amz':
						$type = 'application/vnd.cluetrust.cartomobile-config-pkg';
						break;
					case 'c4d':
						$type = 'application/vnd.clonk.c4group';
						break;
					case 'c4f':
						$type = 'application/vnd.clonk.c4group';
						break;
					case 'c4g':
						$type = 'application/vnd.clonk.c4group';
						break;
					case 'c4p':
						$type = 'application/vnd.clonk.c4group';
						break;
					case 'c4u':
						$type = 'application/vnd.clonk.c4group';
						break;
					case 'cab':
						$type = 'application/vnd.ms-cab-compressed';
						break;
					case 'caf':
						$type = 'audio/x-caf';
						break;
					case 'cap':
						$type = 'application/vnd.tcpdump.pcap';
						break;
					case 'car':
						$type = 'application/vnd.curl.car';
						break;
					case 'cat':
						$type = 'application/vnd.ms-pki.seccat';
						break;
					case 'cb7':
						$type = 'application/x-cbr';
						break;
					case 'cba':
						$type = 'application/x-cbr';
						break;
					case 'cbr':
						$type = 'application/x-cbr';
						break;
					case 'cbt':
						$type = 'application/x-cbr';
						break;
					case 'cbz':
						$type = 'application/x-cbr';
						break;
					case 'cc':
						$type = 'text/x-c';
						break;
					case 'cct':
						$type = 'application/x-director';
						break;
					case 'ccxml':
						$type = 'application/ccxml+xml';
						break;
					case 'cdbcmsg':
						$type = 'application/vnd.contact.cmsg';
						break;
					case 'cdf':
						$type = 'application/x-netcdf';
						break;
					case 'cdkey':
						$type = 'application/vnd.mediastation.cdkey';
						break;
					case 'cdmia':
						$type = 'application/cdmi-capability';
						break;
					case 'cdmic':
						$type = 'application/cdmi-container';
						break;
					case 'cdmid':
						$type = 'application/cdmi-domain';
						break;
					case 'cdmio':
						$type = 'application/cdmi-object';
						break;
					case 'cdmiq':
						$type = 'application/cdmi-queue';
						break;
					case 'cdx':
						$type = 'chemical/x-cdx';
						break;
					case 'cdxml':
						$type = 'application/vnd.chemdraw+xml';
						break;
					case 'cdy':
						$type = 'application/vnd.cinderella';
						break;
					case 'cer':
						$type = 'application/pkix-cert';
						break;
					case 'cfs':
						$type = 'application/x-cfs-compressed';
						break;
					case 'cgm':
						$type = 'image/cgm';
						break;
					case 'chat':
						$type = 'application/x-chat';
						break;
					case 'chm':
						$type = 'application/vnd.ms-htmlhelp';
						break;
					case 'chrt':
						$type = 'application/vnd.kde.kchart';
						break;
					case 'cif':
						$type = 'chemical/x-cif';
						break;
					case 'cii':
						$type = 'application/vnd.anser-web-certificate-issue-initiation';
						break;
					case 'cil':
						$type = 'application/vnd.ms-artgalry';
						break;
					case 'cla':
						$type = 'application/vnd.claymore';
						break;
					case 'class':
						$type = 'application/java-vm';
						break;
					case 'clkk':
						$type = 'application/vnd.crick.clicker.keyboard';
						break;
					case 'clkp':
						$type = 'application/vnd.crick.clicker.palette';
						break;
					case 'clkt':
						$type = 'application/vnd.crick.clicker.template';
						break;
					case 'clkw':
						$type = 'application/vnd.crick.clicker.wordbank';
						break;
					case 'clkx':
						$type = 'application/vnd.crick.clicker';
						break;
					case 'clp':
						$type = 'application/x-msclip';
						break;
					case 'cmc':
						$type = 'application/vnd.cosmocaller';
						break;
					case 'cmdf':
						$type = 'chemical/x-cmdf';
						break;
					case 'cml':
						$type = 'chemical/x-cml';
						break;
					case 'cmp':
						$type = 'application/vnd.yellowriver-custom-menu';
						break;
					case 'cmx':
						$type = 'image/x-cmx';
						break;
					case 'cod':
						$type = 'application/vnd.rim.cod';
						break;
					case 'com':
						$type = 'application/x-msdownload';
						break;
					case 'conf':
						$type = 'text/plain';
						break;
					case 'cpio':
						$type = 'application/x-cpio';
						break;
					case 'cpp':
						$type = 'text/x-c';
						break;
					case 'cpt':
						$type = 'application/mac-compactpro';
						break;
					case 'crd':
						$type = 'application/x-mscardfile';
						break;
					case 'crl':
						$type = 'application/pkix-crl';
						break;
					case 'crt':
						$type = 'application/x-x509-ca-cert';
						break;
					case 'crx':
						$type = 'application/x-chrome-extension';
						break;
					case 'cryptonote':
						$type = 'application/vnd.rig.cryptonote';
						break;
					case 'csh':
						$type = 'application/x-csh';
						break;
					case 'csml':
						$type = 'chemical/x-csml';
						break;
					case 'csp':
						$type = 'application/vnd.commonspace';
						break;
					case 'css':
						$type = 'text/css';
						break;
					case 'cst':
						$type = 'application/x-director';
						break;
					case 'csv':
						$type = 'text/csv';
						break;
					case 'cu':
						$type = 'application/cu-seeme';
						break;
					case 'curl':
						$type = 'text/vnd.curl';
						break;
					case 'cww':
						$type = 'application/prs.cww';
						break;
					case 'cxt':
						$type = 'application/x-director';
						break;
					case 'cxx':
						$type = 'text/x-c';
						break;
					case 'dae':
						$type = 'model/vnd.collada+xml';
						break;
					case 'daf':
						$type = 'application/vnd.mobius.daf';
						break;
					case 'dart':
						$type = 'application/vnd.dart';
						break;
					case 'dataless':
						$type = 'application/vnd.fdsn.seed';
						break;
					case 'davmount':
						$type = 'application/davmount+xml';
						break;
					case 'dbk':
						$type = 'application/docbook+xml';
						break;
					case 'dcr':
						$type = 'application/x-director';
						break;
					case 'dcurl':
						$type = 'text/vnd.curl.dcurl';
						break;
					case 'dd2':
						$type = 'application/vnd.oma.dd2+xml';
						break;
					case 'ddd':
						$type = 'application/vnd.fujixerox.ddd';
						break;
					case 'deb':
						$type = 'application/x-debian-package';
						break;
					case 'def':
						$type = 'text/plain';
						break;
					case 'deploy':
						$type = 'application/octet-stream';
						break;
					case 'der':
						$type = 'application/x-x509-ca-cert';
						break;
					case 'dfac':
						$type = 'application/vnd.dreamfactory';
						break;
					case 'dgc':
						$type = 'application/x-dgc-compressed';
						break;
					case 'dic':
						$type = 'text/x-c';
						break;
					case 'dir':
						$type = 'application/x-director';
						break;
					case 'dis':
						$type = 'application/vnd.mobius.dis';
						break;
					case 'dist':
						$type = 'application/octet-stream';
						break;
					case 'distz':
						$type = 'application/octet-stream';
						break;
					case 'djv':
						$type = 'image/vnd.djvu';
						break;
					case 'djvu':
						$type = 'image/vnd.djvu';
						break;
					case 'dll':
						$type = 'application/x-msdownload';
						break;
					case 'dmg':
						$type = 'application/x-apple-diskimage';
						break;
					case 'dmp':
						$type = 'application/vnd.tcpdump.pcap';
						break;
					case 'dms':
						$type = 'application/octet-stream';
						break;
					case 'dna':
						$type = 'application/vnd.dna';
						break;
					case 'doc':
						$type = 'application/msword';
						break;
					case 'docm':
						$type = 'application/vnd.ms-word.document.macroenabled.12';
						break;
					case 'docx':
						$type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
						break;
					case 'dot':
						$type = 'application/msword';
						break;
					case 'dotm':
						$type = 'application/vnd.ms-word.template.macroenabled.12';
						break;
					case 'dotx':
						$type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
						break;
					case 'dp':
						$type = 'application/vnd.osgi.dp';
						break;
					case 'dpg':
						$type = 'application/vnd.dpgraph';
						break;
					case 'dra':
						$type = 'audio/vnd.dra';
						break;
					case 'dsc':
						$type = 'text/prs.lines.tag';
						break;
					case 'dssc':
						$type = 'application/dssc+der';
						break;
					case 'dtb':
						$type = 'application/x-dtbook+xml';
						break;
					case 'dtd':
						$type = 'application/xml-dtd';
						break;
					case 'dts':
						$type = 'audio/vnd.dts';
						break;
					case 'dtshd':
						$type = 'audio/vnd.dts.hd';
						break;
					case 'dump':
						$type = 'application/octet-stream';
						break;
					case 'dvb':
						$type = 'video/vnd.dvb.file';
						break;
					case 'dvi':
						$type = 'application/x-dvi';
						break;
					case 'dwf':
						$type = 'model/vnd.dwf';
						break;
					case 'dwg':
						$type = 'image/vnd.dwg';
						break;
					case 'dxf':
						$type = 'image/vnd.dxf';
						break;
					case 'dxp':
						$type = 'application/vnd.spotfire.dxp';
						break;
					case 'dxr':
						$type = 'application/x-director';
						break;
					case 'ecelp4800':
						$type = 'audio/vnd.nuera.ecelp4800';
						break;
					case 'ecelp7470':
						$type = 'audio/vnd.nuera.ecelp7470';
						break;
					case 'ecelp9600':
						$type = 'audio/vnd.nuera.ecelp9600';
						break;
					case 'ecma':
						$type = 'application/ecmascript';
						break;
					case 'edm':
						$type = 'application/vnd.novadigm.edm';
						break;
					case 'edx':
						$type = 'application/vnd.novadigm.edx';
						break;
					case 'efif':
						$type = 'application/vnd.picsel';
						break;
					case 'ei6':
						$type = 'application/vnd.pg.osasli';
						break;
					case 'elc':
						$type = 'application/octet-stream';
						break;
					case 'emf':
						$type = 'application/x-msmetafile';
						break;
					case 'eml':
						$type = 'message/rfc822';
						break;
					case 'emma':
						$type = 'application/emma+xml';
						break;
					case 'emz':
						$type = 'application/x-msmetafile';
						break;
					case 'eol':
						$type = 'audio/vnd.digital-winds';
						break;
					case 'eot':
						$type = 'application/vnd.ms-fontobject';
						break;
					case 'eps':
						$type = 'application/postscript';
						break;
					case 'epub':
						$type = 'application/epub+zip';
						break;
					case 'es3':
						$type = 'application/vnd.eszigno3+xml';
						break;
					case 'esa':
						$type = 'application/vnd.osgi.subsystem';
						break;
					case 'esf':
						$type = 'application/vnd.epson.esf';
						break;
					case 'et3':
						$type = 'application/vnd.eszigno3+xml';
						break;
					case 'etx':
						$type = 'text/x-setext';
						break;
					case 'eva':
						$type = 'application/x-eva';
						break;
					case 'event-stream':
						$type = 'text/event-stream';
						break;
					case 'evy':
						$type = 'application/x-envoy';
						break;
					case 'exe':
						$type = 'application/x-msdownload';
						break;
					case 'exi':
						$type = 'application/exi';
						break;
					case 'ext':
						$type = 'application/vnd.novadigm.ext';
						break;
					case 'ez':
						$type = 'application/andrew-inset';
						break;
					case 'ez2':
						$type = 'application/vnd.ezpix-album';
						break;
					case 'ez3':
						$type = 'application/vnd.ezpix-package';
						break;
					case 'f':
						$type = 'text/x-fortran';
						break;
					case 'f4v':
						$type = 'video/x-f4v';
						break;
					case 'f77':
						$type = 'text/x-fortran';
						break;
					case 'f90':
						$type = 'text/x-fortran';
						break;
					case 'fbs':
						$type = 'image/vnd.fastbidsheet';
						break;
					case 'fcdt':
						$type = 'application/vnd.adobe.formscentral.fcdt';
						break;
					case 'fcs':
						$type = 'application/vnd.isac.fcs';
						break;
					case 'fdf':
						$type = 'application/vnd.fdf';
						break;
					case 'fe_launch':
						$type = 'application/vnd.denovo.fcselayout-link';
						break;
					case 'fg5':
						$type = 'application/vnd.fujitsu.oasysgp';
						break;
					case 'fgd':
						$type = 'application/x-director';
						break;
					case 'fh':
						$type = 'image/x-freehand';
						break;
					case 'fh4':
						$type = 'image/x-freehand';
						break;
					case 'fh5':
						$type = 'image/x-freehand';
						break;
					case 'fh7':
						$type = 'image/x-freehand';
						break;
					case 'fhc':
						$type = 'image/x-freehand';
						break;
					case 'fig':
						$type = 'application/x-xfig';
						break;
					case 'flac':
						$type = 'audio/flac';
						break;
					case 'fli':
						$type = 'video/x-fli';
						break;
					case 'flo':
						$type = 'application/vnd.micrografx.flo';
						break;
					case 'flv':
						$type = 'video/x-flv';
						break;
					case 'flw':
						$type = 'application/vnd.kde.kivio';
						break;
					case 'flx':
						$type = 'text/vnd.fmi.flexstor';
						break;
					case 'fly':
						$type = 'text/vnd.fly';
						break;
					case 'fm':
						$type = 'application/vnd.framemaker';
						break;
					case 'fnc':
						$type = 'application/vnd.frogans.fnc';
						break;
					case 'for':
						$type = 'text/x-fortran';
						break;
					case 'fpx':
						$type = 'image/vnd.fpx';
						break;
					case 'frame':
						$type = 'application/vnd.framemaker';
						break;
					case 'fsc':
						$type = 'application/vnd.fsc.weblaunch';
						break;
					case 'fst':
						$type = 'image/vnd.fst';
						break;
					case 'ftc':
						$type = 'application/vnd.fluxtime.clip';
						break;
					case 'fti':
						$type = 'application/vnd.anser-web-funds-transfer-initiation';
						break;
					case 'fvt':
						$type = 'video/vnd.fvt';
						break;
					case 'fxp':
						$type = 'application/vnd.adobe.fxp';
						break;
					case 'fxpl':
						$type = 'application/vnd.adobe.fxp';
						break;
					case 'fzs':
						$type = 'application/vnd.fuzzysheet';
						break;
					case 'g2w':
						$type = 'application/vnd.geoplan';
						break;
					case 'g3':
						$type = 'image/g3fax';
						break;
					case 'g3w':
						$type = 'application/vnd.geospace';
						break;
					case 'gac':
						$type = 'application/vnd.groove-account';
						break;
					case 'gam':
						$type = 'application/x-tads';
						break;
					case 'gbr':
						$type = 'application/rpki-ghostbusters';
						break;
					case 'gca':
						$type = 'application/x-gca-compressed';
						break;
					case 'gdl':
						$type = 'model/vnd.gdl';
						break;
					case 'geo':
						$type = 'application/vnd.dynageo';
						break;
					case 'gex':
						$type = 'application/vnd.geometry-explorer';
						break;
					case 'ggb':
						$type = 'application/vnd.geogebra.file';
						break;
					case 'ggt':
						$type = 'application/vnd.geogebra.tool';
						break;
					case 'ghf':
						$type = 'application/vnd.groove-help';
						break;
					case 'gif':
						$type = 'image/gif';
						break;
					case 'gim':
						$type = 'application/vnd.groove-identity-message';
						break;
					case 'gml':
						$type = 'application/gml+xml';
						break;
					case 'gmx':
						$type = 'application/vnd.gmx';
						break;
					case 'gnumeric':
						$type = 'application/x-gnumeric';
						break;
					case 'gph':
						$type = 'application/vnd.flographit';
						break;
					case 'gpx':
						$type = 'application/gpx+xml';
						break;
					case 'gqf':
						$type = 'application/vnd.grafeq';
						break;
					case 'gqs':
						$type = 'application/vnd.grafeq';
						break;
					case 'gram':
						$type = 'application/srgs';
						break;
					case 'gramps':
						$type = 'application/x-gramps-xml';
						break;
					case 'gre':
						$type = 'application/vnd.geometry-explorer';
						break;
					case 'grv':
						$type = 'application/vnd.groove-injector';
						break;
					case 'grxml':
						$type = 'application/srgs+xml';
						break;
					case 'gsf':
						$type = 'application/x-font-ghostscript';
						break;
					case 'gtar':
						$type = 'application/x-gtar';
						break;
					case 'gtm':
						$type = 'application/vnd.groove-tool-message';
						break;
					case 'gtw':
						$type = 'model/vnd.gtw';
						break;
					case 'gv':
						$type = 'text/vnd.graphviz';
						break;
					case 'gxf':
						$type = 'application/gxf';
						break;
					case 'gxt':
						$type = 'application/vnd.geonext';
						break;
					case 'h':
						$type = 'text/x-c';
						break;
					case 'h261':
						$type = 'video/h261';
						break;
					case 'h263':
						$type = 'video/h263';
						break;
					case 'h264':
						$type = 'video/h264';
						break;
					case 'hal':
						$type = 'application/vnd.hal+xml';
						break;
					case 'hbci':
						$type = 'application/vnd.hbci';
						break;
					case 'hdf':
						$type = 'application/x-hdf';
						break;
					case 'hh':
						$type = 'text/x-c';
						break;
					case 'hlp':
						$type = 'application/winhlp';
						break;
					case 'hpgl':
						$type = 'application/vnd.hp-hpgl';
						break;
					case 'hpid':
						$type = 'application/vnd.hp-hpid';
						break;
					case 'hps':
						$type = 'application/vnd.hp-hps';
						break;
					case 'hqx':
						$type = 'application/mac-binhex40';
						break;
					case 'htc':
						$type = 'text/x-component';
						break;
					case 'htke':
						$type = 'application/vnd.kenameaapp';
						break;
					case 'htm':
						$type = 'text/html';
						break;
					case 'html':
						$type = 'text/html';
						break;
					case 'hvd':
						$type = 'application/vnd.yamaha.hv-dic';
						break;
					case 'hvp':
						$type = 'application/vnd.yamaha.hv-voice';
						break;
					case 'hvs':
						$type = 'application/vnd.yamaha.hv-script';
						break;
					case 'i2g':
						$type = 'application/vnd.intergeo';
						break;
					case 'icc':
						$type = 'application/vnd.iccprofile';
						break;
					case 'ice':
						$type = 'x-conference/x-cooltalk';
						break;
					case 'icm':
						$type = 'application/vnd.iccprofile';
						break;
					case 'ico':
						$type = 'image/x-icon';
						break;
					case 'ics':
						$type = 'text/calendar';
						break;
					case 'ief':
						$type = 'image/ief';
						break;
					case 'ifb':
						$type = 'text/calendar';
						break;
					case 'ifm':
						$type = 'application/vnd.shana.informed.formdata';
						break;
					case 'iges':
						$type = 'model/iges';
						break;
					case 'igl':
						$type = 'application/vnd.igloader';
						break;
					case 'igm':
						$type = 'application/vnd.insors.igm';
						break;
					case 'igs':
						$type = 'model/iges';
						break;
					case 'igx':
						$type = 'application/vnd.micrografx.igx';
						break;
					case 'iif':
						$type = 'application/vnd.shana.informed.interchange';
						break;
					case 'imp':
						$type = 'application/vnd.accpac.simply.imp';
						break;
					case 'ims':
						$type = 'application/vnd.ms-ims';
						break;
					case 'in':
						$type = 'text/plain';
						break;
					case 'ink':
						$type = 'application/inkml+xml';
						break;
					case 'inkml':
						$type = 'application/inkml+xml';
						break;
					case 'install':
						$type = 'application/x-install-instructions';
						break;
					case 'iota':
						$type = 'application/vnd.astraea-software.iota';
						break;
					case 'ipfix':
						$type = 'application/ipfix';
						break;
					case 'ipk':
						$type = 'application/vnd.shana.informed.package';
						break;
					case 'irm':
						$type = 'application/vnd.ibm.rights-management';
						break;
					case 'irp':
						$type = 'application/vnd.irepository.package+xml';
						break;
					case 'iso':
						$type = 'application/x-iso9660-image';
						break;
					case 'itp':
						$type = 'application/vnd.shana.informed.formtemplate';
						break;
					case 'ivp':
						$type = 'application/vnd.immervision-ivp';
						break;
					case 'ivu':
						$type = 'application/vnd.immervision-ivu';
						break;
					case 'jad':
						$type = 'text/vnd.sun.j2me.app-descriptor';
						break;
					case 'jam':
						$type = 'application/vnd.jam';
						break;
					case 'jar':
						$type = 'application/java-archive';
						break;
					case 'java':
						$type = 'text/x-java-source';
						break;
					case 'jisp':
						$type = 'application/vnd.jisp';
						break;
					case 'jlt':
						$type = 'application/vnd.hp-jlyt';
						break;
					case 'jnlp':
						$type = 'application/x-java-jnlp-file';
						break;
					case 'joda':
						$type = 'application/vnd.joost.joda-archive';
						break;
					case 'jpe':
						$type = 'image/jpeg';
						break;
					case 'jpeg':
						$type = 'image/jpeg';
						break;
					case 'jpg':
						$type = 'image/jpeg';
						break;
					case 'jpgm':
						$type = 'video/jpm';
						break;
					case 'jpgv':
						$type = 'video/jpeg';
						break;
					case 'jpm':
						$type = 'video/jpm';
						break;
					case 'js':
						$type = 'application/javascript';
						break;
					case 'json':
						$type = 'application/json';
						break;
					case 'jsonml':
						$type = 'application/jsonml+json';
						break;
					case 'kar':
						$type = 'audio/midi';
						break;
					case 'karbon':
						$type = 'application/vnd.kde.karbon';
						break;
					case 'kfo':
						$type = 'application/vnd.kde.kformula';
						break;
					case 'kia':
						$type = 'application/vnd.kidspiration';
						break;
					case 'kml':
						$type = 'application/vnd.google-earth.kml+xml';
						break;
					case 'kmz':
						$type = 'application/vnd.google-earth.kmz';
						break;
					case 'kne':
						$type = 'application/vnd.kinar';
						break;
					case 'knp':
						$type = 'application/vnd.kinar';
						break;
					case 'kon':
						$type = 'application/vnd.kde.kontour';
						break;
					case 'kpr':
						$type = 'application/vnd.kde.kpresenter';
						break;
					case 'kpt':
						$type = 'application/vnd.kde.kpresenter';
						break;
					case 'kpxx':
						$type = 'application/vnd.ds-keypoint';
						break;
					case 'ksp':
						$type = 'application/vnd.kde.kspread';
						break;
					case 'ktr':
						$type = 'application/vnd.kahootz';
						break;
					case 'ktx':
						$type = 'image/ktx';
						break;
					case 'ktz':
						$type = 'application/vnd.kahootz';
						break;
					case 'kwd':
						$type = 'application/vnd.kde.kword';
						break;
					case 'kwt':
						$type = 'application/vnd.kde.kword';
						break;
					case 'lasxml':
						$type = 'application/vnd.las.las+xml';
						break;
					case 'latex':
						$type = 'application/x-latex';
						break;
					case 'lbd':
						$type = 'application/vnd.llamagraphics.life-balance.desktop';
						break;
					case 'lbe':
						$type = 'application/vnd.llamagraphics.life-balance.exchange+xml';
						break;
					case 'les':
						$type = 'application/vnd.hhe.lesson-player';
						break;
					case 'lha':
						$type = 'application/x-lzh-compressed';
						break;
					case 'link66':
						$type = 'application/vnd.route66.link66+xml';
						break;
					case 'list':
						$type = 'text/plain';
						break;
					case 'list3820':
						$type = 'application/vnd.ibm.modcap';
						break;
					case 'listafp':
						$type = 'application/vnd.ibm.modcap';
						break;
					case 'lnk':
						$type = 'application/x-ms-shortcut';
						break;
					case 'log':
						$type = 'text/plain';
						break;
					case 'lostxml':
						$type = 'application/lost+xml';
						break;
					case 'lrf':
						$type = 'application/octet-stream';
						break;
					case 'lrm':
						$type = 'application/vnd.ms-lrm';
						break;
					case 'ltf':
						$type = 'application/vnd.frogans.ltf';
						break;
					case 'lua':
						$type = 'text/x-lua';
						break;
					case 'luac':
						$type = 'application/x-lua-bytecode';
						break;
					case 'lvp':
						$type = 'audio/vnd.lucent.voice';
						break;
					case 'lwp':
						$type = 'application/vnd.lotus-wordpro';
						break;
					case 'lzh':
						$type = 'application/x-lzh-compressed';
						break;
					case 'm13':
						$type = 'application/x-msmediaview';
						break;
					case 'm14':
						$type = 'application/x-msmediaview';
						break;
					case 'm1v':
						$type = 'video/mpeg';
						break;
					case 'm21':
						$type = 'application/mp21';
						break;
					case 'm2a':
						$type = 'audio/mpeg';
						break;
					case 'm2v':
						$type = 'video/mpeg';
						break;
					case 'm3a':
						$type = 'audio/mpeg';
						break;
					case 'm3u':
						$type = 'audio/x-mpegurl';
						break;
					case 'm3u8':
						$type = 'application/x-mpegURL';
						break;
					case 'm4a':
						$type = 'audio/mp4';
						break;
					case 'm4p':
						$type = 'application/mp4';
						break;
					case 'm4u':
						$type = 'video/vnd.mpegurl';
						break;
					case 'm4v':
						$type = 'video/x-m4v';
						break;
					case 'ma':
						$type = 'application/mathematica';
						break;
					case 'mads':
						$type = 'application/mads+xml';
						break;
					case 'mag':
						$type = 'application/vnd.ecowin.chart';
						break;
					case 'maker':
						$type = 'application/vnd.framemaker';
						break;
					case 'man':
						$type = 'text/troff';
						break;
					case 'manifest':
						$type = 'text/cache-manifest';
						break;
					case 'mar':
						$type = 'application/octet-stream';
						break;
					case 'markdown':
						$type = 'text/x-markdown';
						break;
					case 'mathml':
						$type = 'application/mathml+xml';
						break;
					case 'mb':
						$type = 'application/mathematica';
						break;
					case 'mbk':
						$type = 'application/vnd.mobius.mbk';
						break;
					case 'mbox':
						$type = 'application/mbox';
						break;
					case 'mc1':
						$type = 'application/vnd.medcalcdata';
						break;
					case 'mcd':
						$type = 'application/vnd.mcd';
						break;
					case 'mcurl':
						$type = 'text/vnd.curl.mcurl';
						break;
					case 'md':
						$type = 'text/x-markdown';
						break;
					case 'mdb':
						$type = 'application/x-msaccess';
						break;
					case 'mdi':
						$type = 'image/vnd.ms-modi';
						break;
					case 'me':
						$type = 'text/troff';
						break;
					case 'mesh':
						$type = 'model/mesh';
						break;
					case 'meta4':
						$type = 'application/metalink4+xml';
						break;
					case 'metalink':
						$type = 'application/metalink+xml';
						break;
					case 'mets':
						$type = 'application/mets+xml';
						break;
					case 'mfm':
						$type = 'application/vnd.mfmp';
						break;
					case 'mft':
						$type = 'application/rpki-manifest';
						break;
					case 'mgp':
						$type = 'application/vnd.osgeo.mapguide.package';
						break;
					case 'mgz':
						$type = 'application/vnd.proteus.magazine';
						break;
					case 'mid':
						$type = 'audio/midi';
						break;
					case 'midi':
						$type = 'audio/midi';
						break;
					case 'mie':
						$type = 'application/x-mie';
						break;
					case 'mif':
						$type = 'application/vnd.mif';
						break;
					case 'mime':
						$type = 'message/rfc822';
						break;
					case 'mj2':
						$type = 'video/mj2';
						break;
					case 'mjp2':
						$type = 'video/mj2';
						break;
					case 'mk3d':
						$type = 'video/x-matroska';
						break;
					case 'mka':
						$type = 'audio/x-matroska';
						break;
					case 'mkd':
						$type = 'text/x-markdown';
						break;
					case 'mks':
						$type = 'video/x-matroska';
						break;
					case 'mkv':
						$type = 'video/x-matroska';
						break;
					case 'mlp':
						$type = 'application/vnd.dolby.mlp';
						break;
					case 'mmd':
						$type = 'application/vnd.chipnuts.karaoke-mmd';
						break;
					case 'mmf':
						$type = 'application/vnd.smaf';
						break;
					case 'mmr':
						$type = 'image/vnd.fujixerox.edmics-mmr';
						break;
					case 'mng':
						$type = 'video/x-mng';
						break;
					case 'mny':
						$type = 'application/x-msmoney';
						break;
					case 'mobi':
						$type = 'application/x-mobipocket-ebook';
						break;
					case 'mods':
						$type = 'application/mods+xml';
						break;
					case 'mov':
						$type = 'video/quicktime';
						break;
					case 'movie':
						$type = 'video/x-sgi-movie';
						break;
					case 'mp2':
						$type = 'audio/mpeg';
						break;
					case 'mp21':
						$type = 'application/mp21';
						break;
					case 'mp2a':
						$type = 'audio/mpeg';
						break;
					case 'mp3':
						$type = 'audio/mpeg';
						break;
					case 'mp4':
						$type = 'video/mp4';
						break;
					case 'mp4a':
						$type = 'audio/mp4';
						break;
					case 'mp4s':
						$type = 'application/mp4';
						break;
					case 'mp4v':
						$type = 'video/mp4';
						break;
					case 'mpc':
						$type = 'application/vnd.mophun.certificate';
						break;
					case 'mpe':
						$type = 'video/mpeg';
						break;
					case 'mpeg':
						$type = 'video/mpeg';
						break;
					case 'mpg':
						$type = 'video/mpeg';
						break;
					case 'mpg4':
						$type = 'video/mp4';
						break;
					case 'mpga':
						$type = 'audio/mpeg';
						break;
					case 'mpkg':
						$type = 'application/vnd.apple.installer+xml';
						break;
					case 'mpm':
						$type = 'application/vnd.blueice.multipass';
						break;
					case 'mpn':
						$type = 'application/vnd.mophun.application';
						break;
					case 'mpp':
						$type = 'application/vnd.ms-project';
						break;
					case 'mpt':
						$type = 'application/vnd.ms-project';
						break;
					case 'mpy':
						$type = 'application/vnd.ibm.minipay';
						break;
					case 'mqy':
						$type = 'application/vnd.mobius.mqy';
						break;
					case 'mrc':
						$type = 'application/marc';
						break;
					case 'mrcx':
						$type = 'application/marcxml+xml';
						break;
					case 'ms':
						$type = 'text/troff';
						break;
					case 'mscml':
						$type = 'application/mediaservercontrol+xml';
						break;
					case 'mseed':
						$type = 'application/vnd.fdsn.mseed';
						break;
					case 'mseq':
						$type = 'application/vnd.mseq';
						break;
					case 'msf':
						$type = 'application/vnd.epson.msf';
						break;
					case 'msh':
						$type = 'model/mesh';
						break;
					case 'msi':
						$type = 'application/x-msdownload';
						break;
					case 'msl':
						$type = 'application/vnd.mobius.msl';
						break;
					case 'msty':
						$type = 'application/vnd.muvee.style';
						break;
					case 'mts':
						$type = 'model/vnd.mts';
						break;
					case 'mus':
						$type = 'application/vnd.musician';
						break;
					case 'musicxml':
						$type = 'application/vnd.recordare.musicxml+xml';
						break;
					case 'mvb':
						$type = 'application/x-msmediaview';
						break;
					case 'mwf':
						$type = 'application/vnd.mfer';
						break;
					case 'mxf':
						$type = 'application/mxf';
						break;
					case 'mxl':
						$type = 'application/vnd.recordare.musicxml';
						break;
					case 'mxml':
						$type = 'application/xv+xml';
						break;
					case 'mxs':
						$type = 'application/vnd.triscape.mxs';
						break;
					case 'mxu':
						$type = 'video/vnd.mpegurl';
						break;
					case 'n-gage':
						$type = 'application/vnd.nokia.n-gage.symbian.install';
						break;
					case 'n3':
						$type = 'text/n3';
						break;
					case 'nb':
						$type = 'application/mathematica';
						break;
					case 'nbp':
						$type = 'application/vnd.wolfram.player';
						break;
					case 'nc':
						$type = 'application/x-netcdf';
						break;
					case 'ncx':
						$type = 'application/x-dtbncx+xml';
						break;
					case 'nfo':
						$type = 'text/x-nfo';
						break;
					case 'ngdat':
						$type = 'application/vnd.nokia.n-gage.data';
						break;
					case 'nitf':
						$type = 'application/vnd.nitf';
						break;
					case 'nlu':
						$type = 'application/vnd.neurolanguage.nlu';
						break;
					case 'nml':
						$type = 'application/vnd.enliven';
						break;
					case 'nnd':
						$type = 'application/vnd.noblenet-directory';
						break;
					case 'nns':
						$type = 'application/vnd.noblenet-sealer';
						break;
					case 'nnw':
						$type = 'application/vnd.noblenet-web';
						break;
					case 'npx':
						$type = 'image/vnd.net-fpx';
						break;
					case 'nsc':
						$type = 'application/x-conference';
						break;
					case 'nsf':
						$type = 'application/vnd.lotus-notes';
						break;
					case 'ntf':
						$type = 'application/vnd.nitf';
						break;
					case 'nzb':
						$type = 'application/x-nzb';
						break;
					case 'oa2':
						$type = 'application/vnd.fujitsu.oasys2';
						break;
					case 'oa3':
						$type = 'application/vnd.fujitsu.oasys3';
						break;
					case 'oas':
						$type = 'application/vnd.fujitsu.oasys';
						break;
					case 'obd':
						$type = 'application/x-msbinder';
						break;
					case 'obj':
						$type = 'application/x-tgif';
						break;
					case 'oda':
						$type = 'application/oda';
						break;
					case 'odb':
						$type = 'application/vnd.oasis.opendocument.database';
						break;
					case 'odc':
						$type = 'application/vnd.oasis.opendocument.chart';
						break;
					case 'odf':
						$type = 'application/vnd.oasis.opendocument.formula';
						break;
					case 'odft':
						$type = 'application/vnd.oasis.opendocument.formula-template';
						break;
					case 'odg':
						$type = 'application/vnd.oasis.opendocument.graphics';
						break;
					case 'odi':
						$type = 'application/vnd.oasis.opendocument.image';
						break;
					case 'odm':
						$type = 'application/vnd.oasis.opendocument.text-master';
						break;
					case 'odp':
						$type = 'application/vnd.oasis.opendocument.presentation';
						break;
					case 'ods':
						$type = 'application/vnd.oasis.opendocument.spreadsheet';
						break;
					case 'odt':
						$type = 'application/vnd.oasis.opendocument.text';
						break;
					case 'oga':
						$type = 'audio/ogg';
						break;
					case 'ogg':
						$type = 'audio/ogg';
						break;
					case 'ogv':
						$type = 'video/ogg';
						break;
					case 'ogx':
						$type = 'application/ogg';
						break;
					case 'omdoc':
						$type = 'application/omdoc+xml';
						break;
					case 'onepkg':
						$type = 'application/onenote';
						break;
					case 'onetmp':
						$type = 'application/onenote';
						break;
					case 'onetoc':
						$type = 'application/onenote';
						break;
					case 'onetoc2':
						$type = 'application/onenote';
						break;
					case 'opf':
						$type = 'application/oebps-package+xml';
						break;
					case 'opml':
						$type = 'text/x-opml';
						break;
					case 'oprc':
						$type = 'application/vnd.palm';
						break;
					case 'org':
						$type = 'application/vnd.lotus-organizer';
						break;
					case 'osf':
						$type = 'application/vnd.yamaha.openscoreformat';
						break;
					case 'osfpvg':
						$type = 'application/vnd.yamaha.openscoreformat.osfpvg+xml';
						break;
					case 'otc':
						$type = 'application/vnd.oasis.opendocument.chart-template';
						break;
					case 'otf':
						$type = 'font/opentype';
						break;
					case 'otg':
						$type = 'application/vnd.oasis.opendocument.graphics-template';
						break;
					case 'oth':
						$type = 'application/vnd.oasis.opendocument.text-web';
						break;
					case 'oti':
						$type = 'application/vnd.oasis.opendocument.image-template';
						break;
					case 'otp':
						$type = 'application/vnd.oasis.opendocument.presentation-template';
						break;
					case 'ots':
						$type = 'application/vnd.oasis.opendocument.spreadsheet-template';
						break;
					case 'ott':
						$type = 'application/vnd.oasis.opendocument.text-template';
						break;
					case 'oxps':
						$type = 'application/oxps';
						break;
					case 'oxt':
						$type = 'application/vnd.openofficeorg.extension';
						break;
					case 'p':
						$type = 'text/x-pascal';
						break;
					case 'p10':
						$type = 'application/pkcs10';
						break;
					case 'p12':
						$type = 'application/x-pkcs12';
						break;
					case 'p7b':
						$type = 'application/x-pkcs7-certificates';
						break;
					case 'p7c':
						$type = 'application/pkcs7-mime';
						break;
					case 'p7m':
						$type = 'application/pkcs7-mime';
						break;
					case 'p7r':
						$type = 'application/x-pkcs7-certreqresp';
						break;
					case 'p7s':
						$type = 'application/pkcs7-signature';
						break;
					case 'p8':
						$type = 'application/pkcs8';
						break;
					case 'pas':
						$type = 'text/x-pascal';
						break;
					case 'paw':
						$type = 'application/vnd.pawaafile';
						break;
					case 'pbd':
						$type = 'application/vnd.powerbuilder6';
						break;
					case 'pbm':
						$type = 'image/x-portable-bitmap';
						break;
					case 'pcap':
						$type = 'application/vnd.tcpdump.pcap';
						break;
					case 'pcf':
						$type = 'application/x-font-pcf';
						break;
					case 'pcl':
						$type = 'application/vnd.hp-pcl';
						break;
					case 'pclxl':
						$type = 'application/vnd.hp-pclxl';
						break;
					case 'pct':
						$type = 'image/x-pict';
						break;
					case 'pcurl':
						$type = 'application/vnd.curl.pcurl';
						break;
					case 'pcx':
						$type = 'image/x-pcx';
						break;
					case 'pdb':
						$type = 'application/vnd.palm';
						break;
					case 'pdf':
						$type = 'application/pdf';
						break;
					case 'pfa':
						$type = 'application/x-font-type1';
						break;
					case 'pfb':
						$type = 'application/x-font-type1';
						break;
					case 'pfm':
						$type = 'application/x-font-type1';
						break;
					case 'pfr':
						$type = 'application/font-tdpfr';
						break;
					case 'pfx':
						$type = 'application/x-pkcs12';
						break;
					case 'pgm':
						$type = 'image/x-portable-graymap';
						break;
					case 'pgn':
						$type = 'application/x-chess-pgn';
						break;
					case 'pgp':
						$type = 'application/pgp-encrypted';
						break;
					case 'pic':
						$type = 'image/x-pict';
						break;
					case 'pkg':
						$type = 'application/octet-stream';
						break;
					case 'pki':
						$type = 'application/pkixcmp';
						break;
					case 'pkipath':
						$type = 'application/pkix-pkipath';
						break;
					case 'plb':
						$type = 'application/vnd.3gpp.pic-bw-large';
						break;
					case 'plc':
						$type = 'application/vnd.mobius.plc';
						break;
					case 'plf':
						$type = 'application/vnd.pocketlearn';
						break;
					case 'pls':
						$type = 'application/pls+xml';
						break;
					case 'pml':
						$type = 'application/vnd.ctc-posml';
						break;
					case 'png':
						$type = 'image/png';
						break;
					case 'pnm':
						$type = 'image/x-portable-anymap';
						break;
					case 'portpkg':
						$type = 'application/vnd.macports.portpkg';
						break;
					case 'pot':
						$type = 'application/vnd.ms-powerpoint';
						break;
					case 'potm':
						$type = 'application/vnd.ms-powerpoint.template.macroenabled.12';
						break;
					case 'potx':
						$type = 'application/vnd.openxmlformats-officedocument.presentationml.template';
						break;
					case 'ppam':
						$type = 'application/vnd.ms-powerpoint.addin.macroenabled.12';
						break;
					case 'ppd':
						$type = 'application/vnd.cups-ppd';
						break;
					case 'ppm':
						$type = 'image/x-portable-pixmap';
						break;
					case 'pps':
						$type = 'application/vnd.ms-powerpoint';
						break;
					case 'ppsm':
						$type = 'application/vnd.ms-powerpoint.slideshow.macroenabled.12';
						break;
					case 'ppsx':
						$type = 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
						break;
					case 'ppt':
						$type = 'application/vnd.ms-powerpoint';
						break;
					case 'pptm':
						$type = 'application/vnd.ms-powerpoint.presentation.macroenabled.12';
						break;
					case 'pptx':
						$type = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
						break;
					case 'pqa':
						$type = 'application/vnd.palm';
						break;
					case 'prc':
						$type = 'application/x-mobipocket-ebook';
						break;
					case 'pre':
						$type = 'application/vnd.lotus-freelance';
						break;
					case 'prf':
						$type = 'application/pics-rules';
						break;
					case 'ps':
						$type = 'application/postscript';
						break;
					case 'psb':
						$type = 'application/vnd.3gpp.pic-bw-small';
						break;
					case 'psd':
						$type = 'image/vnd.adobe.photoshop';
						break;
					case 'psf':
						$type = 'application/x-font-linux-psf';
						break;
					case 'pskcxml':
						$type = 'application/pskc+xml';
						break;
					case 'ptid':
						$type = 'application/vnd.pvi.ptid1';
						break;
					case 'pub':
						$type = 'application/x-mspublisher';
						break;
					case 'pvb':
						$type = 'application/vnd.3gpp.pic-bw-var';
						break;
					case 'pwn':
						$type = 'application/vnd.3m.post-it-notes';
						break;
					case 'pya':
						$type = 'audio/vnd.ms-playready.media.pya';
						break;
					case 'pyv':
						$type = 'video/vnd.ms-playready.media.pyv';
						break;
					case 'qam':
						$type = 'application/vnd.epson.quickanime';
						break;
					case 'qbo':
						$type = 'application/vnd.intu.qbo';
						break;
					case 'qfx':
						$type = 'application/vnd.intu.qfx';
						break;
					case 'qps':
						$type = 'application/vnd.publishare-delta-tree';
						break;
					case 'qt':
						$type = 'video/quicktime';
						break;
					case 'qwd':
						$type = 'application/vnd.quark.quarkxpress';
						break;
					case 'qwt':
						$type = 'application/vnd.quark.quarkxpress';
						break;
					case 'qxb':
						$type = 'application/vnd.quark.quarkxpress';
						break;
					case 'qxd':
						$type = 'application/vnd.quark.quarkxpress';
						break;
					case 'qxl':
						$type = 'application/vnd.quark.quarkxpress';
						break;
					case 'qxt':
						$type = 'application/vnd.quark.quarkxpress';
						break;
					case 'ra':
						$type = 'audio/x-pn-realaudio';
						break;
					case 'ram':
						$type = 'audio/x-pn-realaudio';
						break;
					case 'rar':
						$type = 'application/x-rar-compressed';
						break;
					case 'ras':
						$type = 'image/x-cmu-raster';
						break;
					case 'rcprofile':
						$type = 'application/vnd.ipunplugged.rcprofile';
						break;
					case 'rdf':
						$type = 'application/rdf+xml';
						break;
					case 'rdz':
						$type = 'application/vnd.data-vision.rdz';
						break;
					case 'rep':
						$type = 'application/vnd.businessobjects';
						break;
					case 'res':
						$type = 'application/x-dtbresource+xml';
						break;
					case 'rgb':
						$type = 'image/x-rgb';
						break;
					case 'rif':
						$type = 'application/reginfo+xml';
						break;
					case 'rip':
						$type = 'audio/vnd.rip';
						break;
					case 'ris':
						$type = 'application/x-research-info-systems';
						break;
					case 'rl':
						$type = 'application/resource-lists+xml';
						break;
					case 'rlc':
						$type = 'image/vnd.fujixerox.edmics-rlc';
						break;
					case 'rld':
						$type = 'application/resource-lists-diff+xml';
						break;
					case 'rm':
						$type = 'application/vnd.rn-realmedia';
						break;
					case 'rmi':
						$type = 'audio/midi';
						break;
					case 'rmp':
						$type = 'audio/x-pn-realaudio-plugin';
						break;
					case 'rms':
						$type = 'application/vnd.jcp.javame.midlet-rms';
						break;
					case 'rmvb':
						$type = 'application/vnd.rn-realmedia-vbr';
						break;
					case 'rnc':
						$type = 'application/relax-ng-compact-syntax';
						break;
					case 'roa':
						$type = 'application/rpki-roa';
						break;
					case 'roff':
						$type = 'text/troff';
						break;
					case 'rp9':
						$type = 'application/vnd.cloanto.rp9';
						break;
					case 'rpss':
						$type = 'application/vnd.nokia.radio-presets';
						break;
					case 'rpst':
						$type = 'application/vnd.nokia.radio-preset';
						break;
					case 'rq':
						$type = 'application/sparql-query';
						break;
					case 'rs':
						$type = 'application/rls-services+xml';
						break;
					case 'rsd':
						$type = 'application/rsd+xml';
						break;
					case 'rss':
						$type = 'application/rss+xml';
						break;
					case 'rtf':
						$type = 'text/rtf';
						break;
					case 'rtx':
						$type = 'text/richtext';
						break;
					case 's':
						$type = 'text/x-asm';
						break;
					case 's3m':
						$type = 'audio/s3m';
						break;
					case 'saf':
						$type = 'application/vnd.yamaha.smaf-audio';
						break;
					case 'sbml':
						$type = 'application/sbml+xml';
						break;
					case 'sc':
						$type = 'application/vnd.ibm.secure-container';
						break;
					case 'scd':
						$type = 'application/x-msschedule';
						break;
					case 'scm':
						$type = 'application/vnd.lotus-screencam';
						break;
					case 'scq':
						$type = 'application/scvp-cv-request';
						break;
					case 'scs':
						$type = 'application/scvp-cv-response';
						break;
					case 'scurl':
						$type = 'text/vnd.curl.scurl';
						break;
					case 'sda':
						$type = 'application/vnd.stardivision.draw';
						break;
					case 'sdc':
						$type = 'application/vnd.stardivision.calc';
						break;
					case 'sdd':
						$type = 'application/vnd.stardivision.impress';
						break;
					case 'sdkd':
						$type = 'application/vnd.solent.sdkm+xml';
						break;
					case 'sdkm':
						$type = 'application/vnd.solent.sdkm+xml';
						break;
					case 'sdp':
						$type = 'application/sdp';
						break;
					case 'sdw':
						$type = 'application/vnd.stardivision.writer';
						break;
					case 'see':
						$type = 'application/vnd.seemail';
						break;
					case 'seed':
						$type = 'application/vnd.fdsn.seed';
						break;
					case 'sema':
						$type = 'application/vnd.sema';
						break;
					case 'semd':
						$type = 'application/vnd.semd';
						break;
					case 'semf':
						$type = 'application/vnd.semf';
						break;
					case 'ser':
						$type = 'application/java-serialized-object';
						break;
					case 'setpay':
						$type = 'application/set-payment-initiation';
						break;
					case 'setreg':
						$type = 'application/set-registration-initiation';
						break;
					case 'sfd-hdstx':
						$type = 'application/vnd.hydrostatix.sof-data';
						break;
					case 'sfs':
						$type = 'application/vnd.spotfire.sfs';
						break;
					case 'sfv':
						$type = 'text/x-sfv';
						break;
					case 'sgi':
						$type = 'image/sgi';
						break;
					case 'sgl':
						$type = 'application/vnd.stardivision.writer-global';
						break;
					case 'sgm':
						$type = 'text/sgml';
						break;
					case 'sgml':
						$type = 'text/sgml';
						break;
					case 'sh':
						$type = 'application/x-sh';
						break;
					case 'shar':
						$type = 'application/x-shar';
						break;
					case 'shf':
						$type = 'application/shf+xml';
						break;
					case 'sid':
						$type = 'image/x-mrsid-image';
						break;
					case 'sig':
						$type = 'application/pgp-signature';
						break;
					case 'sil':
						$type = 'audio/silk';
						break;
					case 'silo':
						$type = 'model/mesh';
						break;
					case 'sis':
						$type = 'application/vnd.symbian.install';
						break;
					case 'sisx':
						$type = 'application/vnd.symbian.install';
						break;
					case 'sit':
						$type = 'application/x-stuffit';
						break;
					case 'sitx':
						$type = 'application/x-stuffitx';
						break;
					case 'skd':
						$type = 'application/vnd.koan';
						break;
					case 'skm':
						$type = 'application/vnd.koan';
						break;
					case 'skp':
						$type = 'application/vnd.koan';
						break;
					case 'skt':
						$type = 'application/vnd.koan';
						break;
					case 'sldm':
						$type = 'application/vnd.ms-powerpoint.slide.macroenabled.12';
						break;
					case 'sldx':
						$type = 'application/vnd.openxmlformats-officedocument.presentationml.slide';
						break;
					case 'slt':
						$type = 'application/vnd.epson.salt';
						break;
					case 'sm':
						$type = 'application/vnd.stepmania.stepchart';
						break;
					case 'smf':
						$type = 'application/vnd.stardivision.math';
						break;
					case 'smi':
						$type = 'application/smil+xml';
						break;
					case 'smil':
						$type = 'application/smil+xml';
						break;
					case 'smv':
						$type = 'video/x-smv';
						break;
					case 'smzip':
						$type = 'application/vnd.stepmania.package';
						break;
					case 'snd':
						$type = 'audio/basic';
						break;
					case 'snf':
						$type = 'application/x-font-snf';
						break;
					case 'so':
						$type = 'application/octet-stream';
						break;
					case 'spc':
						$type = 'application/x-pkcs7-certificates';
						break;
					case 'spf':
						$type = 'application/vnd.yamaha.smaf-phrase';
						break;
					case 'spl':
						$type = 'application/x-futuresplash';
						break;
					case 'spot':
						$type = 'text/vnd.in3d.spot';
						break;
					case 'spp':
						$type = 'application/scvp-vp-response';
						break;
					case 'spq':
						$type = 'application/scvp-vp-request';
						break;
					case 'spx':
						$type = 'audio/ogg';
						break;
					case 'sql':
						$type = 'application/x-sql';
						break;
					case 'src':
						$type = 'application/x-wais-source';
						break;
					case 'srt':
						$type = 'application/x-subrip';
						break;
					case 'sru':
						$type = 'application/sru+xml';
						break;
					case 'srx':
						$type = 'application/sparql-results+xml';
						break;
					case 'ssdl':
						$type = 'application/ssdl+xml';
						break;
					case 'sse':
						$type = 'application/vnd.kodak-descriptor';
						break;
					case 'ssf':
						$type = 'application/vnd.epson.ssf';
						break;
					case 'ssml':
						$type = 'application/ssml+xml';
						break;
					case 'st':
						$type = 'application/vnd.sailingtracker.track';
						break;
					case 'stc':
						$type = 'application/vnd.sun.xml.calc.template';
						break;
					case 'std':
						$type = 'application/vnd.sun.xml.draw.template';
						break;
					case 'stf':
						$type = 'application/vnd.wt.stf';
						break;
					case 'sti':
						$type = 'application/vnd.sun.xml.impress.template';
						break;
					case 'stk':
						$type = 'application/hyperstudio';
						break;
					case 'stl':
						$type = 'application/vnd.ms-pki.stl';
						break;
					case 'str':
						$type = 'application/vnd.pg.format';
						break;
					case 'stw':
						$type = 'application/vnd.sun.xml.writer.template';
						break;
					case 'sub':
						$type = 'text/vnd.dvb.subtitle';
						break;
					case 'sus':
						$type = 'application/vnd.sus-calendar';
						break;
					case 'susp':
						$type = 'application/vnd.sus-calendar';
						break;
					case 'sv4cpio':
						$type = 'application/x-sv4cpio';
						break;
					case 'sv4crc':
						$type = 'application/x-sv4crc';
						break;
					case 'svc':
						$type = 'application/vnd.dvb.service';
						break;
					case 'svd':
						$type = 'application/vnd.svd';
						break;
					case 'svg':
						$type = 'image/svg+xml';
						break;
					case 'svgz':
						$type = 'image/svg+xml';
						break;
					case 'swa':
						$type = 'application/x-director';
						break;
					case 'swf':
						$type = 'application/x-shockwave-flash';
						break;
					case 'swi':
						$type = 'application/vnd.aristanetworks.swi';
						break;
					case 'sxc':
						$type = 'application/vnd.sun.xml.calc';
						break;
					case 'sxd':
						$type = 'application/vnd.sun.xml.draw';
						break;
					case 'sxg':
						$type = 'application/vnd.sun.xml.writer.global';
						break;
					case 'sxi':
						$type = 'application/vnd.sun.xml.impress';
						break;
					case 'sxm':
						$type = 'application/vnd.sun.xml.math';
						break;
					case 'sxw':
						$type = 'application/vnd.sun.xml.writer';
						break;
					case 't':
						$type = 'text/troff';
						break;
					case 't3':
						$type = 'application/x-t3vm-image';
						break;
					case 'taglet':
						$type = 'application/vnd.mynfc';
						break;
					case 'tao':
						$type = 'application/vnd.tao.intent-module-archive';
						break;
					case 'tar':
						$type = 'application/x-tar';
						break;
					case 'tcap':
						$type = 'application/vnd.3gpp2.tcap';
						break;
					case 'tcl':
						$type = 'application/x-tcl';
						break;
					case 'teacher':
						$type = 'application/vnd.smart.teacher';
						break;
					case 'tei':
						$type = 'application/tei+xml';
						break;
					case 'teicorpus':
						$type = 'application/tei+xml';
						break;
					case 'tex':
						$type = 'application/x-tex';
						break;
					case 'texi':
						$type = 'application/x-texinfo';
						break;
					case 'texinfo':
						$type = 'application/x-texinfo';
						break;
					case 'text':
						$type = 'text/plain';
						break;
					case 'tfi':
						$type = 'application/thraud+xml';
						break;
					case 'tfm':
						$type = 'application/x-tex-tfm';
						break;
					case 'tga':
						$type = 'image/x-tga';
						break;
					case 'thmx':
						$type = 'application/vnd.ms-officetheme';
						break;
					case 'tif':
						$type = 'image/tiff';
						break;
					case 'tiff':
						$type = 'image/tiff';
						break;
					case 'tmo':
						$type = 'application/vnd.tmobile-livetv';
						break;
					case 'torrent':
						$type = 'application/x-bittorrent';
						break;
					case 'tpl':
						$type = 'application/vnd.groove-tool-template';
						break;
					case 'tpt':
						$type = 'application/vnd.trid.tpt';
						break;
					case 'tr':
						$type = 'text/troff';
						break;
					case 'tra':
						$type = 'application/vnd.trueapp';
						break;
					case 'trm':
						$type = 'application/x-msterminal';
						break;
					case 'ts':
						$type = 'video/MP2T';
						break;
					case 'tsd':
						$type = 'application/timestamped-data';
						break;
					case 'tsv':
						$type = 'text/tab-separated-values';
						break;
					case 'ttc':
						$type = 'application/x-font-ttf';
						break;
					case 'ttf':
						$type = 'application/x-font-ttf';
						break;
					case 'ttl':
						$type = 'text/turtle';
						break;
					case 'twd':
						$type = 'application/vnd.simtech-mindmapper';
						break;
					case 'twds':
						$type = 'application/vnd.simtech-mindmapper';
						break;
					case 'txd':
						$type = 'application/vnd.genomatix.tuxedo';
						break;
					case 'txf':
						$type = 'application/vnd.mobius.txf';
						break;
					case 'txt':
						$type = 'text/plain';
						break;
					case 'u32':
						$type = 'application/x-authorware-bin';
						break;
					case 'udeb':
						$type = 'application/x-debian-package';
						break;
					case 'ufd':
						$type = 'application/vnd.ufdl';
						break;
					case 'ufdl':
						$type = 'application/vnd.ufdl';
						break;
					case 'ulx':
						$type = 'application/x-glulx';
						break;
					case 'umj':
						$type = 'application/vnd.umajin';
						break;
					case 'unityweb':
						$type = 'application/vnd.unity';
						break;
					case 'uoml':
						$type = 'application/vnd.uoml+xml';
						break;
					case 'uri':
						$type = 'text/uri-list';
						break;
					case 'uris':
						$type = 'text/uri-list';
						break;
					case 'urls':
						$type = 'text/uri-list';
						break;
					case 'ustar':
						$type = 'application/x-ustar';
						break;
					case 'utz':
						$type = 'application/vnd.uiq.theme';
						break;
					case 'uu':
						$type = 'text/x-uuencode';
						break;
					case 'uva':
						$type = 'audio/vnd.dece.audio';
						break;
					case 'uvd':
						$type = 'application/vnd.dece.data';
						break;
					case 'uvf':
						$type = 'application/vnd.dece.data';
						break;
					case 'uvg':
						$type = 'image/vnd.dece.graphic';
						break;
					case 'uvh':
						$type = 'video/vnd.dece.hd';
						break;
					case 'uvi':
						$type = 'image/vnd.dece.graphic';
						break;
					case 'uvm':
						$type = 'video/vnd.dece.mobile';
						break;
					case 'uvp':
						$type = 'video/vnd.dece.pd';
						break;
					case 'uvs':
						$type = 'video/vnd.dece.sd';
						break;
					case 'uvt':
						$type = 'application/vnd.dece.ttml+xml';
						break;
					case 'uvu':
						$type = 'video/vnd.uvvu.mp4';
						break;
					case 'uvv':
						$type = 'video/vnd.dece.video';
						break;
					case 'uvva':
						$type = 'audio/vnd.dece.audio';
						break;
					case 'uvvd':
						$type = 'application/vnd.dece.data';
						break;
					case 'uvvf':
						$type = 'application/vnd.dece.data';
						break;
					case 'uvvg':
						$type = 'image/vnd.dece.graphic';
						break;
					case 'uvvh':
						$type = 'video/vnd.dece.hd';
						break;
					case 'uvvi':
						$type = 'image/vnd.dece.graphic';
						break;
					case 'uvvm':
						$type = 'video/vnd.dece.mobile';
						break;
					case 'uvvp':
						$type = 'video/vnd.dece.pd';
						break;
					case 'uvvs':
						$type = 'video/vnd.dece.sd';
						break;
					case 'uvvt':
						$type = 'application/vnd.dece.ttml+xml';
						break;
					case 'uvvu':
						$type = 'video/vnd.uvvu.mp4';
						break;
					case 'uvvv':
						$type = 'video/vnd.dece.video';
						break;
					case 'uvvx':
						$type = 'application/vnd.dece.unspecified';
						break;
					case 'uvvz':
						$type = 'application/vnd.dece.zip';
						break;
					case 'uvx':
						$type = 'application/vnd.dece.unspecified';
						break;
					case 'uvz':
						$type = 'application/vnd.dece.zip';
						break;
					case 'vcard':
						$type = 'text/vcard';
						break;
					case 'vcd':
						$type = 'application/x-cdlink';
						break;
					case 'vcf':
						$type = 'text/x-vcard';
						break;
					case 'vcg':
						$type = 'application/vnd.groove-vcard';
						break;
					case 'vcs':
						$type = 'text/x-vcalendar';
						break;
					case 'vcx':
						$type = 'application/vnd.vcx';
						break;
					case 'vis':
						$type = 'application/vnd.visionary';
						break;
					case 'viv':
						$type = 'video/vnd.vivo';
						break;
					case 'vob':
						$type = 'video/x-ms-vob';
						break;
					case 'vor':
						$type = 'application/vnd.stardivision.writer';
						break;
					case 'vox':
						$type = 'application/x-authorware-bin';
						break;
					case 'vrml':
						$type = 'model/vrml';
						break;
					case 'vsd':
						$type = 'application/vnd.visio';
						break;
					case 'vsf':
						$type = 'application/vnd.vsf';
						break;
					case 'vss':
						$type = 'application/vnd.visio';
						break;
					case 'vst':
						$type = 'application/vnd.visio';
						break;
					case 'vsw':
						$type = 'application/vnd.visio';
						break;
					case 'vtt':
						$type = 'text/vtt';
						break;
					case 'vtu':
						$type = 'model/vnd.vtu';
						break;
					case 'vxml':
						$type = 'application/voicexml+xml';
						break;
					case 'w3d':
						$type = 'application/x-director';
						break;
					case 'wad':
						$type = 'application/x-doom';
						break;
					case 'wav':
						$type = 'audio/x-wav';
						break;
					case 'wax':
						$type = 'audio/x-ms-wax';
						break;
					case 'wbmp':
						$type = 'image/vnd.wap.wbmp';
						break;
					case 'wbs':
						$type = 'application/vnd.criticaltools.wbs+xml';
						break;
					case 'wbxml':
						$type = 'application/vnd.wap.wbxml';
						break;
					case 'wcm':
						$type = 'application/vnd.ms-works';
						break;
					case 'wdb':
						$type = 'application/vnd.ms-works';
						break;
					case 'wdp':
						$type = 'image/vnd.ms-photo';
						break;
					case 'weba':
						$type = 'audio/webm';
						break;
					case 'webapp':
						$type = 'application/x-web-app-manifest+json';
						break;
					case 'webm':
						$type = 'video/webm';
						break;
					case 'webp':
						$type = 'image/webp';
						break;
					case 'wg':
						$type = 'application/vnd.pmi.widget';
						break;
					case 'wgt':
						$type = 'application/widget';
						break;
					case 'wks':
						$type = 'application/vnd.ms-works';
						break;
					case 'wm':
						$type = 'video/x-ms-wm';
						break;
					case 'wma':
						$type = 'audio/x-ms-wma';
						break;
					case 'wmd':
						$type = 'application/x-ms-wmd';
						break;
					case 'wmf':
						$type = 'application/x-msmetafile';
						break;
					case 'wml':
						$type = 'text/vnd.wap.wml';
						break;
					case 'wmlc':
						$type = 'application/vnd.wap.wmlc';
						break;
					case 'wmls':
						$type = 'text/vnd.wap.wmlscript';
						break;
					case 'wmlsc':
						$type = 'application/vnd.wap.wmlscriptc';
						break;
					case 'wmv':
						$type = 'video/x-ms-wmv';
						break;
					case 'wmx':
						$type = 'video/x-ms-wmx';
						break;
					case 'wmz':
						$type = 'application/x-msmetafile';
						break;
					case 'woff':
						$type = 'application/x-font-woff';
						break;
					case 'wpd':
						$type = 'application/vnd.wordperfect';
						break;
					case 'wpl':
						$type = 'application/vnd.ms-wpl';
						break;
					case 'wps':
						$type = 'application/vnd.ms-works';
						break;
					case 'wqd':
						$type = 'application/vnd.wqd';
						break;
					case 'wri':
						$type = 'application/x-mswrite';
						break;
					case 'wrl':
						$type = 'model/vrml';
						break;
					case 'wsdl':
						$type = 'application/wsdl+xml';
						break;
					case 'wspolicy':
						$type = 'application/wspolicy+xml';
						break;
					case 'wtb':
						$type = 'application/vnd.webturbo';
						break;
					case 'wvx':
						$type = 'video/x-ms-wvx';
						break;
					case 'x32':
						$type = 'application/x-authorware-bin';
						break;
					case 'x3d':
						$type = 'model/x3d+xml';
						break;
					case 'x3db':
						$type = 'model/x3d+binary';
						break;
					case 'x3dbz':
						$type = 'model/x3d+binary';
						break;
					case 'x3dv':
						$type = 'model/x3d+vrml';
						break;
					case 'x3dvz':
						$type = 'model/x3d+vrml';
						break;
					case 'x3dz':
						$type = 'model/x3d+xml';
						break;
					case 'xaml':
						$type = 'application/xaml+xml';
						break;
					case 'xap':
						$type = 'application/x-silverlight-app';
						break;
					case 'xar':
						$type = 'application/vnd.xara';
						break;
					case 'xbap':
						$type = 'application/x-ms-xbap';
						break;
					case 'xbd':
						$type = 'application/vnd.fujixerox.docuworks.binder';
						break;
					case 'xbm':
						$type = 'image/x-xbitmap';
						break;
					case 'xdf':
						$type = 'application/xcap-diff+xml';
						break;
					case 'xdm':
						$type = 'application/vnd.syncml.dm+xml';
						break;
					case 'xdp':
						$type = 'application/vnd.adobe.xdp+xml';
						break;
					case 'xdssc':
						$type = 'application/dssc+xml';
						break;
					case 'xdw':
						$type = 'application/vnd.fujixerox.docuworks';
						break;
					case 'xenc':
						$type = 'application/xenc+xml';
						break;
					case 'xer':
						$type = 'application/patch-ops-error+xml';
						break;
					case 'xfdf':
						$type = 'application/vnd.adobe.xfdf';
						break;
					case 'xfdl':
						$type = 'application/vnd.xfdl';
						break;
					case 'xht':
						$type = 'application/xhtml+xml';
						break;
					case 'xhtml':
						$type = 'application/xhtml+xml';
						break;
					case 'xhvml':
						$type = 'application/xv+xml';
						break;
					case 'xif':
						$type = 'image/vnd.xiff';
						break;
					case 'xla':
						$type = 'application/vnd.ms-excel';
						break;
					case 'xlam':
						$type = 'application/vnd.ms-excel.addin.macroenabled.12';
						break;
					case 'xlc':
						$type = 'application/vnd.ms-excel';
						break;
					case 'xlf':
						$type = 'application/x-xliff+xml';
						break;
					case 'xlm':
						$type = 'application/vnd.ms-excel';
						break;
					case 'xls':
						$type = 'application/vnd.ms-excel';
						break;
					case 'xlsb':
						$type = 'application/vnd.ms-excel.sheet.binary.macroenabled.12';
						break;
					case 'xlsm':
						$type = 'application/vnd.ms-excel.sheet.macroenabled.12';
						break;
					case 'xlsx':
						$type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
						break;
					case 'xlt':
						$type = 'application/vnd.ms-excel';
						break;
					case 'xltm':
						$type = 'application/vnd.ms-excel.template.macroenabled.12';
						break;
					case 'xltx':
						$type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
						break;
					case 'xlw':
						$type = 'application/vnd.ms-excel';
						break;
					case 'xm':
						$type = 'audio/xm';
						break;
					case 'xml':
						$type = 'application/xml';
						break;
					case 'xo':
						$type = 'application/vnd.olpc-sugar';
						break;
					case 'xop':
						$type = 'application/xop+xml';
						break;
					case 'xpi':
						$type = 'application/x-xpinstall';
						break;
					case 'xpl':
						$type = 'application/xproc+xml';
						break;
					case 'xpm':
						$type = 'image/x-xpixmap';
						break;
					case 'xpr':
						$type = 'application/vnd.is-xpr';
						break;
					case 'xps':
						$type = 'application/vnd.ms-xpsdocument';
						break;
					case 'xpw':
						$type = 'application/vnd.intercon.formnet';
						break;
					case 'xpx':
						$type = 'application/vnd.intercon.formnet';
						break;
					case 'xsl':
						$type = 'application/xml';
						break;
					case 'xslt':
						$type = 'application/xslt+xml';
						break;
					case 'xsm':
						$type = 'application/vnd.syncml+xml';
						break;
					case 'xspf':
						$type = 'application/xspf+xml';
						break;
					case 'xul':
						$type = 'application/vnd.mozilla.xul+xml';
						break;
					case 'xvm':
						$type = 'application/xv+xml';
						break;
					case 'xvml':
						$type = 'application/xv+xml';
						break;
					case 'xwd':
						$type = 'image/x-xwindowdump';
						break;
					case 'xyz':
						$type = 'chemical/x-xyz';
						break;
					case 'xz':
						$type = 'application/x-xz';
						break;
					case 'yang':
						$type = 'application/yang';
						break;
					case 'yin':
						$type = 'application/yin+xml';
						break;
					case 'z1':
						$type = 'application/x-zmachine';
						break;
					case 'z2':
						$type = 'application/x-zmachine';
						break;
					case 'z3':
						$type = 'application/x-zmachine';
						break;
					case 'z4':
						$type = 'application/x-zmachine';
						break;
					case 'z5':
						$type = 'application/x-zmachine';
						break;
					case 'z6':
						$type = 'application/x-zmachine';
						break;
					case 'z7':
						$type = 'application/x-zmachine';
						break;
					case 'z8':
						$type = 'application/x-zmachine';
						break;
					case 'zaz':
						$type = 'application/vnd.zzazz.deck+xml';
						break;
					case 'zip':
						$type = 'application/zip'; // This shouldn't happen. TODO: Put error for zip files
						break;
					case 'zir':
						$type = 'application/vnd.zul';
						break;
					case 'zirz':
						$type = 'application/vnd.zul';
						break;
					case 'zmm':
						$type = 'application/vnd.handheld-entertainment+xml';
						break;
		            case '':
		                //error_log(__METHOD__ . '() no file extension: ' . $filename, 0);
		                return;
		            default:
		                $type = 'application/octet-stream'; 
		                break;
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
            $templateFiles = is_array($templateFiles) ? $templateFiles : array();
            
            // Download files
            foreach($templateFiles as $key=>$file){
	            $templateFiles[$key] = $this->save_file(basename($key), $file);
            }
            
            return $templateFiles;
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
    		$URL = parse_url($URL, PHP_URL_QUERY) ? $URL . "&bundlr=true&width=$width" : $URL . "?bundlr=true&width=$width";
    		
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
        private function get_linked_assets_from_html($htmlStr, $template) {
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
	            	$relative = (strpos($image->src, "//") === false) ? true : false;
	            	
	            	if($relative){
		            	$path = $this->get_asset_relative($image->src, $template);
						$assets[$image->src] = $this->save_file($image->src, $path);
	            	}else{
						$path = $this->get_asset_path($image->src);
						$path = ltrim($path, '/');
						$assets[$path] = $this->save_file(basename($path), $image->src);
						$image->src = $path;
	            	}
                }
            }
            
            // update audio / video source to local assets folder
            foreach($mediaSources as $media) {
                if( isset($media->src) && pathinfo($media->src, PATHINFO_EXTENSION)){
	                $relative = (strpos($media->src, "//") === false) ? true : false;
	            	
	            	if($relative){
		            	$path = $this->get_asset_relative($media->src, $template);
						$path = ltrim($path, '/');
						$assets[$media->src] = $this->save_file($media->src, $path);
	            	}else{
	               		$path = $this->get_asset_path($media->src);
						$path = ltrim($path, '/');
				   		$assets[$path] = $this->save_file(basename($path), $media->src);
				   		$media->src = $path;
	            	}
                }
            }
            
            // update css to local assets folder
            foreach($styles as $style) {
                if( isset($style->href) && $style->rel == 'stylesheet' && pathinfo($style->href, PATHINFO_EXTENSION)){
	                $relative = (strpos($style->href, "//") === false) ? true : false;
	            	
	            	if($relative){
		            	$path = $this->get_asset_relative($style->href, $template);
						$path = ltrim($path, '/');
						$assets[$style->href] = $this->save_file($style->href, $path);
	            	}else{
	               		$path = $this->get_asset_path($style->href);
						$path = ltrim($path, '/');
				   		$assets[$path] = $this->save_file(basename($path), $style->href);
				   		$style->href = $path;
	            	}
                }
            }
    
            // update javascript to local assets folder
            foreach($scripts as $script) {
                if( isset($script->src) && pathinfo($script->src, PATHINFO_EXTENSION)){
	                $relative = (strpos($script->src, "//") === false) ? true : false;
	            	
	            	if($relative){
		            	$path = $this->get_asset_relative($script->src, $template);
						$path = ltrim($path, '/');
						$assets[$script->src] = $this->save_file($script->src, $path);
	            	}else{
	               		$path = $this->get_asset_path($script->src);
						$path = ltrim($path, '/');
				   		$assets[$path] = $this->save_file(basename($path), $script->src);
				   		$script->src = $path;
	            	}
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
	    	$url = $this->make_absolute($url, "");
			$url_parts = parse_url($url);
			$path = isset($url_parts["path"]) ? $url_parts["path"] : "";
	        return $path;
        }
        
        private function get_asset_relative($url, $template){
	        $templatePath = pathinfo($template);
		    $path = $templatePath['dirname'] . "/" . ltrim($url, '/');
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