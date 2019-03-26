<?php

/**
 * class Playlist
 * Model that contains properties and methods for "playlist" and "video" database queries 
 */
class Playlist {
	
	/**
 	* private properties
 	*/
	private $conn;
	private $mysql_table = "playlist";

	/**
	 * private properties of playlist and video
	 *
	 * @param integer $playlist_id The id of the playlist
	 * @param string $name The name of the playlist
	 *
 	*/
	private $playlist_id;
	private $name;

	/* construct */
	public function __construct($db){
		$this->conn = $db;
	}
	
	/**
	 * Setter for playlist_id
	 *
	 * @param integer $playlist_id The id of the playlist
	 * @return void
	 */
	public function set_playlist_id($playlist_id){
		$this->playlist_id = $playlist_id;
	}

	/**
	 * Setter for name
	 *
	 * @param string $name The name of the playlist
	 * @return void
	 */
	public function set_name($name){
		$this->name = $name;
	}

	/**
	 * Delete a playlist and it's associations
	 *
	 * @param integer $this->playlist_id The id of the playlist to delete
	 * @return Boolean
	 */
	public function delete(){
		// delete the playlist
		$sql  = "delete from playlist where id = ?;";
		$stmt_playlist = $this->conn->prepare($sql);
		$stmt_playlist->bind_param("d", $this->playlist_id);

		// delete the videos associated to this playlist
		$sql  = "delete from playlist_video where playlist_id = ?;";
		$stmt_playlist_video = $this->conn->prepare($sql);
		$stmt_playlist_video->bind_param("d", $this->playlist_id);

		// execute query
		if($stmt_playlist->execute() && $stmt_playlist_video->execute()){
			$this->conn->close();
			return true;
    		}
    		return false;
	}
	
	/**
	 * Return the position of a given playlist's video
	 *
	 * @param integer $video_id The id of the video
	 * @return Integer
	 */
	public function get_video_current_pos($video_id){
		// get the video position given playlist_id and video_id parameters
		$sql_video_actual_pos = sprintf("select video_order from playlist_video where playlist_id = %d and video_id = %d",$this->playlist_id,$video_id);
		$stmt 	= $this->conn->query($sql_video_actual_pos);
		$row 	= $stmt->fetch_assoc();
		return intval($row['video_order']);
	}
	
	/**
	 * Remove a video from a playlist
	 *
	 * @param integer $video_id The id of the video to remove
	 * @return Boolean
	 */
	public function remove_video($video_id){

		// first we get the position of the video to remove
		$video_current_pos = $this->get_video_current_pos($video_id);
		// if no position found, then the video doesn't exists in the playlist
                if(!$video_current_pos) return false;

		// remove the video from the playlist
		$sql_remove_video = "delete from playlist_video where playlist_id = ? and video_id = ?;";
		$stmt = $this->conn->prepare($sql_remove_video);
		$stmt->bind_param("dd", $this->playlist_id, $video_id);
		if(!$stmt->execute()){
			$this->conn->close();
			return false;
		}

		// re-arrange the playlist : decrement the position of videos located after the removed video
		$sql_re_arrange_order = "update playlist_video set video_order = video_order -1 where video_order > ? and playlist_id = ?;";
		$stmt = $this->conn->prepare($sql_re_arrange_order);
		$stmt->bind_param("dd", $video_current_pos, $this->playlist_id);
		if(!$stmt->execute()){
			$this->conn->close();
			return false;
		}

		$this->conn->close();
		return true;
	}
	
