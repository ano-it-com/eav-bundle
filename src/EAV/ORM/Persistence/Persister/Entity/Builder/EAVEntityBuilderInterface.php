<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;

interface EAVEntityBuilderInterface
{

    public function buildEntities(array $entityRows, array $valuesRows): array;


    public function extractData(EAVPersistableInterface $entity): array;
}