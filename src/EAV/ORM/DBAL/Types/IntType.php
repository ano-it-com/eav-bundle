<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class IntType extends AbstractType implements ValueTypeInterface
{

    public const INT = 3;


    public function getCode(): int
    {
        return self::INT;
    }

}