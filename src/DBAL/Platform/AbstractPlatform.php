<?php

namespace LoneCat\ORM\DBAL\Platform;

abstract class AbstractPlatform
{

    public function quoteIdentifier(string $identifier) {
        $q = static::QUOTE_FOR_IDENTIFIER;
        return $q . str_replace($q, $q.$q, $identifier) . $q;
    }

}