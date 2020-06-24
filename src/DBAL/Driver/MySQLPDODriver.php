<?php

namespace LoneCat\ORM\DBAL\Driver;

use LoneCat\ORM\DBAL\PDOConnection;
use LoneCat\ORM\DBAL\Platform\MySQLPlatform;
use LoneCat\ORM\DBAL\Platform\AbstractPlatform;
use PDO;

class MySQLPDODriver
    extends PDODriver
{


    // credentials
    private string $server;
    private int $port;
    private string $user;
    private string $password;

    public function getPlatform(): AbstractPlatform
    {
        return new MySQLPlatform();
    }



    protected function generateDSN(array $options): string {

        $dsn = 'mysql:';
        if (isset($options[self::HOST])) {
            $dsn .= 'host=' . $options[self::HOST] . ';';
        }

        if (isset($options[self::PORT])) {
            $dsn .= 'port=' . $options[self::PORT] . ';';
        }

        if (isset($options[self::DBNAME])) {
            $dsn .= 'dbname=' . $options[self::DBNAME] . ';';
        }

        if (isset($options[self::CHARSET])) {
            $dsn .= 'charset=' . $options[self::CHARSET] . ';';
        }

        return $dsn;
    }

    public function generateOptions(array $options): array
    {
        return array_merge(
            parent::generateOptions($options),
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $options[self::CHARSET],
            ]
        );
    }
}