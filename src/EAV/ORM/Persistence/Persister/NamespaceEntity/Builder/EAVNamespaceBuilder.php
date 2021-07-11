<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\NamespaceEntity\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;

class EAVNamespaceBuilder implements EAVNamespaceBuilderInterface
{

    protected EAVEntityManagerInterface $em;

    protected EAVHydratorInterface $hydrator;


    public function __construct(
        EAVEntityManagerInterface $em,
        EAVNamespaceHydrator $hydrator
    ) {
        $this->em       = $em;
        $this->hydrator = $hydrator;
    }


    public function buildEntities(array $entityRows): array
    {
        return $this->hydrator->hydrate($entityRows);
    }


    public function extractData(EAVPersistableInterface $entity): array
    {
        return $this->hydrator->extract($entity);
    }

}