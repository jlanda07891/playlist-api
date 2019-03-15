<?php
/**
 * Include required classes
 *
 * ./config/database.php contains everything to init a new mysql instance
 * ./model/playlist.php Model that contains properties and methods for "playlist" database queries 
 * ./model/video.php Model that contains properties and methods for "video" database queries 
 *./config/request.php contains methods to return HTTP responses with a message
 *
 */
include_once './config/database.php';
include_once './model/playlist.php';
include_once './model/video.php';
include_once './config/request.php';

/**
 * class PlaylistController
 * contains functions to call methods from Playlist class 
 */
class PlaylistController {

	/* 
	 * construct 
	 * @param $get_params get parameters passed 
	 * @param $post_params post parameters passed
	 * 
	 * */
	public function __construct($get_params, $post_params){
		// init database and playlist object
		$database 	   = new Database();
		$this->db 	   = $database->getConnection();
		$this->request 	   = new Request($_SERVER);
		$this->get_params  = $get_params;
		$this->post_params = $post_params;
	}
	
	/**
	 * Get videos of a given playlist
	 * @return Request:response_message()
	 */
	public function getPlaylistVideos(){
		// init Video class
		$playlist = new Playlist($this->db);
		$playlist->playlist_id = $this->get_params['id'];
		$stmt = $playlist->getOne();
		// data will be fetched, store into a result and send with a HTTP response
		$this->fetch_data($stmt);
	}
	
	/**
	 * Get all videos
	 * @return Request:response_message()
	 */
	public function getAllVideos(){
		// init Video class
		$playlist = new Video($this->db);
		$stmt = $playlist->getAll();
		$this->fetch_data($stmt);
	}
	
	/**
	 * Get either all the playlists or a playlist if a playlist_id is specified
	 * @return Request:response_message()
	 */
	public function getPlaylist(){
		// init Playlist class
		$playlist = new Playlist($this->db);
		// if a playlist id is specified
		if(isset($this->get_params['id'])){
			// give the playlist_id to the Playlist instance
			$playlist->playlist_id = $this->get_params['id'];
		}
		$stmt = $playlist->getAll();
		$this->fetch_data($stmt);
	}
	
	/**
	 * Delete a playlist
	 * @return Request:response_message()
	 */
	public function delete(){
		// init object
		$playlist = new Playlist($this->db);
		// test if the required params are not empty 
		if(!empty($this->post_params->id_playlist)){
			// set public properties of Playlist
			$playlist->playlist_id = $this->post_params->id_playlist;

			// delete the playlist
			if($playlist->delete()){
				$this->request->response_message(404, ["message" => "playlist succesfully deleted"]);
			}
			else $this->request->response_message(503, ["message" => "unable to delete playlist"]);

		}
		else $this->request->response_message(404, ["message" => "unable to delete playlist, please specify a playlist_id"]);
		
	}

	/**
	 * Remove a video from a playlist
	 * @return Request:response_message()
	 */
	public function remove_video(){
		
		// init object
		$playlist = new Playlist($this->db);
		// test if the required params are not empty 
		if(!empty($this->post_params->id_video) && !empty($this->post_params->id_playlist)){
			// set public properties of Playlist
			$playlist->playlist_id 	   = $this->post_params->id_playlist;
			$playlist->video_id        = $this->post_params->id_video;

			// remove the video from the playlist
			if($playlist->remove_video()){
				$this->request->response_message(404, ["message" => "video succesfully removed to playlist"]);
			}
			else $this->request->response_message(503, ["message" => "unable to remove video into playlist"]);
		}
		else $this->request->response_message(404, ["message" => "unable to remove the video, please specify an id_video and id_playlist"]);
	}

	/**
	 * Move a video inside a playlist
	 * @return Request:response_message()
	 */
	public function move_video(){
		
		// init Playlist class
		$playlist = new Playlist($this->db);
		// test if the required params are not empty
		if(!empty($this->post_params->id_video) && !empty($this->post_params->id_playlist) && !empty($this->post_params->placement)){
			// set public properties of Playlist
			$playlist->playlist_id 	   = $this->post_params->id_playlist;
			$playlist->video_id        = $this->post_params->id_video;
			$playlist->video_placement = $this->post_params->placement;

			// move the video in playlist
			if($playlist->move_video()){
				$this->request->response_message(404, ["message" => "video succesfully moved to playlist"]);
			}
			else $this->request->response_message(503, ["message" => "unable to move video into playlist"]);
		}
		else $this->request->response_message(404, ["message" => "unable to move the video, please specify an id_video, id_playlist and placement"]);
	}
	
	/**
	 * Add a video inside a playlist
	 * @return Request:response_message()
	 */
	public function add_video(){
		
		// init Playlist class
		$playlist = new Playlist($this->db);
		// test if the required params are not empty
		if(!empty($this->post_params->id_video) && !empty($this->post_params->id_playlist) && !empty($this->post_params->placement)){
			// set public properties of Playlist
			$playlist->playlist_id 	   = $this->post_params->id_playlist;
			$playlist->video_id        = $this->post_params->id_video;
			$playlist->video_placement = $this->post_params->placement;

			// add the video in playlist
			if($playlist->add_video()){
				$this->request->response_message(404, ["message" => "video succesfully added to playlist"]);
			}
			else $this->request->response_message(503, ["message" => "unable to add video into playlist"]);
		}
		else $this->request->response_message(404, ["message" => "unable to add video to playlist, please specify an id_video, id_playlist and placement"]);
	}  
	
	/**
	 * Update a playlist
	 * @return Request:response_message()
	 */
	public function update(){
		
		// init Playlist class
		$playlist = new Playlist($this->db);
		// test if the required params are not empty
		if(!empty($this->post_params->name) and !empty($this->post_params->id_playlist)){
			// set public properties of Playlist
			$playlist->name = $this->post_params->name;
			$playlist->playlist_id = $this->post_params->id_playlist;

			// update the playlist
			if($playlist->update()){
				$this->request->response_message(404, ["message" => sprintf("playlist %s succesfully updated",$this->post_params->name)]);
			}
			else $this->request->response_message(503, ["message" => "unable to update playlist"]);
		}
		else $this->request->response_message(404, ["message" => "unable to modify playlist, please specify a playlist_id and name"]);
	}
	
	/**
	 * Create a playlist
	 * @return Request:response_message()
	 */
	public function create(){
		
		// init object
		$playlist = new Playlist($this->db);
		// test if the required params are not empty
		if(!empty($this->post_params->name)){
			// set public properties of Playlist
			$playlist->name = $this->post_params->name;

			// create the playlist
			if($playlist->create()){
				$this->request->response_message(404, ["message" => sprintf("playlist %s succesfully created",$this->post_params->name)]);
			}
			else $this->request->response_message(503, ["message" => "unable to create playlist"]);
		}
		else $this->request->response_message(404, ["message" => "unable to create the playlist, please specify a name"]);
	}
	
	/**
	 * Fetch the statement, store rows into an array
	 * @return Request:response_message()
	 */
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

}
