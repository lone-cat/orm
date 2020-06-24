<?php

namespace LoneCat\ORM\DBAL;

use PDO;

class PDOResult
{

    private \PDOStatement $statement;

    public function __construct(\PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    public function fetch() {
        return $this->statement->fetch();
    }

    public function fetchColumn(int $num = 0) {
        return $this->statement->fetchAll(PDO::FETCH_COLUMN, $num);
    }

    public function fetchAll() {
        return $this->statement->fetchAll();
    }

}