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
	 * construct
	 * */
	public function __construct($server){
		$this->server = $server;
		$this->method = $this->server['REQUEST_METHOD'];
	}
	
	/**
	 * Validate the post parameters
	 *
	 * @param stdclass object $params Object that contains POST params
	 * @return Boolean
	 */
	public function validate_params($params){

		if($this->method == "POST") {

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
