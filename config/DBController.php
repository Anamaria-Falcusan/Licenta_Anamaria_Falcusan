<?php

class DBController
{
    private string $host = "localhost";
    private string $dbName = "musicconnect";
    private string $username = "root";
    private string $password = "";

    private PDO $conn;

    public function __construct()
    {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4",
                $this->username,
                $this->password
            );

         
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            die("Eroare la conectarea la baza de date: " . $e->getMessage());
        }
    }

    public function getDBResult(string $sql, array $params = []): array
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

 
    public function updateDB(string $sql, array $params = []): bool
    {
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

   
    public function getLastInsertId(): string
    {
        return $this->conn->lastInsertId();
    }
}