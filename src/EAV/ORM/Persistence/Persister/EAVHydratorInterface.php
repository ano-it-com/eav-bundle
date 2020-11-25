<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;

interface EAVHydratorInterface
{

    public function hydrate(array $entityRows): array;


    public function extract(EAVPersistableInterface $entity): array;
}