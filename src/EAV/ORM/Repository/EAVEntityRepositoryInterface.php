<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

interface EAVEntityRepositoryInterface
{

    public function findBy(array $criteria, int $refDepth = 0, array $orderBy = [], $limit = null, $offset = null);


    public function getEntityClass(): string;
}