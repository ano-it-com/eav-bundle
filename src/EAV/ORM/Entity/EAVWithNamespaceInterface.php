<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;

interface EAVWithNamespaceInterface
{

    public function getNamespace(): EAVNamespaceInterface;


    public function setNamespace(EAVNamespaceInterface $namespace): void;
}