<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\Type;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVWithNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVWithOntologyClassInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;

interface EAVTypeInterface extends EAVPersistableInterface, EAVWithNamespaceInterface, EAVWithOntologyClassInterface
{

    public function __construct(string $id, EAVNamespaceInterface $namespace);


    public function getId(): string;


    public function getAlias(): string;


    public function setAlias(string $alias): void;


    public function getTitle(): string;


    public function setTitle(string $title): void;


    /**
     * @return EAVTypePropertyInterface[]
     */
    public function getProperties(): array;


    public function setProperties(array $properties): void;


    public function getPropertyById(string $id): ?EAVTypePropertyInterface;


    public function getMeta();


    public function setMeta($meta): void;


    public function getComment(): ?string;


    public function setComment(?string $comment): void;

}