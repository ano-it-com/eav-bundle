<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity;

class EAVNamespace implements EAVNamespaceInterface
{

    protected string $id;

    protected string $iri;

    protected ?string $title = null;

    protected ?string $comment = null;

    protected $meta;


    public function __construct(string $id, string $iri)
    {
        $this->id  = $id;
        $this->iri = $iri;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getIri(): string
    {
        return $this->iri;
    }


    public function getMeta()
    {
        return $this->meta;
    }


    public function setMeta($meta): void
    {
        $this->meta = $meta;
    }


    public function getTitle(): ?string
    {
        return $this->title;
    }


    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }


    public function getComment(): ?string
    {
        return $this->comment;
    }


    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

}