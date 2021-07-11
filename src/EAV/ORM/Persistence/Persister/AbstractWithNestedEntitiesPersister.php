<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler\CriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler\OrderCriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor\DeleteLine;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor\InsertLine;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor\InsertNestedLine;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Builder\WithNestedEntityBuilderInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\ChangesCalculatorInterface;
use Doctrine\DBAL\Connection;

abstract class AbstractWithNestedEntitiesPersister
{

    protected EAVEntityManagerInterface $em;

    protected CriteriaHandlerInterface $criteriaHandler;

    protected ChangesCalculatorInterface $changesCalculator;

    protected WithNestedEntityBuilderInterface $builder;

    protected OrderCriteriaHandlerInterface $orderCriteriaHandler;


    abstract public static function getSupportedClass(): string;


    public function loadByCriteria(array $criteria = [], array $orderBy = [], $limit = null, $offset = null): array
    {
        foreach ($criteria as $oneCriteria) {
            $this->validateCriteria($oneCriteria);
        }

        $entityRows = $this->loadEntities($criteria, $orderBy, $limit, $offset);

        if ( ! count($entityRows)) {
            return [];
        }

        $entityIds = array_column($entityRows, 'id');

        $propertyRows = $this->loadNestedRows($entityIds);

        return $this->builder->buildEntities($entityRows, $propertyRows);

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

        $entityRows = $stmt->fetchAll();

        if ( ! count($entityRows)) {
            return null;
        }

        $entityIds = array_column($entityRows, 'id');

        $propertyRows = $this->loadNestedRows($entityIds);

        $entities = $this->builder->buildEntities($entityRows, $propertyRows);

        return $entities[0];
    }


    protected function loadEntities(array $criteria = [], array $orderBy = [], $limit = null, $offset = null): array
    {
        $settings  = $this->em->getEavSettings();
        $tableName = $settings->getTableNameForEntityType($this->getEntityType());

        $qb = $this->em->getConnection()
                       ->createQueryBuilder()
                       ->from($tableName)
                       ->select([ $tableName . '.*' ])
                       ->groupBy($tableName . '.id');

        $this->criteriaHandler->applyCriteria($qb, $criteria, $settings);

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


    protected function loadNestedRows(array $parentIds): array
    {
        $settings  = $this->em->getEavSettings();
        $tableName = $settings->getTableNameForEntityType($this->getNestedEntityType());

        $qb = $this->em->getConnection()
                       ->createQueryBuilder()
                       ->from($tableName)
                       ->select($tableName . '.*');

        $qb->andWhere($qb->expr()->in($this->getNestedEntityForeignKey(), ':ids'))
           ->setParameter('ids', $parentIds, Connection::PARAM_STR_ARRAY);

        $stmt = $qb->execute();

        return $stmt->fetchAll();
    }


    abstract protected function getNestedEntityType(): string;


    abstract protected function getNestedEntityForeignKey(): string;


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
        $insertData = [];
        $state      = $this->getCurrentState($entity);

        $entityTableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getEntityType());
        $nestedTableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getNestedEntityType());

        $nestedEntitiesKey = $this->getNestedEntitiesKey();

        $entityData = $state;
        $nestedData = $entityData[$nestedEntitiesKey];
        unset($entityData[$nestedEntitiesKey]);

        $this->convertEntityDataToDbData($entityData);

        $insertData[] = new InsertLine($entity, $entityData, $state, $entityTableName);

        foreach ($nestedData as $nestedItem) {

            $this->convertNestedEntityDataToDbData($nestedItem);

            $insertData[] = new InsertNestedLine($nestedItem, $nestedTableName);

        }

        return $insertData;
    }


    public function getDeferredDeleteData(EAVPersistableInterface $entity): array
    {
        $deleteData = [];
        $entityData = $this->builder->extractData($entity);

        $entityTableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getEntityType());
        $nestedTableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getNestedEntityType());

        $nestedEntitiesKey = $this->getNestedEntitiesKey();

        $nestedData = $entityData[$nestedEntitiesKey];
        unset($entityData[$nestedEntitiesKey]);

        $deleteData[] = DeleteLine::entity($entity->getId(), 'id', $entityTableName);

        foreach ($nestedData as $nestedItem) {

            $deleteData[] = DeleteLine::nested($nestedItem['id'], 'id', $nestedTableName);
        }

        return $deleteData;
    }


    abstract protected function getNestedEntitiesKey(): string;


    protected function convertEntityDataToDbData(array &$entityData): void { }


    protected function convertNestedEntityDataToDbData(array &$nestedData): void { }


    public function update(EAVPersistableInterface $entity, array $changeSet): void
    {
        $entityChanges = $changeSet['entity'] ?? [];

        if (count($entityChanges)) {
            $this->doEntityUpdate($entity, $entityChanges);
        }

        $nestedChanges = $changeSet['nested'] ?? [];

        if (count($nestedChanges)) {
            $this->doNestedUpdate($entity, $nestedChanges);
        }
    }


    protected function doEntityUpdate(EAVPersistableInterface $entity, array $entityChanges): void
    {
        $tableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getEntityType());

        $values = [];
        foreach ($entityChanges as $field => $change) {
            $newValue = $change['new'];

            $values[$field] = $newValue;
        }

        $this->em->getConnection()->update($tableName, $values, [ 'id' => $entity->getId() ]);


    }


    protected function doNestedUpdate(EAVPersistableInterface $entity, array $nestedChanges): void
    {
        $itemsToUpdate = $nestedChanges['updated'] ?? [];
        $itemsToAdd    = $nestedChanges['added'] ?? [];
        $itemsToRemove = $nestedChanges['removed'] ?? [];

        $tableName = $this->em->getEavSettings()->getTableNameForEntityType($this->getNestedEntityType());

        if (count($itemsToUpdate)) {
            foreach ($itemsToUpdate as $change) {
                $newValue = $change['new'];

                $valueId = $newValue['id'];

                $this->convertNestedEntityDataToDbData($newValue);

                $this->em->getConnection()->update($tableName, $newValue, [ 'id' => $valueId ]);
            }
        }

        if (count($itemsToAdd)) {
            foreach ($itemsToAdd as $change) {
                $newValue = $change['new'];

                $this->convertNestedEntityDataToDbData($newValue);

                $this->em->getConnection()->insert($tableName, $newValue);
            }
        }

        if (count($itemsToRemove)) {
            foreach ($itemsToRemove as $change) {
                $oldValue = $change['old'];

                $valueId = $oldValue['id'];

                $this->em->getConnection()->delete($tableName, [ 'id' => $valueId ]);
            }
        }
    }


    public function getCurrentState(EAVPersistableInterface $type): array
    {
        return $this->builder->extractData($type);
    }


    private function validateCriteria(FilterCriteriaInterface $oneCriteria): void
    {
        if ( ! $oneCriteria->supports($this->getEntityType())) {
            throw new \InvalidArgumentException('Criteria ' . get_class($oneCriteria) . ' doesn\'t supports entity ' . $this->getEntityType());

        }
    }

}