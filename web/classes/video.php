<?php

class Video {
	
	// database connection
	private $conn;
	private $mysql_table = "video";

	// properties of a video object
	public $id;
	public $title;
	public $thumbnail;

	public function __construct($db){
		$this->conn = $db;
	}


	public function getAll(){
		// format the sql query
		$sql 	= "select v.id, title, thumbnail from video v order by v.id asc;";
		// prepare and execute the query
		$stmt 	= $this->conn->query($sql);
		return $stmt;
	}

}

