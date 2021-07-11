<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta\JsonMetaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVTypeInterface;

interface EAVEntityInterface extends EAVPersistableInterface
{

    public function __construct(string $id, EAVNamespaceInterface $namespace, EAVTypeInterface $type);


    public function getId(): string;


    public function getNamespace(): EAVNamespaceInterface;


    public function setNamespace(EAVNamespaceInterface $namespace): void;


    public function getType(): EAVTypeInterface;


    public function getMeta() : JsonMetaInterface;


    public function setMeta(JsonMetaInterface $meta): void;


    /**
     * @return EAVEntityPropertyValueInterface[]
     */
    public function getValues(): array;


    public function setValues(array $values): void;


    public function addPropertyValueByAlias(string $alias, $value): void;


    public function addPropertyValueByPropertyTypeId(string $id, $value, $meta = null): void;


    public function removeProperty(EAVEntityPropertyValueInterface $propertyValue): void;
}