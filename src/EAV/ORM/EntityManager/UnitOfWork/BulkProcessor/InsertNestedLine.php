<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor;

class InsertNestedLine implements InsertableLineInterface
{

    private int $type;

    private array $data;

    private string $table;


    public function __construct(array $data, string $table)
    {

        $this->type  = BulkPlan::TYPE_NESTED;
        $this->data  = $data;
        $this->table = $table;
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

}