<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;

interface EAVTypeBuilderInterface
{

    public function buildTypes(array $typeRows, array $propertyRows): array;


    public function extractData(EAVPersistableInterface $entity): array;
}