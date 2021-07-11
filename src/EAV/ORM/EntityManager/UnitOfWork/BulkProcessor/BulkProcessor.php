<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\EAVUnitOfWorkInterface;
use Doctrine\DBAL\Connection;

class BulkProcessor
{

    public const MAX_BULK_SIZE = 65535;

    private EAVUnitOfWorkInterface $uow;

    private Connection $connection;

    private int $bulkSize;


    public function __construct(EAVUnitOfWorkInterface $uow, Connection $connection, ?int $bulkSize = null)
    {
        $this->uow        = $uow;
        $this->connection = $connection;
        $this->bulkSize   = $bulkSize ?: static::MAX_BULK_SIZE;
    }


    public function execute(BulkPlan $bulkPlan): void
    {
        /**
         * @var InsertLine[] $lines
         */
        foreach ($bulkPlan->getInsertEntityLines() as $table => $lines) {
            $this->executeBatchInsert($table, $lines);

            foreach ($lines as $line) {
                $this->uow->registerManaged($line->getEntity(), $line->getState());
            }
        }

        foreach ($bulkPlan->getInsertNestedLines() as $table => $lines) {
            $this->executeBatchInsert($table, $lines);
        }

        foreach ($bulkPlan->getDeletes() as $tableDeleteList) {
            $this->executeBatchDelete($tableDeleteList->getTable(), $tableDeleteList->getColumn(), $tableDeleteList->getIds());
        }
    }


    private function executeBatchDelete(string $table, string $column, array $ids): void
    {
        $idsChunks = array_chunk($ids, $this->bulkSize);

        foreach ($idsChunks as $chunk) {
            $builder = $this->connection->createQueryBuilder()
                                        ->delete($table)
                                        ->where($column . ' in (:ids)')
                                        ->setParameter(':ids', $chunk, Connection::PARAM_INT_ARRAY);
            $builder->execute();
        }
    }


    /**
     * @param string                    $table
     * @param InsertableLineInterface[] $lines
     */
    private function executeBatchInsert(string $table, array $lines): void
    {
        $values = array_map(static function (InsertableLineInterface $line) { return $line->getData(); }, $lines);

        $firstValues   = reset($values);
        $columns       = sprintf('(%s)', implode(', ', array_keys($firstValues)));
        $columnsLength = count($firstValues);

        $valuesChunks = array_chunk($values, floor($this->bulkSize / count($firstValues)));

        foreach ($valuesChunks as $valuesChunk) {
            $datasetLength = count($valuesChunk);

            $placeholders = sprintf('(%s)', implode(', ', array_fill(0, $columnsLength, '?')));
            $placeholder  = implode(', ', array_fill(0, $datasetLength, $placeholders));

            $parameters = [];
            foreach ($valuesChunk as $valueSet) {
                foreach ($valueSet as $oneValue) {
                    $parameters[] = $oneValue;
                }
            }

            $sql = sprintf(
                'INSERT INTO %s %s VALUES %s;',
                $table,
                $columns,
                $placeholder
            );

            $result = $this->connection->executeUpdate($sql, $parameters);
        }


    }
}