<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings;

class EAVEntitySettings
{

    private string $tableName;

    private string $entityClass;

    private ?string $persisterClass;


    public function __construct(string $tableName, string $entityClass, ?string $persisterClass)
    {
        $this->tableName      = $tableName;
        $this->entityClass    = $entityClass;
        $this->persisterClass = $persisterClass;
    }


    public function getTableName(): string
    {
        return $this->tableName;
    }


    public function getEntityClass(): string
    {
        return $this->entityClass;
    }


    public function getPersisterClass(): ?string
    {
        return $this->persisterClass;
    }
}