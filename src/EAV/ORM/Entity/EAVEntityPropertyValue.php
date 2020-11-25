<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

class EAVEntityPropertyValue
{

    /** @var string */
    protected $id;

    /** @var mixed */
    protected $value;

    /** @var int */
    protected $valueType;

    /** @var string */
    protected $typePropertyId;

    /** @var string|null */
    protected $meta;


    public function __construct(string $id)
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }


    /**
     * @return string|null
     */
    public function getMeta(): ?string
    {
        return $this->meta;
    }


    /**
     * @param string|null $meta
     */
    public function setMeta(?string $meta): void
    {
        $this->meta = $meta;
    }


    /**
     * @return string
     */
    public function getTypePropertyId(): string
    {
        return $this->typePropertyId;
    }


    /**
     * @param string $typePropertyId
     */
    public function setTypePropertyId(string $typePropertyId): void
    {
        $this->typePropertyId = $typePropertyId;
    }


    /**
     * @return int
     */
    public function getValueType(): int
    {
        return $this->valueType;
    }


    /**
     * @param int $valueType
     */
    public function setValueType(int $valueType): void
    {
        $this->valueType = $valueType;
    }
}