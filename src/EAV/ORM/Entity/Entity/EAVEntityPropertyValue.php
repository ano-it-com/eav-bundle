<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BasicJsonMetaType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DateTimeType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DateType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVTypePropertyInterface;

class EAVEntityPropertyValue implements EAVEntityPropertyValueInterface
{

    protected string $id;

    protected EAVNamespaceInterface $namespace;

    protected $value;

    protected int $valueTypeCode;

    protected string $typePropertyId;

    protected $meta;


    public function __construct(string $id, EAVNamespaceInterface $namespace, EAVTypePropertyInterface $typeProperty)
    {
        $this->id             = $id;
        $this->typePropertyId = $typeProperty->getId();
        $this->valueTypeCode  = $typeProperty->getValueType()->getCode();
        $this->namespace = $namespace;
    }


    public function getId(): string
    {
        return $this->id;
    }

    public function getNamespace(): EAVNamespaceInterface
    {
        return $this->namespace;
    }


    public function setNamespace(EAVNamespaceInterface $namespace): void
    {
        $this->namespace = $namespace;
    }


    public function getValue()
    {
        return $this->value;
    }


    public function setValue($value): void
    {
        $this->value = $value;
    }


    public function getValueAsString(): ?string
    {
        if ( ! $this->value) {
            return $this->value;
        }
        if ($this->valueTypeCode === DateType::CODE) {
            return $this->value->format('Y-m-d');
        }
        if ($this->valueTypeCode === DateTimeType::CODE) {
            return $this->value->format('Y-m-d H:i:s');
        }

        if ($this->valueTypeCode === BasicJsonMetaType::CODE) {
            return $this->value->toString();
        }

        return $this->value;
    }


    public function getMeta()
    {
        return $this->meta;
    }


    public function setMeta($meta): void
    {
        $this->meta = $meta;
    }


    public function getTypePropertyId(): string
    {
        return $this->typePropertyId;
    }


    public function getValueTypeCode(): int
    {
        return $this->valueTypeCode;
    }

}