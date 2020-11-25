<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;

class EAVTypeRepository implements EAVTypeRepositoryInterface
{

    /**
     * @var EAVEntityManagerInterface
     */
    private $em;


    public function __construct(EAVEntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
    {
        $persister = $this->em->getUnitOfWork()->getPersisterForClass($this->em->getEavSettings()->getBaseTypeClass());

        return $persister->loadByCriteria($criteria, $refDepth = 0, $orderBy, $limit, $offset);
    }


    public function getEntityClass(): string
    {
        return EAVEntity::class;
    }
}