<?php

// include the request parser
include_once './config/request.php';

include_once "./controller/playlist.php";

// init request parser object
$request = new Request($_SERVER);
$method  = $request->getMethod();

$get_params	= $_GET;
// todo check that
$action 	= $get_params['action']; 
$playlist_id 	= $get_params['id']; 

if($method=="POST"){
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");
	header("Access-Control-Allow-Methods: POST");
	header("Access-Control-Max-Age: 3600");
	header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

	// todo check all params in Util
	$post_params 	= json_decode(file_get_contents("php://input"));
}

$controller = new PlaylistController($get_params, $post_params);

switch($action){
	case "createplaylist":
		$controller->create();
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
	case "getplaylists":
		$controller->getAllPlaylists();
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
