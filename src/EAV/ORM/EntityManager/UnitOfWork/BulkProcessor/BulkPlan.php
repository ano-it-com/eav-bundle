<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\EAVUnitOfWorkInterface;

class BulkPlan
{

    public const TYPE_ENTITY = 0;
    public const TYPE_NESTED = 1;

    private array $toInsert = [];

    private array $toDelete = [];

    private EAVUnitOfWorkInterface $uow;


    public function __construct(EAVUnitOfWorkInterface $uow)
    {
        $this->uow = $uow;
    }


    public function addInsert(EAVPersistableInterface $entity): void
    {
        $persister = $this->uow->getPersisterForClass(get_class($entity));

        $insertLines = $persister->getDeferredInsertData($entity);

        foreach ($insertLines as $insertLine) {
            $this->toInsert[$insertLine->getType()][$insertLine->getTable()][] = $insertLine;
        }
    }


    public function addDelete(EAVPersistableInterface $entity): void
    {
        $persister = $this->uow->getPersisterForClass(get_class($entity));

        $deleteLines = $persister->getDeferredDeleteData($entity);

        foreach ($deleteLines as $deleteLine) {
            $this->toDelete[$deleteLine->getType()][$this->makeDeleteKey($deleteLine)][] = $deleteLine;
        }
    }


    /**
     * @return InsertLine[]
     */
    public function getInsertEntityLines(): array
    {
        return $this->toInsert[self::TYPE_ENTITY] ?? [];
    }


    /**
     * @return InsertNestedLine[]
     */
    public function getInsertNestedLines(): array
    {
        return $this->toInsert[self::TYPE_NESTED] ?? [];
    }


    /**
     * @return TableDeleteList[]
     */
    public function getDeletes(): array
    {
        $deletes = [];
        foreach ([ self::TYPE_NESTED, self::TYPE_ENTITY ] as $type) {
            if ( ! isset($this->toDelete[$type])) {
                continue;
            }

            $byDeleteKey = $this->toDelete[$type];

            foreach ($byDeleteKey as $deleteKey => $lines) {
                if ( ! isset($deletes[$deleteKey])) {
                    $line                = reset($lines);
                    $deletes[$deleteKey] = new TableDeleteList($line->getTable(), $line->getIdentityColumn());
                }

                $tableDeleteList = $deletes[$deleteKey];

                /** @var DeleteLine $line */
                foreach ($lines as $line) {
                    $tableDeleteList->addId($line->getId());
                }
            }
        }

        return array_values($deletes);
    }


    private function makeDeleteKey(DeleteLine $line): string
    {
        return $line->getTable() . '|' . $line->getIdentityColumn();
    }

}