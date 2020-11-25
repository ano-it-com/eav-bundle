<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class TextType extends AbstractType implements ValueTypeInterface
{

    public const TEXT = 0;


    public function getCode(): int
    {
        return self::TEXT;
    }

}