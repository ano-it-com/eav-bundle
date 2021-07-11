<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor;

class TableDeleteList
{

    private string $table;

    private string $column;

    private array $ids = [];


    public function __construct(string $table, string $column)
    {
        $this->table  = $table;
        $this->column = $column;
    }


    public function addId(string $id): void
    {
        $this->ids[] = $id;
    }


    public function getTable(): string
    {
        return $this->table;
    }


    public function getColumn(): string
    {
        return $this->column;
    }


    public function getIds(): array
    {
        return $this->ids;
    }

}