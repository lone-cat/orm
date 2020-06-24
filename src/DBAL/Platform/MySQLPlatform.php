<?php

namespace LoneCat\ORM\DBAL\Platform;

class MySQLPlatform
    extends AbstractPlatform
{

    protected const QUOTE_FOR_IDENTIFIER = '`';

}