<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor;

interface InsertableLineInterface
{

    public function getType(): int;


    public function getData(): array;


    public function getTable(): string;
}