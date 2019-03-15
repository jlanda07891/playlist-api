<?php

/**
* Include required classes
*
* ./config/request.php contains methods to return HTTP responses with a message / parse and validate the GET/POST params
* ./controller/playlist.php is a controller that contains functions to check required params and call methods from Playlist Model
 */
include_once './config/request.php';
include_once "./controller/playlist.php";

// create a new instance of Request
$request = new Request($_SERVER);
$method  = $request->getMethod();

// store the get parameters
$get_params	= $_GET;

// api is called with POST method
if($method=="POST"){
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");
	header("Access-Control-Allow-Methods: POST");
	header("Access-Control-Max-Age: 3600");
	header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

	// store and test the POST params
	$post_params 	= json_decode(file_get_contents("php://input"));
	// stop if something is wrong with parameters
	if(!$request->validate_params($post_params)) exit(1);
}

// create a new instance of PlaylistController
$controller = new PlaylistController($get_params, $post_params);

$action 	= $get_params['action']; 
$playlist_id 	= $get_params['id']; 

// dispatch to the controller, based on the action required in url
switch($action){
	case "createplaylist":
		$controller->create();
		break;
	case "updateplaylist":
		$controller->update();
		break;
	case "deleteplaylist":
		$controller->delete();
		break;
	case "addtoplaylist":
		$controller->add_video();
		break;
	case "moveinplaylist":
		$controller->move_video();
		break;
	case "removefromplaylist":
		$controller->remove_video();
		break;
	case "getplaylist":
		$controller->getPlaylist();
		break;
	case "getvideos":
		$controller->getAllVideos();
		break;
	case "getplaylistvideos":
		$controller->getPlaylistVideos();
		break;
	default:
		$request->response_message(404, ["message" => "please request a known action"]);
		break;
}
