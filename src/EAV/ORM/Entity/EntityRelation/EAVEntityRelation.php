<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;

class EAVEntityRelation implements EAVEntityRelationInterface
{

    protected string $id;

    protected EAVNamespaceInterface $namespace;

    protected EAVEntityInterface $from;

    protected EAVEntityInterface $to;

    protected EAVEntityRelationTypeInterface $type;

    protected $meta;


    public function __construct(string $id, EAVNamespaceInterface $namespace, EAVEntityRelationTypeInterface $type)
    {
        $this->id        = $id;
        $this->namespace = $namespace;
        $this->type      = $type;
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


    public function getFrom(): EAVEntityInterface
    {
        return $this->from;
    }


    public function setFrom(EAVEntityInterface $from): void
    {
        $this->from = $from;
    }


    public function getTo(): EAVEntityInterface
    {
        return $this->to;
    }


    public function setTo(EAVEntityInterface $to): void
    {
        $this->to = $to;
    }


    public function getMeta()
    {
        return $this->meta;
    }


    public function setMeta($meta): void
    {
        $this->meta = $meta;
    }


    public function getType(): EAVEntityRelationTypeInterface
    {
        return $this->type;
    }

}