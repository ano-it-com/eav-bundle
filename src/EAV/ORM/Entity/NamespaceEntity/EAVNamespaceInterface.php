<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;

interface EAVNamespaceInterface extends EAVPersistableInterface
{

    public function __construct(string $id, string $iri);


    public function getId(): string;


    public function getIri(): string;


    public function getMeta();


    public function setMeta($meta): void;


    public function getTitle(): ?string;


    public function setTitle(?string $title): void;


    public function getComment(): ?string;


    public function setComment(?string $comment): void;
}