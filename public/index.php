<?php

require_once '../vendor/autoload.php';

use LoneCat\ORM\DBAL;

$creds = [
    DBAL\Driver\PDODriver::DBNAME => '../sqlite/test.db',
];

$conn = new DBAL\Connection(DBAL\Connection::SQLITE, $creds);

$conn->connect();


var_dump($conn->executeQuery('select * from test_table where id = :id', ['id' => 1], ['id'=>'bin'])->fetchAll());
/*$stmt = $dbal->queryTableWithRawWhere('test_table', 'id > -1');
$result = $stmt->execute()->fetchAll();
foreach ($result as $row) {
    var_dump($row);
}*/