	/**
	 * Move a video inside a playlist
	 *
	 * @param integer $video_id The id of the video to move
	 * @param integer $video_placement The position of the video to move
	 * @return Boolean
	 */
	public function move_video($video_id,$video_placement){
		
		// first we get the position of the video to remove
		$video_current_pos = $this->get_video_current_pos($video_id);
		// if no position found, then the video doesn't exists in the playlist
                if(!$video_current_pos) return false;

		// the position requested is the current one, no move needed
		if($video_placement == $video_current_pos){
			$this->conn->close();
			return true;
		}
		// if the video is moved earlier inside the playlist
		else if($video_placement < $video_current_pos){
			// all the videos from the new position to the actual position must be shifted by +1
			$sql_update_playlist = "update playlist_video set video_order = video_order+1 where video_order >= ? and video_order < ? and playlist_id = ?;";
		}
		// if the video is moved later inside the playlist
		else {
			// all the videos from the actual position to the new position must be shifted by -1
			$sql_update_playlist = "update playlist_video set video_order = video_order-1 where video_order <= ? and video_order > ? and playlist_id = ?;";
		}

		// update position of videos
		$stmt = $this->conn->prepare($sql_update_playlist);
		$stmt->bind_param("ddd", $video_placement, $video_current_pos, $this->playlist_id);
		if(!$stmt->execute()){
			$this->conn->close();
			return false;
		}

		// the video can now be moved to the new position
		$sql_move_video = "update playlist_video set video_order = ? where video_id = ? and playlist_id = ?;";
		$stmt = $this->conn->prepare($sql_move_video);
		$stmt->bind_param("ddd", $video_placement, $video_id, $this->playlist_id);
		if(!$stmt->execute()){
			$this->conn->close();
			return false;
		}

		$this->conn->close();
		return true;
	}
	
	/**
	 * Add a video inside a playlist
	 *
	 * @param integer $video_id The id of the video to add
	 * @param integer $video_placement The position of the video to add
	 * @return Boolean
	 */
	public function add_video($video_id,$video_placement){

		// first all the videos starting a the new position must be shifted by +1
		$sql_update_order = "update playlist_video 
					set video_order = video_order+1 
					where video_order >= ? and playlist_id = ?;";
		$stmt = $this->conn->prepare($sql_update_order);
		$stmt->bind_param("dd", $video_placement, $this->playlist_id);
		// execute query
		if(!$stmt->execute()){
			$this->conn->close();
			return false;
    		}

		// insert the new video in playlist, at position needed
		$sql  = "insert into playlist_video (video_id,video_order,playlist_id) values (?,?,?);";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("ddd", $video_id, $video_placement, $this->playlist_id);
		// execute query
		if($stmt->execute()){
			$this->conn->close();
			return true;
		}
		$this->conn->close();
    		return false;
	}
	
	/**
	 * update a playlist
	 *
	 * @param string $this->name The new name of the playlist
	 * @return Boolean
	 */
	public function update(){
		// sanitize the name param
    		$this->name 	 = htmlspecialchars(strip_tags($this->name));

		// update the playlist's name
		$sql  = "update playlist set name = ? where id = ?;";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("sd", $this->name, $this->playlist_id);

		// execute query
		if($stmt->execute()){
			$this->conn->close();
			return true;
		}
		$this->conn->close();
    		return false;
	}
	
	/**
	 * create a playlist
	 *
	 * @param string $this->name The name of the playlist
	 * @return Boolean
	 */
	public function create(){
		// sanitize the name param
    		$this->name 	 = htmlspecialchars(strip_tags($this->name));

		// insert the new playlist
		$sql  = "insert into playlist (name) values (?);";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("s", $this->name);

		// execute query
		if($stmt->execute()){
			$this->conn->close();
			return true;
		}
		$this->conn->close();
    		return false;
	}
	
	/**
	 * Return the list of all videos from a playlist, ordered by position
	 *
	 * @param integer $this->playlist_id The id of the playlist
	 * @return mysqli statement
	 */
	public function getOne(){
		// get videos of a given playlist using playlist_video table
		$sql 	= sprintf("select v.id, concat(v.title,' from ',p.name) as title, v.thumbnail from video v join playlist_video pv on pv.video_id = v.id join playlist p on p.id = pv.playlist_id where p.id = %d order by video_order asc;", $this->playlist_id);
		$stmt   = $this->conn->query($sql);;
		$this->conn->close();
		return $stmt;
	}
	
	/**
	 * Return either the list of all the playlist, or a particular playlist if a playlist_id is setted
	 *
	 * @param  integer $this->playlist_id The id of the playlist
	 * @return mysqli statement
	 */
	public function getAll(){
		$sql_playlist_filter = !empty($this->playlist_id) ? sprintf(" where p.id = %d", $this->playlist_id) : "";
		// format the sql query
		$sql 	= sprintf("select p.id, name from playlist p %s order by p.id asc;", $sql_playlist_filter);
		// prepare and execute the query
		$stmt 	= $this->conn->query($sql);
		$this->conn->close();
		return $stmt;
	}
	
}
