<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\Type;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;

class EAVTypeProperty implements EAVTypePropertyInterface
{

    protected string $id;

    protected EAVNamespaceInterface $namespace;

    protected string $typeId;

    protected ValueTypeInterface $valueType;

    protected string $alias;

    protected string $title;

    protected ?string $ontologyClass = null;

    protected ?string $comment = null;

    protected $meta;


    public function __construct(string $id, EAVNamespaceInterface $namespace, EAVTypeInterface $type, ValueTypeInterface $valueType)
    {
        $this->id        = $id;
        $this->valueType = $valueType;
        $this->typeId    = $type->getId();
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


    public function getTypeId(): string
    {
        return $this->typeId;
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


    public function getOntologyClass(): ?string
    {
        return $this->ontologyClass;
    }


    public function setOntologyClass(?string $ontologyClass): void
    {
        $this->ontologyClass = $ontologyClass;
    }


    public function getComment(): ?string
    {
        return $this->comment;
    }


    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }


    public function getMeta()
    {
        return $this->meta;
    }


    public function setMeta($meta): void
    {
        $this->meta = $meta;
    }


    public function getValueType(): ValueTypeInterface
    {
        return $this->valueType;
    }

}