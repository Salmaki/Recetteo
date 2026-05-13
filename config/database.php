<?php 

class Database{
    private $host='localhost';
    private $dbname = 'devspace';
    private $username = 'root';
    private $password  = '';
    private $port  = '';

    public $conn;

    
    public function getconnection(){
        $this->conn = null;
        $dsn="mysql:host={$this->host};port={$this->port};dbname={$this->dbname};" ;

        try{
            $this->conn = new PDO($dsn,$this->username,$this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            die("error:". $e->getMessage());
        }
        return $this->conn;
    }
}