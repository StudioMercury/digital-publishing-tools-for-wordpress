<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Curl
 * Description: This class handles the HTTPS requests for interacting with the Adobe API.
 */
 
namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Curl')) { 
    
    class Curl {
	    
	    // global HTTPS request values
		private $curl;
		private $curl_http_code;
		private $curl_options;
		private $curl_response;
		private $curl_response_header;
		private $curl_response_body;
		private $curl_response_size;
		private $file_path;
		private $request_data;
		private $request_method;
		private $request_header;
		private $request_url;
		private $verbose;
		
	    public function __construct($request_method, $request_url, $request_headers = null, $request_data = null, $isFile = FALSE) {
			if ($request_data !== null) {
				if ($isFile) { // generates file data
					$this->file_path = $request_data;
				} else { // store request data parameters
					$this->request_data = $request_data;
				}
			}
			$this->request_header = $request_headers;
			$this->request_method = $request_method;
			$this->request_url = $request_url;
			$this->setOptions();
			$this->exec();
		}
				
		public function updateRequestData($request_data){
			$this->request_data = $request_data;
		}
		
		public function updateRequestHeader($request_headers){
			$this->request_header = $request_headers;
		}
		
		/**
		 * This method will return the entity content version from the API response.
		 * @return {String} $content_version - The entity's content version
		 */
		public function getContentVersion() {
			$data = $this->curl_response_body;
			if (!isset($data['_links']) || !isset($data['_links']['contentUrl']))
				return null;
			$content_url = $data['_links']['contentUrl']['href'];
			$index = strrpos($content_url, '=');
			$content_version = substr($content_url, $index + 1, -1);
			return $content_version;
		}
		
		/**
		 * This method will return the response HTTP code.
		 * @return {Int} curl_http_code - The cURL request HTTP code
		 */
		public function getHTTPCode() {
			return $this->curl_http_code;
		}
		
		/**
		 * This method will return the API request header.
		 * @return {String} $curl_options - The API request header
		 */
		public function getRequestHeader() {
			$curl_options = array(
				'CURLOPT_CUSTOMREQUEST' => $this->request_method,
				'CURLOPT_HTTPHEADER' => $this->request_header
			);
			if ($this->request_data !== null)
				$curl_options['CURLOPT_POSTFIELDS'] = $this->request_data;
			else if ($this->file_path !== null)
				$curl_options['CURLOPT_INFILE'] = $this->file_path;
			return $curl_options;
		}
		
		/**
		 * This method will return the API reesponse header.
		 * @return {String} $curl_response_header - The API response header
		 */
		public function getResponseHeader() {
			return $this->curl_response_header;
		}
		
		public function getRequestUrl() {
			return $this->request_url;
		}
	
		/**
		 * This method will return the API reesponse body.
		 * @return {String} $curl_response_body - The API response body
		 */
		public function getResponseBody() {
			return $this->curl_response_body;
		}
	
		/**
		 * This method will return the API reesponse debug statement.
		 * @return {String} $verbose - The API response debug
		 */
		public function getResponseVerbose() {
			return $this->verbose;
		}
	
		/**
		 * This method will return the entity version from the API response.
		 * @return {String} $version_id - The entity's version
		 */
		public function getVersionId() {
			$data = $this->curl_response_body;
			$version_id = null;
			if (isset($data['message']))
				$index = strrpos($data['message'], 'currentVersion=');
				$version_id = substr($data['message'], $index + strlen('currentVersion='));
			return $version_id;
		}
		
		/**
		 * This method will initialize and execute the HTTPS request.
		 */
		public function exec() {
			// initialize the cURL
			$this->curl = curl_init();
			// initialize cURL parameters
			curl_setopt_array($this->curl, $this->curl_options);
			// execute cURL request
			$this->curl_response = curl_exec($this->curl);
			// get the response HTTP code
			$this->curl_http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
			// get the response header size
			$this->curl_response_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
			// call helper to parse & store the response header and body
			$this->formatResponse();
			
			if(curl_errno($this->curl)){
				$error = new Error("Error", curl_errno($this->curl));
				$error = new Error("Error", 300);
				$error->setTitle('Unable to send a request to the Adobe API');
				$error->setMessage(curl_error($this->curl));
				throw $error;
			}
			
			// close cURL request
			curl_close($this->curl);
		}
		
		/**
		 * Helper.
		 * This method will format and separate the response header and body:
		 * 1. if response body is JSON, decode and store it as JSON
		 * 2. if response body is XML, store it as XML
		 */
		private function formatResponse() {
			// store the response header
			$this->curl_response_header = substr($this->curl_response, 0, $this->curl_response_size);
			$response_body = substr($this->curl_response, $this->curl_response_size);
			if (strpos($response_body, '<?xml') === 0) // stores response body as XML
				$this->curl_response_body = $response_body;
			else // store response body as JSON
				$this->curl_response_body = json_decode($response_body, true);
		}
		
		private function setOptions() {
			$this->verbose = fopen('php://temp', 'rw+');
			$this->curl_options[CURLOPT_CUSTOMREQUEST] = $this->request_method;
			$this->curl_options[CURLOPT_HEADER] = true;
			$this->curl_options[CURLOPT_RETURNTRANSFER] = true;
			$this->curl_options[CURLOPT_STDERR] = $this->verbose;
			$this->curl_options[CURLOPT_URL] = $this->request_url;
			$this->curl_options[CURLOPT_USERAGENT] = $_SERVER['HTTP_USER_AGENT'];
			$this->curl_options[CURLOPT_VERBOSE] = true;
			
			// set the request header, optional
			if ($this->request_header !== null) {
				$this->curl_options[CURLOPT_HTTPHEADER] = $this->request_header;
			}
			
			// set the request data
			if ($this->request_data !== null) { 
				// append JSON data
				$this->curl_options[CURLOPT_POSTFIELDS] = json_encode($this->request_data);
			} else if ($this->file_path !== null) { 
				// append file data
				$this->curl_options[CURLOPT_INFILE] = fopen($this->file_path, 'r');
				$this->curl_options[CURLOPT_INFILESIZE] = filesize($this->file_path);
				$this->curl_options[CURLOPT_UPLOAD] = true;
			}
		}
		
		/**
		 * This method will print the API response in a human-readable format.
		 *
		 * @param {String} $request_name - The name of the API request
		 * @param {Boolean} $show_input - Toggler, whether to show the user input
		 * @param {Boolean} $show_debug - Toggler, whether to show the debug value
		 */
		public function printCurlData($request_name = "", $show_input = true, $show_debug = false) {
				echo '<pre>';
				echo '<h2>';
				echo ($request_name);
				echo '</h2>';
			if ($show_input) {
				echo '<h3>Input Data:</h3>';
				print_r($this->getRequestHeader());
				echo '<h3>URL</h3>';
				print_r($this->getRequestUrl());
				echo '<h3>Response Header:</h3>';
				print_r($this->getResponseHeader());
				echo '<h3>Response Body:</h3>';
			}
				print_r($this->getResponseBody());
			if ($show_debug) {
				echo '<h3>Verbose information:</h3>';
				echo !rewind($this->getResponseVerbose());
				echo htmlspecialchars(stream_get_contents($this->getResponseVerbose()));
			}
				echo '</pre>';
			return $this;
		}
			        
    } // END class Curl 
}