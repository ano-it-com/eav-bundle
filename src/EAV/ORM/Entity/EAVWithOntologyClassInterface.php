<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

interface EAVWithOntologyClassInterface
{

    public function getOntologyClass(): ?string;


    public function setOntologyClass(?string $ontologyClass): void;
}