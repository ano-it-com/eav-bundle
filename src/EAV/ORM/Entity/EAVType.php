<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

class EAVType implements EAVPersistableInterface
{

    /** @var string */
    protected $id;

    /** @var string */
    protected $alias;

    /** @var string */
    protected $title;

    /** @var string|null */
    protected $meta;

    /** @var EAVTypeProperty[] */
    protected $properties = [];


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
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }


    /**
     * @param string $alias
     */
    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }


    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }


    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    /**
     * @return EAVTypeProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }


    /**
     * @param EAVTypeProperty[] $properties
     */
    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
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

}