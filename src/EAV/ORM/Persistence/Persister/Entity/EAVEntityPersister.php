<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler\CriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler\OrderCriteriaHandler;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\BasicEntityChangesCalculator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\ChangesCalculatorInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity\Builder\EAVEntityBuilder;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity\Builder\EAVEntityBuilderInterface;
use Doctrine\DBAL\Connection;

class EAVEntityPersister implements EAVPersisterInterface
{

    protected EAVEntityManagerInterface $em;

    protected CriteriaHandlerInterface $criteriaHandler;

    protected ChangesCalculatorInterface $changesCalculator;

    protected EAVEntityBuilderInterface $builder;

    private OrderCriteriaHandler $orderCriteriaHandler;


    public function __construct(
        EAVEntityManagerInterface $em,
        EAVEntityBuilder $builder,
        CriteriaHandlerInterface $criteriaHandler,
        OrderCriteriaHandler $orderCriteriaHandler,
        BasicEntityChangesCalculator $changesCalculator

    ) {
        $this->em                   = $em;
        $this->criteriaHandler      = $criteriaHandler;
        $this->orderCriteriaHandler = $orderCriteriaHandler;
        $this->changesCalculator    = $changesCalculator;
        $this->builder              = $builder;
    }


    public static function getSupportedClass(): string
    {
        return EAVEntity::class;
    }


    public function loadByCriteria(array $criteria = [], int $refDepth = 0, array $orderBy = [], $limit = null, $offset = null): array
    {
        foreach ($criteria as $oneCriteria) {
            if ( ! $oneCriteria instanceof \ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityFilterCriteriaInterface) {
                throw new \InvalidArgumentException('Each criteria must implement EntityCriteriaInterface');
            }
        }

        $entityRows = $this->loadEntityRows($criteria, $orderBy, $limit, $offset);

        if ( ! count($entityRows)) {
            return [];
        }

        $entityIds = array_values(array_unique(array_column($entityRows, 'id')));

        $entityValuesRows = $this->loadValuesRows($entityIds);

        return $this->builder->buildEntities($entityRows, $entityValuesRows);
    }


