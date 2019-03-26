<?php

/**
 * class Request
 *
 * contains methods to return HTTP responses with a message
 * parse and validate the GET/POST params
 */
class Request {
	
	/**
 	* private properties
 	*/
	private $method;
	private $request;
	private $input;
	private $server;

	/**
 	* const to store valid regex for each parameter
 	*/
	const PARAMS_REGEX = array(
		'id' 		=> '/^\d+$/',
		'name' 		=> '/^\w+$/',
		'action' 	=> '/^\w+$/',
		'id_playlist' 	=> '/^\d+$/',
		'id_video' 	=> '/^\d+$/',
		'placement' 	=> '/^\d+$/'
	);

	/**
 	* const to store authorized HTTP methods
 	*/
	const HTTP_METHODS = array(
		'POST',
		'PUT',
		'DELETE'
	);
	
	/**
 	* const to store known URL params and possible values
 	*/
	const URI_PARAMS = array(
		'ressource1' 	=> ['playlists','videos'],
		'ressource2' 	=> ['videos'],
		'playlist_id' 	=> [],
		'video_id' 	=> []
	);

	/**
	 * construct
	 * */
	public function __construct($server, $get_params=null){
		$this->server = $server;
		$this->method = $this->server['REQUEST_METHOD'];
		$this->get_params = $get_params;
	}

	/**
	 * Return a specific GET parameter
	 *
	 * @param string $param name of the parameter to extract
	 * @return String
	 */
	public function get_param($param){
		return str_replace("/","",$this->get_params[$param]); 
	}

	/**
	 * Validate the URI parameters
	 * @return Boolean
	 */
	public function validate_uri(){
		foreach($this->get_params as $param_name => $param_value){
			// extract the parameter's value
			$param_value = $this->get_param($param_name);

			if(empty($param_value)) continue;

			// the ressource's name requested in URI is unknown
			if( !array_key_exists($param_name, self::URI_PARAMS) ){
				$this->response_message(404, ["message" => sprintf("parameter '%s' in URI is unknown",$param_name)]);
				return false;
			}
			// the value of the ressource is unknown
			if( count(self::URI_PARAMS[$param_name]) and !in_array($param_value, self::URI_PARAMS[$param_name]) ){
				$this->response_message(404, ["message" => sprintf("possible values for URI parameter '%s' are %s",$param_name,implode(",",self::URI_PARAMS[$param_name]))]);
				return false;
			}
		}
		return true;
	}

	/**
	 * Validate the POST parameters
	 *
	 * @param stdclass object $params Object that contains POST params
	 * @return Boolean
	 */
	public function validate_params($params){

		if($params){
			if(in_array($this->method,self::HTTP_METHODS)) {
		
				// extract keys from parameters
				$param_keys = array_keys(get_object_vars($params));

				// foreach key
				foreach($param_keys as $key){
					// if this parameter is allowed 
					if(isset(self::PARAMS_REGEX[$key])){

						// get the value of this parameter
						$value = $params->$key;

						// if the parameter is not empty
						if(!empty($value)) {
							// if the parameter pattern is correct
							if(preg_match( self::PARAMS_REGEX[$key], $value )) continue;
							else {
								$this->response_message(404, ["message" => "bad pattern for param $key"]);
								return false;
							}
						}
						else {
							$this->response_message(404, ["message" => "param $key cannot be empty"]);
							return false;
						}
					}
					else {
						$this->response_message(404, ["message" => "param $key not allowed"]);
						return false;
					}
				}
			}
			else {
				$this->response_message(404, ["message" => sprintf("%s requests not supported",$this->method)]);
				return false;
			}
		}
		return true;
	}

	/**
	 * getter for method
	 * @return string
	 */
	public function getMethod(){
		return $this->method;
	}

	/**
	 * Return an HTTP Response with a json message
	 * @return json_encode
	 */
	public function response_message($http_code, $content){
		http_response_code($http_code);
		echo json_encode($content);
	}
}
