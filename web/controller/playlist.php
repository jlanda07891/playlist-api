<?php

// include the database
include_once './config/database.php';
// contains properties and methods for "playlist" database queries.
include_once './classes/playlist.php';
include_once './classes/video.php';
// include the request parser
include_once './config/request.php';

class PlaylistController {

	// todo close connection
	public function __construct($get_params, $post_params){

		// init database and playlist object
		$database = new Database();
		$this->db 	   = $database->getConnection();
		$this->request 	   = new Request($_SERVER);
		$this->get_params  = $get_params;
		$this->post_params = $post_params;
	}

	public function fetch_data($stmt){
	
		$num_rows = $stmt->num_rows;

		if($num_rows>0){
			$result = ['data'=>[]];

			// output data of each row
			while($row = $stmt->fetch_assoc()) {
				$result['data'][] = $row;
			}

			// set response code - 200 OK
			// show object data in json format
		        $this->request->response_message(200, $result);
		}
		// no video or playlist found
		else{
		    // set response code - 404 Not found
		    // tell the user no object found
		    $this->request->response_message(404, ["message" => sprintf("no %s found","playlist")]);
		}
	}

	public function getPlaylistVideos(){
		// init Video class
		$playlist = new Playlist($this->db);
		$playlist->playlist_id = $this->get_params['id'];
		$stmt = $playlist->getOne();
		$this->fetch_data($stmt);
	}

	public function getAllVideos(){
		// init Video class
		$playlist = new Video($this->db);
		$stmt = $playlist->getAll();
		$this->fetch_data($stmt);
	}

	public function getAllPlaylists(){
		// init Playlist class
		$playlist = new Playlist($this->db);
		$stmt = $playlist->getAll();
		$this->fetch_data($stmt);
	}

	public function update(){
		// init object
		$playlist = new Playlist($this->db);

		// id of the playlist is necessary to update
		if(!empty($this->params->id) && (!empty($this->params->video_ids) or !empty($this->params->name))){
			$playlist->playlist_id 	= $this->params->id;
			if(!empty($this->params->video_ids)) 	$playlist->video_ids 	= $this->params->video_ids;
			if(!empty($this->params->name)) 	$playlist->name 	 	= $this->params->name;
		}
		else $this->request->response_message(404, ["message" => sprintf("unable to create %s, data is incomplete","playlist")]);
		
		// create the playlist
		if($playlist->update()){
			$this->request->response_message(404, ["message" => sprintf("%s succesfully updated","playlist")]);
		}
		else $this->request->response_message(503, ["message" => sprintf("unable to update %s","playlist")]);

	}

	public function delete(){
		// init object
		$playlist = new Playlist($this->db);
		if(!empty($this->post_params->id_playlist)){
			$playlist->playlist_id = $this->post_params->id_playlist;
		}
		else $this->request->response_message(404, ["message" => "unable to delete playlist, please specify a playlist_id"]);
		
		// delete the playlist
		if($playlist->delete()){
			$this->request->response_message(404, ["message" => "playlist succesfully deleted"]);
		}
		else $this->request->response_message(503, ["message" => "unable to delete playlist"]);
	}

	public function move_video(){
		
		// init object
		$playlist = new Playlist($this->db);
		if(!empty($this->post_params->id_video) && !empty($this->post_params->id_playlist) && !empty($this->post_params->placement)){
			$playlist->playlist_id 	   = $this->post_params->id_playlist;
			$playlist->video_id        = $this->post_params->id_video;
			$playlist->video_placement = $this->post_params->placement;
		}
		else $this->request->response_message(404, ["message" => "unable to move the video, please specify an id_video, id_playlist and placement"]);

		// move the video in playlist
		if($playlist->move_video()){
			$this->request->response_message(404, ["message" => "video succesfully moved to playlist"]);
		}
		else $this->request->response_message(503, ["message" => "unable to move video into playlist"]);
	}

	public function add_video(){
		
		// init object
		$playlist = new Playlist($this->db);
		if(!empty($this->post_params->id_video) && !empty($this->post_params->id_playlist) && !empty($this->post_params->placement)){
			$playlist->playlist_id 	   = $this->post_params->id_playlist;
			$playlist->video_id        = $this->post_params->id_video;
			$playlist->video_placement = $this->post_params->placement;
		}
		else $this->request->response_message(404, ["message" => "unable to add video to playlist, please specify an id_video, id_playlist and placement"]);

		// add the video in playlist
		if($playlist->add_video()){
			$this->request->response_message(404, ["message" => "video succesfully added to playlist"]);
		}
		else $this->request->response_message(503, ["message" => "unable to add video into playlist"]);
	}  

	public function create(){
		
		// init object
		$playlist = new Playlist($this->db);
		if(!empty($this->post_params->name)){
			$playlist->name = $this->post_params->name;
		}
		else $this->request->response_message(404, ["message" => "unable to delete playlist, please specify a name"]);
		
		// create the playlist
		if($playlist->create()){
			$this->request->response_message(404, ["message" => sprintf("playlist %s succesfully created",$this->post_params->name)]);
		}
		else $this->request->response_message(503, ["message" => "unable to create playlist"]);

	}

}
