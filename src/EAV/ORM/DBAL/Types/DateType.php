<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class DateType extends AbstractType implements ValueTypeInterface
{

    public const DATE = 2;


    public function getCode(): int
    {
        return self::DATE;
    }

}