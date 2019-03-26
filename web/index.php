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

// validate the requested URI
if(!$request->validate_uri()) exit(1);

// header based on the HTTP method
if($method == "GET"){
	header('Content-Type: text/plain');
}
else {
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");
	header("Access-Control-Allow-Methods: POST, PUT, DELETE");
	// allows all our wanted HTTP methods to have expiration of access control
	header("Access-Control-Max-Age: 3600");
	header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

	// store and test the POST / PUT params
	$post_params 	= json_decode(file_get_contents("php://input"));
	// stop if something is wrong with parameters
	if(!$request->validate_params($post_params)) exit(1);
}


// create a new instance of PlaylistController
$controller = new PlaylistController($_GET, $post_params);

// get the URI parameters for the dispatch
$ressource 	= $request->get_param('ressource1'); 
$playlist_id 	= $request->get_param('playlist_id'); 
$ressource2 	= $request->get_param('ressource2'); 
$video_id 	= $request->get_param('video_id'); 

// dispatch to the controller, based on the method given and ressource requested in URI
switch($method){

	case "GET":
		switch($ressource){
			case "playlists":
				// URI pattern : /playlists/id_playlist/videos/ 
				if(!empty($ressource2)) {
					if($ressource2=="videos") $controller->getPlaylistVideos();
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
				$request->response_message(404, ["message" => "please request a known ressource"]);
				break;
		}
		break;
	case "POST":
		if($ressource == "playlists"){
			// URI pattern : /playlists/id_playlist/videos/id_video/
			// add a video to the playlist with position
			if(!empty($playlist_id) && !empty($video_id)){
				$controller->add_video();	
			}
			// URI pattern : /playlists/
			// create a playlist
			else $controller->create();
		} 
		else $request->response_message(404, ["message" => "please request a known ressource"]);
		break;
	case "PUT":
		if($ressource == "playlists"){
			// URI pattern : /playlists/id_playlist/videos/id_video/
			if(!empty($playlist_id)){
				// move a video into a playlist with position
				if(!empty($video_id)) $controller->move_video();	
				// update a playlist
				else $controller->update();
			}
			else $request->response_message(404,["message" => "unable to modify playlist, please specify a playlist_id"]); 
		}
		else $request->response_message(404, ["message" => "please request a known ressource"]);
		break;
	case "DELETE":
		if($ressource == "playlists"){
			// URI pattern : /playlists/id_playlist/videos/id_video/
			if(!empty($playlist_id)){
				// remove a video from a playlist
				if(!empty($video_id)) $controller->remove_video();
				// delete a playlist
				else $controller->delete();
			}
			else $request->response_message(404,["message" => "unable to delete playlist, please specify a playlist_id"]);
		}
		else $request->response_message(404, ["message" => "please request a known ressource"]);
		break;
}
