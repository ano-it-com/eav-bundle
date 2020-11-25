<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class DecimalType extends AbstractType implements ValueTypeInterface
{

    public const DECIMAL = 4;


    public function getCode(): int
    {
        return self::DECIMAL;
    }

}