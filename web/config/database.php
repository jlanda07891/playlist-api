<?php

class Database {

	private $host = "mysql";
	private $db   = "playlist";
	private $user = "dev";
	private $pass = "Technik2dev";
	public $conn;	

	public function getConnection(){

		$this->conn = null;

		try {
			// init a new mysqli instance
			$this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
			$this->conn->query("set names utf8");
		}
		catch (mysqli_sql_exception $e) {
			throw $e; 
		}

		return $this->conn;
	}
}
