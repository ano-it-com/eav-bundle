<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL;

class ValueTypes
{

    /** @var ValueTypeInterface[] */
    private array $types;


    public function __construct(iterable $types)
    {
        /** @var ValueTypeInterface $type */
        foreach ($types as $type) {
            $code = $type->getCode();
            if (isset($this->types[$code])) {
                throw new \RuntimeException('Each Value Type must have unique code. Duplicate code for ' . $code);
            }
            $this->types[$code] = $type;
        }
    }


    public function getByCode(int $valueTypeCode): ValueTypeInterface
    {
        $type = $this->types[$valueTypeCode] ?? null;

        if ( ! $type) {
            throw new \RuntimeException('Type with code ' . $valueTypeCode . ' not found!');
        }

        return $type;
    }

}