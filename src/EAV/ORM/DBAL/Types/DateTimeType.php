<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class DateTimeType extends AbstractType implements ValueTypeInterface
{

    public const DATETIME = 1;


    public function getCode(): int
    {
        return self::DATETIME;
    }

}