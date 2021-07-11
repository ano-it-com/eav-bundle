<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVWithNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVWithOntologyClassInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;

interface EAVEntityRelationTypeInterface extends EAVPersistableInterface, EAVWithNamespaceInterface, EAVWithOntologyClassInterface
{

    public function __construct(string $id, EAVNamespaceInterface $namespace);


    public function getId(): string;


    public function getAlias(): string;


    public function setAlias(string $alias): void;


    public function getTitle(): string;


    public function setTitle(string $title): void;


    public function getMeta();


    public function setMeta($meta): void;
}