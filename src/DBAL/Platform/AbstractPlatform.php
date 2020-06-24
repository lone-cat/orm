<?php

namespace LoneCat\ORM\DBAL\Platform;

abstract class AbstractPlatform
{

    public function quoteIdentifier(string $identifier) {
        $q = static::QUOTE_FOR_IDENTIFIER;

        $resultArray = [];
        foreach (explode('.', $identifier) as $key => $value) {
            if (!empty($value)) {
                $resultArray[] = $q . str_replace($q, $q.$q, $value) . $q;
            }
        }

        return implode('.', $resultArray);
    }

    public function getIsNullExpression(string $field) {
        return $field . ' IS NULL';
    }
}