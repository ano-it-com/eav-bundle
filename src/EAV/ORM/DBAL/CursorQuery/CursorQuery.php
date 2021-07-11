<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\CursorQuery;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class CursorQuery
{

    private bool $opened = false;

    private string $name;

    private Connection $connection;

    private QueryBuilder $qb;


    public function __construct(QueryBuilder $qb)
    {
        $this->name       = uniqid('cursor_', false);
        $this->connection = $qb->getConnection();
        $this->qb         = clone $qb;
    }


    public function fetch(int $count): \Generator
    {
        if ( ! $this->opened) {
            $this->open();
        }

        do {
            $sql  = sprintf('FETCH FORWARD %d FROM %s;', $count, $this->name);
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();

            $rows = $stmt->fetchAllAssociative();

            foreach ($rows as $row) {
                yield $row;
            }
        } while (count($rows) === $count);

        $this->close();
    }


    public function __destruct()
    {
        if ($this->opened) {
            $this->close();
        }
    }


    public function close(): void
    {
        if ( ! $this->opened) {
            return;
        }

        $this->connection->exec('CLOSE ' . $this->connection->quoteIdentifier($this->name));

        $this->connection->commit();

        $this->opened = false;
    }


    private function open(): void
    {
        $sql = sprintf(
            'DECLARE %s CURSOR FOR (%s)',
            $this->connection->quoteIdentifier($this->name),
            $this->qb->getSQL()
        );

        $this->connection->beginTransaction();
        try {
            $params = $this->qb->getParameters();
            $types  = $this->qb->getParameterTypes();

            $this->connection->executeQuery($sql, $params, $types);

            $this->opened = true;
        } catch (\Throwable $e) {
            $this->connection->rollBack();

            throw $e;
        }

    }
}