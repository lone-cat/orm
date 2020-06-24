<?php

namespace LoneCat\ORM\DBAL;

use PDO;

class PDOConnection
{

    private PDO $connection;

    public function __construct(string $dsn, string $user = '', string $password = '', array $options = [])
    {
        $this->connection = new PDO($dsn, $user, $password, $options);
    }

    public function query(string $sql): PDOResult
    {
        return new PDOResult($this->connection->query($sql));
    }

    public function getNativePDOConnection(): PDO
    {
        return $this->connection;
    }

    public function prepare(string $sql): PDOStatement
    {
        return new PDOStatement($this->connection->prepare($sql));
    }

    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

}