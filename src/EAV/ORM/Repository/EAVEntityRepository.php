<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;

class EAVEntityRepository implements EAVEntityRepositoryInterface
{

    /**
     * @var EAVEntityManagerInterface
     */
    private $em;


    public function __construct(EAVEntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function findBy(array $criteria, int $refDepth = 0, array $orderBy = [], $limit = null, $offset = null)
    {
        $persister = $this->em->getUnitOfWork()->getPersisterForClass($this->getEntityClass());

        return $persister->loadByCriteria($criteria, $refDepth, $orderBy, $limit, $offset);
    }


    public function getEntityClass(): string
    {
        return EAVEntity::class;
    }
}