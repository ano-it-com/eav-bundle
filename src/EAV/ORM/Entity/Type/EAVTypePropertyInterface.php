<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\Type;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVWithNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVWithOntologyClassInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;

interface EAVTypePropertyInterface extends EAVWithNamespaceInterface, EAVWithOntologyClassInterface
{

    public function __construct(string $id, EAVNamespaceInterface $namespace, EAVTypeInterface $type, ValueTypeInterface $valueType);


    public function getId(): string;


    public function getTypeId(): string;


    public function getAlias(): string;


    public function setAlias(string $alias): void;


    public function getTitle(): string;


    public function setTitle(string $title): void;


    public function getMeta();


    public function setMeta($meta): void;


    public function getValueType(): ValueTypeInterface;
}