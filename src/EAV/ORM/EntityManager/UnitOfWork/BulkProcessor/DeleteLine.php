<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor;

class DeleteLine
{

    private int $type;

    private string $id;

    private string $identityColumn;

    private string $table;


    public static function entity(string $id, string $identityColumn, string $table): self
    {
        return new static(BulkPlan::TYPE_ENTITY, $id, $identityColumn, $table);
    }


    public static function nested(string $id, string $identityColumn, string $table): self
    {
        return new static(BulkPlan::TYPE_NESTED, $id, $identityColumn, $table);
    }


    private function __construct(int $type, string $id, string $identityColumn, string $table)
    {


        $this->type           = $type;
        $this->id             = $id;
        $this->identityColumn = $identityColumn;
        $this->table          = $table;
    }


    public function getType(): int
    {
        return $this->type;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getIdentityColumn(): string
    {
        return $this->identityColumn;
    }


    public function getTable(): string
    {
        return $this->table;
    }
}