<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler\CriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler\OrderCriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor\DeleteLine;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor\InsertLine;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Builder\SimpleEntityBuilderInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\ChangesCalculatorInterface;

abstract class AbstractSimplePersister
{

    protected EAVEntityManagerInterface $em;

    protected CriteriaHandlerInterface $criteriaHandler;

    protected ChangesCalculatorInterface $changesCalculator;

    protected SimpleEntityBuilderInterface $builder;

    protected OrderCriteriaHandlerInterface $orderCriteriaHandler;


    abstract public static function getSupportedClass(): string;


    public function loadByCriteria(array $criteria = [], array $orderBy = [], $limit = null, $offset = null): array
    {
        foreach ($criteria as $oneCriteria) {
            $this->validateCriteria($oneCriteria);
        }

        $entityRows = $this->loadEntityRows($criteria, $orderBy, $limit, $offset);

        if ( ! count($entityRows)) {
            return [];
        }

        return $this->builder->buildEntities($entityRows);
    }


    public function loadById(string $id): ?EAVPersistableInterface
    {
        $tableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getEntityType());

        $qb = $this->em->getConnection()
                       ->createQueryBuilder()
                       ->from($tableName)
                       ->select([ $tableName . '.*' ])
                       ->where($tableName . '.id = :id')
                       ->setParameter('id', $id);

        $stmt = $qb->execute();

        $sql = $qb->getSQL();

        $params = $qb->getParameters();

        $rows = $stmt->fetchAll();

        if ( ! count($rows)) {
            return null;
        }

        $entities = $this->builder->buildEntities($rows);

        return $entities[0];
    }


    protected function loadEntityRows(array $criteria = [], array $orderBy = [], $limit = null, $offset = null): array
    {
        $tableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getEntityType());

        $qb = $this->em->getConnection()
                       ->createQueryBuilder()
                       ->from($tableName)
                       ->select([ $tableName . '.*' ])
                       ->groupBy($tableName . '.id');

        $this->criteriaHandler->applyCriteria($qb, $criteria, $this->em->getEavSettings());

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        $this->orderCriteriaHandler->applyOrdering($qb, $orderBy, $this->em->getEavSettings());

        $stmt = $qb->execute();

        $sql = $qb->getSQL();

        $params = $qb->getParameters();

        return $stmt->fetchAll();
    }


    abstract protected function getEntityType(): string;


    public function getChanges(EAVPersistableInterface $entity, array $oldValues): array
    {
        $newValues = $this->builder->extractData($entity);

        if ($newValues === $oldValues) {
            return [];
        }

        return $this->changesCalculator->getChanges($newValues, $oldValues);
    }


    public function getDeferredInsertData(EAVPersistableInterface $entity): array
    {
        $entityData = $this->getCurrentState($entity);

        $tableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getEntityType());

        return [
            new InsertLine($entity, $entityData, $entityData, $tableName)
        ];
    }


    public function getDeferredDeleteData(EAVPersistableInterface $entity): array
    {
        $tableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getEntityType());

        return [
            DeleteLine::entity($entity->getId(), 'id', $tableName)
        ];
    }


    public function update(EAVPersistableInterface $entity, array $changeSet): void
    {
        $entityChanges = $changeSet['entity'] ?? [];

        if (count($entityChanges)) {
            $this->doUpdate($entity, $entityChanges);
        }

    }


    protected function doUpdate(EAVPersistableInterface $entity, array $entityChanges): void
    {
        $entityTableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getEntityType());

        $values = [];
        foreach ($entityChanges as $field => $change) {
            $newValue = $change['new'];

            $values[$field] = $newValue;
        }

        $this->em->getConnection()->update($entityTableName, $values, [ 'id' => $entity->getId() ]);


    }


    public function getCurrentState(EAVPersistableInterface $entity): array
    {
        return $this->builder->extractData($entity);
    }


    private function validateCriteria(FilterCriteriaInterface $oneCriteria): void
    {
        if ( ! $oneCriteria->supports($this->getEntityType())) {
            throw new \InvalidArgumentException('Criteria ' . get_class($oneCriteria) . ' doesn\'t supports entity ' . $this->getEntityType());

        }
    }
}