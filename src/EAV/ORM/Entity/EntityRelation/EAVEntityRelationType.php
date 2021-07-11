<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;

class EAVEntityRelationType implements EAVEntityRelationTypeInterface
{

    protected string $id;

    protected EAVNamespaceInterface $namespace;

    protected string $alias;

    protected string $title;

    protected ?string $ontologyClass = null;

    protected ?string $comment = null;

    protected $meta;


    public function __construct(string $id, EAVNamespaceInterface $namespace)
    {
        $this->id        = $id;
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
}