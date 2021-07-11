<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta\BasicMeta;

class BasicJsonMetaType extends AbstractJsonMetaType implements ValueTypeInterface
{

    public const CODE = 10;


    public function getCode(): int
    {
        return self::CODE;
    }


    public static function getMetaClass(): string
    {
        return BasicMeta::class;
    }
}