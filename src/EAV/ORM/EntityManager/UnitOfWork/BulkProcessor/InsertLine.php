<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;

class InsertLine implements InsertableLineInterface
{

    private EAVPersistableInterface $entity;

    private int $type;

    private array $data;

    private string $table;

    private array $state;


    public function __construct(EAVPersistableInterface $entity, array $data, array $state, string $table)
    {

        $this->type   = BulkPlan::TYPE_ENTITY;
        $this->data   = $data;
        $this->table  = $table;
        $this->state  = $state;
        $this->entity = $entity;
    }


    public function getType(): int
    {
        return $this->type;
    }


    public function getData(): array
    {
        return $this->data;
    }


    public function getTable(): string
    {
        return $this->table;
    }


    public function getState(): ?array
    {
        return $this->state;
    }


    public function getEntity(): EAVPersistableInterface
    {
        return $this->entity;
    }
}