<?php
// Class to establish connection with the database server
class Database
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "loc8";
    public $conn;
    
    public function __construct()
    {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->servername};dbname={$this->dbname}", 
                $this->username, 
                $this->password
            );
            // Set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
}
?>