<?php

namespace LoneCat\ORM\DBAL;

use LoneCat\ORM\DBAL\Connection\PDOMySQLConnection;
use LoneCat\ORM\DBAL\Connection\PDOSqLiteConnection;
use LoneCat\ORM\DBAL\Driver\PDODriver;
use LoneCat\ORM\DBAL\Driver\MySQLPDODriver;
use LoneCat\ORM\DBAL\Driver\SqLitePDODriver;
use LoneCat\ORM\DBAL\Exceptions\DBALException;
use LoneCat\ORM\DBAL\Platform\AbstractPlatform;

class Connection
{

    public const MYSQL = MySQLPDODriver::class;
    public const SQLITE = SqLitePDODriver::class;

    private const DRIVERS = [
        MySQLPDODriver::class,
        SqLitePDODriver::class,
    ];

    private array $options;

    private PDODriver $driver;
    private AbstractPlatform $platform;
    private ?PDOConnection $connection = null;

    public function __construct(string $driver, array $options)
    {
        if (!in_array($driver,self::DRIVERS, true)) {
            throw new DBALException('invalid driver');
        }

        $this->driver = new $driver();
        $this->platform = $this->driver->getPlatform();
        $this->options = $options;
    }

    public function connect() {
        if (!isset($this->connection)) {
            try {
                $this->connection = $this->driver->connect($this->options);
                return true;
            } catch (\PDOException $e) {
                throw new DBALException('db connection failed');
            }
        }

        return false;
    }

    public function close() {
        $this->connection = null;
    }

    public function getWrappedConnection() {
        $this->connect();

        return $this->connection;
    }

    public function convertToPHPValue($type, $value) {

    }

    public function convertToDBValue($type, $value) {

    }

    public function select() {

    }

    public function quoteIdentifier(string $identifier) {
        return $this->platform->quoteIdentifier($identifier);
    }

    public function executeQuery(string $sql, array $params = [], array $types = []): PDOResult
    {
        $connection = $this->getWrappedConnection();

        if (empty($params)) {
            return $connection->query($sql);
        }

        $statement = $connection->prepare($sql);

        if (empty($types)) {
            return $statement->execute($params);
        }

        $statement->bindValues($params, $types);

        return $statement->execute();
    }

    public function lastInsertId()
    {
        $this->getWrappedConnection()->lastInsertId();
    }

}