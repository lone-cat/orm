<?php

namespace LoneCat\ORM\DBAL;

use LoneCat\ORM\DBAL\Driver\PDODriver;
use LoneCat\ORM\DBAL\Driver\MySQLPDODriver;
use LoneCat\ORM\DBAL\Driver\SqLitePDODriver;
use LoneCat\ORM\DBAL\Exceptions\DBALException;
use LoneCat\ORM\DBAL\Platform\AbstractPlatform;
use LoneCat\ORM\DBAL\Types\Type;
use PDOException;

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

    public function connect()
    {
        if (!isset($this->connection)) {
            try {
                $this->connection = $this->driver->connect($this->options);
                return true;
            } catch (PDOException $e) {
                throw new DBALException('db connection failed');
            }
        }

        return false;
    }

    public function close()
    {
        $this->connection = null;
    }

    public function getWrappedConnection()
    {
        $this->connect();

        return $this->connection;
    }

    public function prepare(string $sql): PDOStatement
    {
        return $this->getWrappedConnection()->prepare($sql);
    }

    public function convertToPHPValue($type, $value)
    {

    }

    public function convertToDBValue($type, $value)
    {

    }

    private function makeTypesPositional(array $data, array $types): array
    {
        $resultTypes = [];
        foreach ($data as $name => $value) {
            $resultTypes[] = $types[$name] ?? null;
        }

        return $resultTypes;
    }

    private function generateConditions(array $conds, array $types)
    {
        $conditions = $columns = $values = $resultTypes = [];

        $typesPositional = is_string(key($types))
            ? $this->makeTypesPositional($conds, $types)
            : $types
        ;

        foreach ($conds as $columnName => $value) {
            $quotedColumnName = $this->quoteIdentifier($columnName);
            if ($value === null || (($typesPositional[count($conditions)] ?? null) === Type::NULL)) {
                $conditions[] = $this->platform->getIsNullExpression($quotedColumnName);
                continue;
            }

            $columns[]    = $columnName;
            $values[]     = $value;
            $conditions[] = $quotedColumnName . ' = ?';
        }

        return [
            $columns,
            $values,
            $conditions,
            $typesPositional
        ];

    }

    private function getOrderByExpr(array $data): string {
        $orderBy = [];
        foreach ($data as $fieldName => $direction)
        {
            $fieldName = $this->quoteIdentifier($fieldName);
            $direction = mb_strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $orderBy[] = $fieldName . ' ' . $direction;
        }

        return empty($orderBy)
            ? ''
            : ' ORDER BY ' . implode($orderBy)
        ;
    }

    public function select(string $tableName, array $conditions = [], array $types = [], array $orderBy = []): PDOResult
    {
        [$columns, $values, $conditions, $types] = $this->generateConditions($conditions, $types);

        return $this->selectWithRawWhere(
            $tableName,
            implode(' AND ', $conditions),
            $values,
            $types,
            $orderBy
        );
    }

    public function selectWithRawWhere(string $tableName, string $where, array $values = [], array $types = [], array $orderBy = []): PDOResult
    {
        $tableName = $this->quoteIdentifier($tableName);

        $orderByExpr = $this->getOrderByExpr($orderBy);

        return $this->executeQuery( 'SELECT * FROM ' . $tableName .  (empty($where) ? '' : ' WHERE ' . $where) . $orderByExpr,
            $values,
            $types
        );
    }

    public function delete(string $tableName, array $conditions, array $types = []): int
    {
        if (empty($conditions)) {
            return 0;
        }

        $tableName = $this->quoteIdentifier($tableName);

        [$columns, $values, $conditions, $types] = $this->generateConditions($conditions, $types);

        return $this->executeUpdate( 'DELETE FROM ' . $tableName . ' WHERE ' . implode(' AND ', $conditions),
            $values,
            $types
        );

    }

    public function update($tableName, array $data, array $conditions, array $dataTypes = [], array $conditionTypes = [])
    {

        if (empty($conditions) || empty($data)) {
            return 0;
        }

        $tableName = $this->quoteIdentifier($tableName);


        [$condColumns, $condValues, $conditions, $conditionTypes] = $this->generateConditions($conditions, $conditionTypes);

        $columnNames = $values = $placeholders = [];

        foreach ($data as $columnName => $value) {
            $columnNames[] = $columnName;
            $values[] = $value;
            $placeholders[] = $columnName . ' = ?';
        }

        if (is_string(key($dataTypes))) {
            $dataTypes = $this->makeTypesPositional($columnNames, $dataTypes);
        }

        $sql = 'UPDATE ' . $tableName . ' SET ' . implode(', ', $placeholders)
            . ' WHERE ' . implode(' AND ', $conditions);

        return $this->executeUpdate($sql, array_merge($values, $condValues), array_merge($dataTypes, $conditionTypes));
    }

    public function insert(string $tableName, array $data, array $types = []): int
    {
        if (empty($data)) {
            return 0;
        }

        $tableName = $this->quoteIdentifier($tableName);

        $columnNames = $values = $placeholders = [];

        foreach ($data as $columnName => $value) {
            $columnNames[] = $this->quoteIdentifier($columnName);
            $values[] = $value;
            $placeholders[] = '?';
        }

        return $this->executeUpdate(
            'INSERT INTO ' . $tableName . ' (' . implode(', ', $columnNames) . ')' .
            ' VALUES (' . implode(', ', $placeholders) . ')',
            $values,
            is_string(key($types))
                ? $this->makeTypesPositional($data, $types)
                : $types
        );
    }



    public function iterate(string $sql, array $params = [], array $types = [])
    {
        $result = $this->executeQuery($sql, $params, $types);

        while (($row = $result->fetch()) !== false) {
            yield $row;
        }
    }

    public function quoteIdentifier(string $identifier)
    {
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

    public function executeUpdate(string $sql, array $params = [], array $types = []): int
    {
        return $this->executeQuery($sql, $params, $types)->rowCount();
    }

    public function lastInsertId()
    {
        $this->getWrappedConnection()->lastInsertId();
    }

}