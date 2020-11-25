<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

interface EAVTypeRepositoryInterface
{

    public function findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null);


    /**
     * Return ENTITY class, not TYPE class.
     * Because, each Entity must have only one concrete type class.
     *
     * @return string
     */
    public function getEntityClass(): string;
}