<?php

class Playlist {
	
	// database connection
	private $conn;
	private $mysql_table = "playlist";

	// properties of a playlist object
	public $playlist_id;
	public $name;
	public $video_id;
	public $video_placement;

	public function __construct($db){
		$this->conn = $db;
	}

	public function link_video(){
		$sql_clean_playlist_videos = "delete from playlist_video where playlist_id = ?;";
		$stmt = $this->conn->prepare($sql_clean_playlist_videos);
                $stmt->bind_param("d", $this->playlist_id);
		$stmt->execute();

		// get video ids separated by comma
		$arr_video_ids = explode(',',$this->video_ids);
		foreach($arr_video_ids as $video_id){
			if(intval($video_id)) {
				// link a video to the (new) playlist
				$sql  = "insert into playlist_video (video_id,playlist_id) values (?,?);";
                		$stmt = $this->conn->prepare($sql);
                		$stmt->bind_param("dd", intval($video_id), $this->playlist_id);
				$stmt->execute();
			}
		}
	}

	public function update(){
		// sanitize
    		$this->name 	 = htmlspecialchars(strip_tags($this->name));
    		$this->video_ids = htmlspecialchars(strip_tags($this->video_ids));

		if(!empty($this->name)){
			// prepare the query and bind the parameter
			$sql  = "update playlist set name = ?;";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param("s", $this->name);
			if(!$stmt->execute()) return false;
		}
		if(!empty($this->video_ids)){
			// link videos to the new playlist
			$this->link_video();
		}
    		return true;
	}

	public function delete(){
		// prepare the query and bind the parameter
		$sql  = "delete from playlist where id = ?;";
		$stmt_playlist = $this->conn->prepare($sql);
		$stmt_playlist->bind_param("d", $this->playlist_id);

		$sql  = "delete from playlist_video where playlist_id = ?;";
		$stmt_playlist_video = $this->conn->prepare($sql);
		$stmt_playlist_video->bind_param("d", $this->playlist_id);

		// execute query
		if($stmt_playlist->execute() && $stmt_playlist_video->execute()){
			return true;
    		}
    		return false;
	}

	public function move_video(){

		// todo check if video in playlist

		// if actual video pos > targeted position
		// todo videos between actual video pos and targeted position must be shifted
		$sql_move_video = "update playlist_video set video_pos = video_pos+1 where video_pos >= ? and playlist_id = ?;";
		$stmt = $this->conn->prepare($sql_move_video);
		$stmt->bind_param("ddd", $this->video_placement, $this->playlist_id);
		if(!$stmt->execute()){
			return false;
    		}

		// video can be move to targeted position
		$sql_move_video = "update playlist_video set video_pos = ? where video_id = ? and playlist_id = ?;";
		$stmt = $this->conn->prepare($sql_move_video);
		$stmt->bind_param("ddd", $this->video_placement, $this->video_id, $this->playlist_id);
		if(!$stmt->execute()){
			return false;
		}

		return true;
	}

	public function add_video(){

		// update position of videos (for every video positionned after the new video's position)
		$sql_update_order = "update playlist_video 
					set video_order = video_order+1 
					where video_order >= ? and playlist_id = ?;";
		$stmt = $this->conn->prepare($sql_update_order);
		$stmt->bind_param("dd", $this->video_placement, $this->playlist_id);
		// execute query
		if(!$stmt->execute()){
			return false;
    		}

		// insert the new video in playlist
		$sql  = "insert into playlist_video (video_id,video_order,playlist_id) values (?,?,?);";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("ddd", $this->video_id, $this->video_placement, $this->playlist_id);
		// execute query
		if($stmt->execute()){
			return true;
    		}
    		return false;
	}

	public function create(){
		// sanitize
    		$this->name 	 = htmlspecialchars(strip_tags($this->name));

		// prepare the query and bind the parameter
		$sql  = "insert into playlist (name) values (?);";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("s", $this->name);

		// execute query
		if($stmt->execute()){
			return true;
    		}
    		return false;
	}
	
	public function getOne(){
		// format the sql query
		$sql 	= sprintf("select v.id, concat(v.title,' from ',p.name) as title, v.thumbnail from video v join playlist_video pv on pv.video_id = v.id join playlist p on p.id = pv.playlist_id where p.id = %d order by video_order asc;", $this->playlist_id);
		$stmt   = $this->conn->query($sql);;
		$this->conn->close();
		return $stmt;
	}

	public function getAll(){
		// format the sql query
		$sql 	= "select p.id, name from playlist p order by p.id asc;";
		// prepare and execute the query
		$stmt 	= $this->conn->query($sql);
		$this->conn->close();
		return $stmt;
	}
	
}
