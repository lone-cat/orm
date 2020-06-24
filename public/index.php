<?php

require_once '../vendor/autoload.php';

use LoneCat\ORM\DBAL;

$creds_sqlite = [
    DBAL\Driver\PDODriver::DBNAME => '../sqlite/test.db',
];

$creds_mysql = [
    DBAL\Driver\PDODriver::HOST => 'mysql',
    DBAL\Driver\PDODriver::CHARSET => 'utf8',
    DBAL\Driver\PDODriver::DBNAME => 'test',
    DBAL\Driver\PDODriver::USER => 'test',
    DBAL\Driver\PDODriver::PASSWORD => 'test',
];



$conn_sqlite = new DBAL\Connection(DBAL\Connection::SQLITE, $creds_sqlite);
$conn_mysql = new DBAL\Connection(DBAL\Connection::MYSQL, $creds_mysql);


//$conn_mysql->insert('test_table', ['value' => 'newval' . rand()]);
var_dump($conn_mysql->select('test_table', ['value' => 'wtf?'], [], ['id' => ''])->fetchAll());

/*$stmt = $dbal->queryTableWithRawWhere('test_table', 'id > -1');
$result = $stmt->execute()->fetchAll();
foreach ($result as $row) {
    var_dump($row);
}*/