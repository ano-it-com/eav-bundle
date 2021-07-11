<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVWithNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;

interface EAVEntityRelationInterface extends EAVPersistableInterface, EAVWithNamespaceInterface
{

    public function __construct(string $id, EAVNamespaceInterface $namespace, EAVEntityRelationTypeInterface $type);


    public function getId(): string;


    public function getFrom(): EAVEntityInterface;


    public function setFrom(EAVEntityInterface $from): void;


    public function getTo(): EAVEntityInterface;


    public function setTo(EAVEntityInterface $to): void;


    public function getMeta();


    public function setMeta($meta): void;


    public function getType(): EAVEntityRelationTypeInterface;
}