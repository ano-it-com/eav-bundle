<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class BoolType extends AbstractType implements ValueTypeInterface
{

    public const BOOLEAN = 5;


    public function getCode(): int
    {
        return self::BOOLEAN;
    }

}