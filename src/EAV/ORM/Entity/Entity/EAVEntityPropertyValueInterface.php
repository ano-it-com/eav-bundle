<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVTypePropertyInterface;

interface EAVEntityPropertyValueInterface
{

    public function __construct(string $id, EAVNamespaceInterface $namespace, EAVTypePropertyInterface $typeProperty);


    public function getId(): string;


    public function getNamespace(): EAVNamespaceInterface;


    public function setNamespace(EAVNamespaceInterface $namespace): void;


    public function getValue();


    public function setValue($value): void;


    public function getValueAsString(): ?string;


    public function getMeta();


    public function setMeta($meta): void;


    public function getTypePropertyId(): string;


    public function getValueTypeCode(): int;
}