<?php

namespace LoneCat\ORM;

use PDO;

class DBAL
{

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;

        $a = $pdo->prepare('select * from a');
    }

    public function queryFullTable(string $table_name)
    {

    }

    protected function

}