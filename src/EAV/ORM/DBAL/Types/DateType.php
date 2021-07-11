<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use DateTime;
use DateTimeInterface;

class DateType extends AbstractType implements ValueTypeInterface
{

    public const CODE = 2;


    public function getCode(): int
    {
        return self::CODE;
    }


    public function convertToPhp($value): ?DateTimeInterface
    {
        if ($value === null || $value instanceof DateTimeInterface) {
            return $value;
        }

        $val = DateTime::createFromFormat('Y-m-d H:i:s|', $value);
        if ($val) {
            $val->setTime(0,0,0,0);
            return $val;
        }

        $val = DateTime::createFromFormat('Y-m-d|', $value);
        if ($val) {
            return $val;
        }

        throw new \RuntimeException('Can\'t convert to PHP DateType value ' . $value);
    }


    public function convertToDatabase($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        throw new \RuntimeException('Can\'t convert to DB DateType value ' . $value);
    }

}