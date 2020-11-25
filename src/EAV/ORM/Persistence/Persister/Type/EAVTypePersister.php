<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler\CriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Type\TypeFilterCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler\OrderCriteriaHandler;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\BasicTypeChangesCalculator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\ChangesCalculatorInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type\Builder\EAVTypeBuilder;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type\Builder\EAVTypeBuilderInterface;
use Doctrine\DBAL\Connection;

class EAVTypePersister implements EAVPersisterInterface
{

    protected EAVEntityManagerInterface $em;

    protected CriteriaHandlerInterface $criteriaHandler;

    protected EAVSettings $eavSettings;

    protected ChangesCalculatorInterface $changesCalculator;

    protected EAVTypeBuilderInterface $builder;

    /**
     * @var OrderCriteriaHandler
     */
    private OrderCriteriaHandler $orderCriteriaHandler;


    public function __construct(
        EAVEntityManagerInterface $em,
        EAVTypeBuilder $builder,
        CriteriaHandlerInterface $criteriaHandler,
        OrderCriteriaHandler $orderCriteriaHandler,
        BasicTypeChangesCalculator $changesCalculator
    ) {
        $this->em                   = $em;
        $this->criteriaHandler      = $criteriaHandler;
        $this->orderCriteriaHandler = $orderCriteriaHandler;
        $this->eavSettings          = $this->em->getEavSettings();
        $this->changesCalculator    = $changesCalculator;
        $this->builder              = $builder;
    }


    public static function getSupportedClass(): string
    {
        return EAVType::class;
    }


    public function loadByCriteria(array $criteria = [], int $refDepth = 0, array $orderBy = [], $limit = null, $offset = null): array
    {
        foreach ($criteria as $oneCriteria) {
            if ( ! $oneCriteria instanceof TypeFilterCriteriaInterface) {
                throw new \InvalidArgumentException('Each criteria must implement TypeCriteriaInterface');
            }
        }

        $typeRows = $this->loadTypeRows($criteria, $orderBy, $limit, $offset);

        if ( ! count($typeRows)) {
            return [];
        }

        $typeIds = array_column($typeRows, 'id');

        $propertyRows = $this->loadTypePropertyRows($typeIds);

        return $this->builder->buildTypes($typeRows, $propertyRows);

    }


    protected function loadTypeRows(array $criteria = [], array $orderBy = [], $limit = null, $offset = null): array
    {
        $typeTableName = $this->eavSettings->getTypeTableName();

        $qb = $this->em->getConnection()
                       ->createQueryBuilder()
                       ->from($typeTableName)
                       ->select([ $typeTableName . '.id', $typeTableName . '.alias', $typeTableName . '.title', $typeTableName . '.meta' ])
                       ->groupBy($typeTableName . '.id');

        $this->criteriaHandler->applyCriteria($qb, $criteria, $this->eavSettings);

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


    protected function loadTypePropertyRows(array $typeIds): array
    {
        $typePropertyTableName = $this->eavSettings->getTypePropertyTableName();

        $qb = $this->em->getConnection()
                       ->createQueryBuilder()
                       ->from($typePropertyTableName)
                       ->select('*');

        $qb->andWhere($qb->expr()->in('type_id', ':types'))
           ->setParameter('types', $typeIds, Connection::PARAM_STR_ARRAY);

        $stmt = $qb->execute();

        return $stmt->fetchAll();
    }


    public function getChanges(EAVPersistableInterface $entity, array $oldValues): array
    {
        $newValues = $this->builder->extractData($entity);

        return $this->changesCalculator->getChanges($newValues, $oldValues);
    }


    public function insert(EAVPersistableInterface $type): void
    {
        $typeData = $this->builder->extractData($type);

        $this->handleTypeInsert($typeData);
    }


    protected function handleTypeInsert(array $typeData): void
    {
        $typeTableName         = $this->em->getEavSettings()->getTypeTableName();
        $typePropertyTableName = $this->em->getEavSettings()->getTypePropertyTableName();

        $properties = $typeData['_properties'];
        unset($typeData['_properties']);

        $this->em->getConnection()->insert($typeTableName, $typeData);

        foreach ($properties as $propertyData) {
            $this->em->getConnection()->insert($typePropertyTableName, $propertyData);
        }


    }


    public function update(EAVPersistableInterface $type, array $changeSet): void
    {
        $entityChanges = $changeSet['type'] ?? [];

        if (count($entityChanges)) {
            $this->handleTypeUpdate($type, $entityChanges);
        }

        $valuesChanges = $changeSet['property'] ?? [];

        if (count($valuesChanges)) {
            $this->handleTypePropertiesUpdate($type, $valuesChanges);
        }
    }


    protected function handleTypeUpdate(EAVPersistableInterface $type, array $typeChanges): void
    {
        $typeTableName = $this->em->getEavSettings()->getTypeTableName();

        $values = [];
        foreach ($typeChanges as $field => $change) {
            $newValue = $change['new'];

            $values[$field] = $newValue;
        }

        $this->em->getConnection()->update($typeTableName, $values, [ 'id' => $type->getId() ]);


    }


    protected function handleTypePropertiesUpdate(EAVPersistableInterface $type, array $valuesChanges): void
    {
        $valuesToUpdate = $valuesChanges['updated'] ?? [];
        $valuesToAdd    = $valuesChanges['added'] ?? [];
        $valuesToRemove = $valuesChanges['removed'] ?? [];

        $propertyTableName = $this->em->getEavSettings()->getTypePropertyTableName();

        if (count($valuesToUpdate)) {
            foreach ($valuesToUpdate as $change) {
                $newValue = $change['new'];

                $valueId = $newValue['id'];

                $this->em->getConnection()->update($propertyTableName, $newValue, [ 'id' => $valueId ]);
            }
        }

        if (count($valuesToAdd)) {
            foreach ($valuesToAdd as $change) {
                $newValue = $change['new'];

                $this->em->getConnection()->insert($propertyTableName, $newValue);
            }
        }

        if (count($valuesToRemove)) {
            foreach ($valuesToRemove as $change) {
                $oldValue = $change['old'];

                $valueId = $oldValue['id'];

                $this->em->getConnection()->delete($propertyTableName, [ 'id' => $valueId ]);
            }
        }
    }


    public function delete(EAVPersistableInterface $entity): void
    {
        $entityData = $this->builder->extractData($entity);

        $this->handleTypeDelete($entityData);
    }


    protected function handleTypeDelete(array $typeData): void
    {
        $typeTableName      = $this->em->getEavSettings()->getTypeTableName();
        $propertyTableName  = $this->em->getEavSettings()->getTypePropertyTableName();
        $relationsTableName = $this->em->getEavSettings()->getTypeRelationsTableName();

        // values
        $qb = $this->em->getConnection()->createQueryBuilder()
                       ->delete($propertyTableName)
                       ->where('type_id = :id')
                       ->setParameter('id', $typeData['id']);
        $qb->execute();

        // relations
        $qb = $this->em->getConnection()->createQueryBuilder()
                       ->delete($relationsTableName)
                       ->orWhere('from_id = :id')
                       ->orWhere('to_id = :id')
                       ->setParameter('id', $typeData['id']);
        $qb->execute();

        $this->em->getConnection()->delete($typeTableName, [ 'id' => $typeData['id'] ]);

    }


    public function getCurrentState(EAVPersistableInterface $type): array
    {
        return $this->builder->extractData($type);
    }

}