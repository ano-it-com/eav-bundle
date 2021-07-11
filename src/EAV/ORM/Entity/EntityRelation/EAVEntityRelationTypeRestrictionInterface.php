<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation;

interface EAVEntityRelationTypeRestrictionInterface
{

    public function __construct(string $id);


    public function getId(): string;


    public function getRestrictionTypeCode(): string;


    public function setRestrictionTypeCode(string $restrictionTypeCode): void;


    public function getRestriction(): array;


    public function setRestriction(array $restriction): void;


    public function getMeta();


    public function setMeta($meta): void;
}