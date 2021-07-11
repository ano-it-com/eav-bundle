<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta\JsonMetaInterface;

abstract class AbstractJsonMetaType extends AbstractType
{

    abstract public function getCode(): int;


    abstract public static function getMetaClass(): string;


    public function convertToPhp($value): ?JsonMetaInterface
    {
        if ($value === null) {
            return null;
        }

        /** @var JsonMetaInterface $metaClass */
        $metaClass = static::getMetaClass();

        return $metaClass::fromArray(json_decode($value, true, 512, JSON_THROW_ON_ERROR));
    }


    public function convertToDatabase($value)
    {
        /** @var JsonMetaInterface $value */
        if ($value === null) {
            return null;
        }

        $json = json_encode($value->toArray(), JSON_THROW_ON_ERROR);
        if ( ! $json) {
            return null;
        }

        return $json;
    }


    public function isEqualDBValues($value1, $value2): bool
    {
        if ($value1 === null || $value2 === null) {
            return $value1 === $value2;
        }

        $array1 = json_decode($value1, true, 512, JSON_THROW_ON_ERROR);
        $array2 = json_decode($value2, true, 512, JSON_THROW_ON_ERROR);

        // not strict comparison because order doesn't mean
        return $array1 == $array2;
    }
}