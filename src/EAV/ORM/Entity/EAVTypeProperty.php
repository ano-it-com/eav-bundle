<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;

class EAVTypeProperty
{

    /** @var string */
    protected $id;

    /** @var string */
    protected $typeId;

    /** @var ValueTypeInterface */
    protected $valueType;

    /** @var string */
    protected $alias;

    /** @var string */
    protected $title;

    /** @var string|null */
    protected $meta;


    public function __construct(string $id)
    {
        $this->id = $id;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getTypeId(): string
    {
        return $this->typeId;
    }


    public function setTypeId(string $typeId): void
    {
        $this->typeId = $typeId;
    }


    public function getAlias(): string
    {
        return $this->alias;
    }


    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }


    public function getTitle(): string
    {
        return $this->title;
    }


    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    public function getMeta(): ?string
    {
        return $this->meta;
    }


    public function setMeta(?string $meta): void
    {
        $this->meta = $meta;
    }


    public function getValueType(): ValueTypeInterface
    {
        return $this->valueType;
    }


    public function setValueType(ValueTypeInterface $valueType): void
    {
        $this->valueType = $valueType;
    }

}