<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor\DeleteLine;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor\InsertLine;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor\InsertNestedLine;

interface EAVPersisterInterface
{

    public static function getSupportedClass(): string;


    public function loadByCriteria(array $criteria = [], array $orderBy = [], $limit = null, $offset = null);


    public function loadById(string $id): ?EAVPersistableInterface;


    public function getChanges(EAVPersistableInterface $entity, array $oldValues): array;


    public function update(EAVPersistableInterface $entity, array $changeSet): void;


    /**
     * @param EAVPersistableInterface $entity
     *
     * @return InsertLine|InsertNestedLine[]
     */
    public function getDeferredInsertData(EAVPersistableInterface $entity): array;


    /**
     * @param EAVPersistableInterface $entity
     *
     * @return DeleteLine[]
     */
    public function getDeferredDeleteData(EAVPersistableInterface $entity): array;


    public function getCurrentState(EAVPersistableInterface $entity): array;

}