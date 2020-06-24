<?php

namespace LoneCat\ORM;

use LoneCat\ORM\DBAL\Connection\Connection;
use LoneCat\ORM\DBAL\PDOStatement;

class DBAL
{

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function queryFullTable(string $table_name): PDOStatement
    {
        return $this->connection->query('SELECT * FROM ' . $table_name);
    }

    public function queryTableWithRawWhere(string $table_name, string $where_statement, array $vars = []): PDOStatement
    {
        return $this->connection->query('SELECT * FROM ' . $table_name . ' WHERE ' . $where_statement, $vars);
    }

}