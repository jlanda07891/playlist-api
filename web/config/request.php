<?php

class Request {

	private $method;
	private $request;
	private $input;
	private $server;

	public function __construct($server){
		$this->server = $server;
		$this->method = $this->server['REQUEST_METHOD'];
		$this->request= explode('/', trim($_SERVER['PATH_INFO'],'/'));
		//$this->input  = json_decode(file_get_contents('php://input'),true);
		$this->input  = file_get_contents('php://input');
	}

	// method getter
	public function getMethod(){
		return $this->method;
	}

	// request getter
	public function getRequest(){
		return $this->request;
	}

	// input getter
	public function getInput(){
		return $this->input;
	}

	public function response_message($http_code, $content){
		http_response_code($http_code);
		echo json_encode($content);
	}
}
