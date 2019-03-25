<?php

/**
* Include required classes
*
* ./config/request.php contains methods to return HTTP responses with a message / parse and validate the GET/POST params
* ./controller/playlist.php is a controller that contains functions to check required params and call methods from Playlist Model
 */
include_once './lib/request.php';
include_once "./controller/playlist.php";

// create a new instance of Request
$request = new Request($_SERVER, $_GET);
$method  = $request->getMethod();

// TODO EXPLAIN
if($method == "GET"){
	header('Content-Type: text/plain');
}
// api is called with POST/PUT method
else {
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");
	header("Access-Control-Allow-Methods: POST, PUT, DELETE");
	header("Access-Control-Max-Age: 3600");
	header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

	// store and test the POST params
	$post_params 	= json_decode(file_get_contents("php://input"));
	// stop if something is wrong with parameters
	if(!$request->validate_params($post_params)) exit(1);
}

// create a new instance of PlaylistController
$controller = new PlaylistController($_GET, $post_params);

// TODO rename actions & actions 2
$action 	= $request->get_param('action'); 
$playlist_id 	= $request->get_param('playlist_id'); 
$action2 	= $request->get_param('action2'); 
$video_id 	= $request->get_param('video_id'); 

// dispatch to the controller, based on the method given and action required in URI
switch($method){

	case "GET":
		switch($action){
			case "playlists":
				// URI pattern : /playlists/id_playlist/videos/ 
				if(!empty($action2)) {
					if($action2=="videos") $controller->getPlaylistVideos();
					else $request->response_message(404, ["message" => "please request a known ressource 2"]);
				}
				// URI pattern : /playlists/id_playlist/ 
				else $controller->getPlaylists();
				break;
			case "videos":
				// URI pattern : /videos/
				$controller->getAllVideos();
				break;
			default:
				$request->response_message(404, ["message" => "please request a known action"]);
				break;
		}
		break;
	case "POST":
		switch($action){
			case "playlists":
				// URI pattern : /playlists/id_playlist/videos/id_video/
				// add a video to the playlist with position
				if(!empty($playlist_id) && !empty($video_id)){
					$controller->add_video();	
				}
				// URI pattern : /playlists/
				// create a playlist
				else $controller->create();
				break;
			default:
				$request->response_message(404, ["message" => "please request a known action"]);
				break;
		}
		break;
	case "PUT":
		switch($action){

			case "playlists":
				// URI pattern : /playlists/id_playlist/videos/id_video/
				// move a video into a playlist with position
				if(!empty($playlist_id) && !empty($video_id)){
					$controller->move_video();	
				}
				// URI pattern : /playlists/id_playlist/
				// update a playlist
				else $controller->update();
				break;
			default:
				$request->response_message(404, ["message" => "please request a known action"]);
				break;
		}
		break;
	case "DELETE":
		switch($action){

			case "playlists":
				// URI pattern : /playlists/id_playlist/videos/id_video/
				// remove a video from a playlist
				if(!empty($playlist_id) && !empty($video_id)){
					$controller->remove_video();	
				}
				// URI pattern : /playlists/id_playlist/
				// delete a playlist
				else $controller->delete();
				break;
			default:
				$request->response_message(404, ["message" => "please request a known action"]);
				break;
		}
		break;
}
