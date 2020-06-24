<?php

namespace LoneCat\ORM\DBAL;

use LoneCat\ORM\DBAL\Exceptions\DBALException;
use LoneCat\ORM\DBAL\Types\Type;
use PDO;

class PDOStatement
{

    private const TYPE_MAP = [
        Type::NULL => PDO::PARAM_NULL,
        Type::INT => PDO::PARAM_INT,
        Type::STRING => PDO::PARAM_STR,
        Type::BOOL => PDO::PARAM_BOOL,
        Type::BINARY => PDO::PARAM_LOB,
    ];

    protected \PDOStatement $statement;

    public function __construct(\PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    public function bindValue($index, $value, ?string $type = null): self
    {
        if (is_null($type)) {
            $type = mb_strtolower(gettype($value));
        }
        if (!isset(self::TYPE_MAP[$type])) {
            throw new DBALException('no such type ' . $type);
        }

        $this->statement->bindValue($index, $value, self::TYPE_MAP[$type]);
        return $this;
    }

    public function bindValues(array $parameters, array $types): self
    {
        if (is_int(key($parameters))) {
            // positional
            $paramPos = 1;
            $typePos = array_key_exists(0, $types) ? 0 : 1;
            foreach ($parameters as $value) {
                $this->bindValue($paramPos, $value, $types[$typePos] ?? null);
                $paramPos++;
                $typePos++;
            }
        } else {
            // named
            foreach ($parameters as $placeholder => $value) {
                $this->bindValue($placeholder, $value, $types[$placeholder] ?? null);
            }
        }

        return $this;
    }

    public function execute(array $params = null): PDOResult
    {
        $this->statement->execute($params);
        return new PDOResult($this->statement);
    }


}