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
		$this->return_data($stmt);
	}
	
	/**
	 * Get all videos
	 * @return Request:response_message()
	 */
	public function getAllVideos(){
		// init Video class
		$playlist = new Video($this->db);
		$stmt = $playlist->getAll();
		$this->return_data($stmt);
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
		$this->return_data($stmt);
	}
	
	/**
	 * Delete a playlist
	 * @return Request:response_message()
	 */
	public function delete(){
		// init object
		$playlist = new Playlist($this->db);
		// test if all the required params are present 
		if(!empty($this->post_params->id_playlist)){
			// set public properties of Playlist
			$playlist->playlist_id = $this->post_params->id_playlist;

			// delete the playlist
			if($playlist->delete()){
				$this->response_message(200,"playlist succesfully deleted");
			}
			else $this->response_message(503,"unable to delete playlist");
		}
		else $this->response_message(404,"unable to delete playlist, please specify a playlist_id");	
	}

	/**
	 * Remove a video from a playlist
	 * @return Request:response_message()
	 */
	public function remove_video(){
		
		// init object
		$playlist = new Playlist($this->db);
		// test if all the required params are present 
		if(!empty($this->post_params->id_video) && !empty($this->post_params->id_playlist)){
			// set public properties of Playlist
			$playlist->playlist_id 	   = $this->post_params->id_playlist;
			
			$video_id = $this->post_params->id_video;
			// remove the video from the playlist
			if($playlist->remove_video($video_id)){
				$this->response_message(200,"video succesfully removed to playlist");
			}
			else $this->response_message(503,"unable to remove video from playlist");
		}
		else $this->response_message(404,"unable to remove the video, please specify an id_video and id_playlist");
	}

	/**
	 * Move a video inside a playlist
	 * @return Request:response_message()
	 */
	public function move_video(){
		
		// init Playlist class
		$playlist = new Playlist($this->db);
		// test if all the required params are present
		if(!empty($this->post_params->id_video) && !empty($this->post_params->id_playlist) && !empty($this->post_params->placement)){
			// set public properties of Playlist
			$playlist->playlist_id 	   = $this->post_params->id_playlist;
			$video_id        	   = $this->post_params->id_video;
			$video_placement 	   = $this->post_params->placement;

			// move the video in playlist
			if($playlist->move_video($video_id,$video_placement)){
				$this->response_message(200,"video succesfully moved into playlist");
			}
			else $this->response_message(503,"unable to move video into playlist");
		}
		else $this->response_message(404,"unable to move the video, please specify an id_video, id_playlist and placement");
	}
	
	/**
	 * Add a video inside a playlist
	 * @return Request:response_message()
	 */
	public function add_video(){
		
		// init Playlist class
		$playlist = new Playlist($this->db);
		// test if all the required params are present
		if(!empty($this->post_params->id_video) && !empty($this->post_params->id_playlist) && !empty($this->post_params->placement)){
			// set public properties of Playlist
			$playlist->playlist_id 	   = $this->post_params->id_playlist;
			$video_id        	   = $this->post_params->id_video;
			$video_placement 	   = $this->post_params->placement;

			// add the video in playlist
			if($playlist->add_video($video_id,$video_placement)){
				$this->response_message(200,"video succesfully added to playlist");
			}
			else $this->response_message(503,"unable to add video into playlist");
		}
		else $this->response_message(404,"unable to add video to playlist, please specify an id_video, id_playlist and placement");
	}  
	
	/**
	 * Update a playlist
	 * @return Request:response_message()
	 */
	public function update(){
		
		// init Playlist class
		$playlist = new Playlist($this->db);
		// test if all the required params are present
		if(!empty($this->post_params->name) and !empty($this->post_params->id_playlist)){
			// set public properties of Playlist
			$playlist->name = $this->post_params->name;
			$playlist->playlist_id = $this->post_params->id_playlist;

			// update the playlist
			if($playlist->update()){
				$this->response_message(200,sprintf("playlist %s succesfully updated",$this->post_params->name));
			}
			else $this->response_message(503,"unable to update playlist");
		}
		else $this->response_message(404,"unable to modify playlist, please specify a playlist_id and name");
	}
	
	/**
	 * Create a playlist
	 * @return Request:response_message()
	 */
	public function create(){
		
		// init object
		$playlist = new Playlist($this->db);
		// test if all the required params are present
		if(!empty($this->post_params->name)){
			// set public properties of Playlist
			$playlist->name = $this->post_params->name;

			// create the playlist
			if($playlist->create()){
				$this->response_message(200,sprintf("playlist %s succesfully created",$this->post_params->name));
			}
			else $this->response_message(503,"unable to create playlist"); 
		}
		else $this->response_message(404,"unable to create the playlist, please specify a name");
	}
	
	/**
	 * Fetch the statement, store rows into an array
	 * @return Request:response_message()
	 */
	private function return_data($stmt){
	
		$num_rows = $stmt->num_rows;

		if($num_rows>0){
			$result = ['data'=>[]];

			// output data of each row
			while($row = $stmt->fetch_assoc()) {
				$result['data'][] = $row;
			}

			// set response code - 200 OK
			// show object data in json format
		    	$this->response_message(200,$result);
		}
		// no video or playlist found
		else{
		    // set response code - 404 Not found
		    // tell the user no object found
		    $this->response_message(404,"no playlist found");
		}
	}
	
	/**
	 * shortcut function to call Request:response_message()
	 * @return Request:response_message()
	 */
	private function response_message($code,$msg){
		$this->request->response_message($code,$msg);
	}

}
