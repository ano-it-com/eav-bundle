<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta;

class BasicMeta implements JsonMetaInterface
{

    private array $meta;


    public function __construct(array $data = [])
    {
        $this->meta = $data;
    }


    public function toArray(): array
    {
        return $this->meta;
    }


    public static function fromArray(array $data): JsonMetaInterface
    {
        return new self($data);
    }


    public function merge(JsonMetaInterface $meta): JsonMetaInterface
    {
        $data  = $this->toArray();
        $data2 = $meta->toArray();

        $data = array_merge_recursive($data, $data2);

        return new self($data);
    }
}