<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta;

interface JsonMetaInterface
{

    public static function fromArray(array $data): self;


    public function toArray(): array;


    public function merge(self $meta): self;
}