    protected function loadEntityRows(array $criteria = [], array $orderBy = [], $limit = null, $offset = null): array
    {
        $entityTableName = $this->em->getEavSettings()->getEntityTableName();

        $qb = $this->em->getConnection()
                       ->createQueryBuilder()
                       ->from($entityTableName)
                       ->select([ $entityTableName . '.id', $entityTableName . '.type_id', $entityTableName . '.meta' ])
                       ->groupBy($entityTableName . '.id');

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


    protected function loadValuesRows(array $entityIds): array
    {
        $valuesTableName = $this->em->getEavSettings()->getValuesTableName();

        $qb = $this->em->getConnection()
                       ->createQueryBuilder()
                       ->from($valuesTableName)
                       ->select('*');

        $parameterName = 'entityIds';
        $expr          = $qb->expr()->in($valuesTableName . '.entity_id', ':' . $parameterName);
        $qb->andWhere($expr)->setParameter($parameterName, $entityIds, Connection::PARAM_STR_ARRAY);

        $stmt = $qb->execute();

        return $stmt->fetchAll();
    }


    public function getChanges(EAVPersistableInterface $entity, array $oldValues): array
    {
        $newValues = $this->builder->extractData($entity);

        return $this->changesCalculator->getChanges($newValues, $oldValues);
    }


    public function insert(EAVPersistableInterface $entity): void
    {
        $entityData = $this->builder->extractData($entity);

        $this->handleEntityInsert($entityData);
    }


    protected function handleEntityInsert(array $entityData): void
    {
        $entityTableName = $this->em->getEavSettings()->getEntityTableName();
        $valueTableName  = $this->em->getEavSettings()->getValuesTableName();

        $values = $entityData['_values'];
        unset($entityData['_values']);

        $this->em->getConnection()->insert($entityTableName, $entityData);

        foreach ($values as $newValue) {
            $newValue = $this->convertNewValueForValueType($newValue);

            $this->em->getConnection()->insert($valueTableName, $newValue);
        }


    }


    private function convertNewValueForValueType(array $newValue): array
    {
        $valueType       = $newValue['_value_type'];
        $valueColumnName = $this->em->getEavSettings()->getColumnNameForValueType($valueType);
        $allColumns      = $this->em->getEavSettings()->getAllValueColumnsNames();

        foreach ($allColumns as $columnName) {
            $newValue[$columnName] = null;
        }

        $newValue[$valueColumnName] = $newValue['value'];

        unset($newValue['_value_type'], $newValue['value']);

        return $newValue;
    }


    public function delete(EAVPersistableInterface $entity): void
    {
        $entityData = $this->builder->extractData($entity);

        $this->handleEntityDelete($entityData);
    }


    protected function handleEntityDelete(array $entityData): void
    {
        $entityTableName    = $this->em->getEavSettings()->getEntityTableName();
        $valueTableName     = $this->em->getEavSettings()->getValuesTableName();
        $relationsTableName = $this->em->getEavSettings()->getEntityRelationsTableName();

        // values
        $qb = $this->em->getConnection()->createQueryBuilder()
                       ->delete($valueTableName)
                       ->where('entity_id = :id')
                       ->setParameter('id', $entityData['id']);
        $qb->execute();

        // relations
        $qb = $this->em->getConnection()->createQueryBuilder()
                       ->delete($relationsTableName)
                       ->orWhere('from_id = :id')
                       ->orWhere('to_id = :id')
                       ->setParameter('id', $entityData['id']);
        $qb->execute();

        $this->em->getConnection()->delete($entityTableName, [ 'id' => $entityData['id'] ]);

    }


    public function update(EAVPersistableInterface $entity, array $changeSet): void
    {
        $entityChanges = $changeSet['entity'] ?? [];

        if (count($entityChanges)) {
            $this->handleEntityUpdate($entity, $entityChanges);
        }

        $valuesChanges = $changeSet['values'] ?? [];

        if (count($valuesChanges)) {
            $this->handleEntityValuesUpdate($entity, $valuesChanges);
        }

    }


    protected function handleEntityUpdate(EAVPersistableInterface $entity, array $entityChanges): void
    {
        $entityTableName = $this->em->getEavSettings()->getEntityTableName();

        $values = [];
        foreach ($entityChanges as $field => $change) {
            $newValue = $change['new'];

            $values[$field] = $newValue;
        }

        $this->em->getConnection()->update($entityTableName, $values, [ 'id' => $entity->getId() ]);


    }


    protected function handleEntityValuesUpdate(EAVPersistableInterface $entity, array $valuesChanges): void
    {
        $valuesToUpdate = $valuesChanges['updated'] ?? [];
        $valuesToAdd    = $valuesChanges['added'] ?? [];
        $valuesToRemove = $valuesChanges['removed'] ?? [];

        $valueTableName = $this->em->getEavSettings()->getValuesTableName();

        if (count($valuesToUpdate)) {
            foreach ($valuesToUpdate as $change) {
                $newValue = $change['new'];

                $valueId = $newValue['id'];

                $newValue = $this->convertNewValueForValueType($newValue);

                $this->em->getConnection()->update($valueTableName, $newValue, [ 'id' => $valueId ]);
            }
        }

        if (count($valuesToAdd)) {
            foreach ($valuesToAdd as $change) {
                $newValue = $change['new'];

                $newValue = $this->convertNewValueForValueType($newValue);

                $this->em->getConnection()->insert($valueTableName, $newValue);
            }
        }

        if (count($valuesToRemove)) {
            foreach ($valuesToRemove as $change) {
                $oldValue = $change['old'];

                $valueId = $oldValue['id'];

                $this->em->getConnection()->delete($valueTableName, [ 'id' => $valueId ]);
            }
        }
    }


    public function getCurrentState(EAVPersistableInterface $entity): array
    {
        return $this->builder->extractData($entity);
    }

}