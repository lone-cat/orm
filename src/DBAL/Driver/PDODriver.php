<?php

namespace LoneCat\ORM\DBAL\Driver;

use LoneCat\ORM\DBAL\PDOConnection;
use LoneCat\ORM\DBAL\Platform\AbstractPlatform;
use PDO;

abstract class PDODriver
{
    public const HOST = 'host';
    public const PORT = 'port';
    public const USER = 'user';
    public const PASSWORD = 'password';
    public const DBNAME = 'dbname';
    public const CHARSET = 'charset';

    protected string $db_name;

    public function connect(array $options): PDOConnection
    {
        return new PDOConnection(
            $this->generateDSN($options),
            $options[static::USER] ?? '',
            $options[static::PASSWORD] ?? '',
            $this->generateOptions($options)
        );

    }

    abstract public function getPlatform(): AbstractPlatform;

    abstract protected function generateDSN(array $options): string;

    protected function generateOptions(array $options): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }
}