<?php

namespace LoneCat\ORM\DBAL\Driver;

use LoneCat\ORM\DBAL\Platform\MySQLPlatform;
use LoneCat\ORM\DBAL\Platform\AbstractPlatform;
use LoneCat\ORM\DBAL\Platform\SqLitePlatform;

class SqLitePDODriver
    extends PDODriver
{


    public function getPlatform(): AbstractPlatform
    {
        return new SqLitePlatform();
    }


    protected function generateDSN(array $options): string {

        $dsn = 'sqlite:';

        if (isset($options[self::DBNAME])) {
            $dsn .= $options[self::DBNAME];
        }

        return $dsn;
    }
}