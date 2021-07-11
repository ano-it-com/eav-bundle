<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler\CriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler\OrderCriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\UnitOfWork\BulkProcessor\DeleteLine;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\AbstractWithNestedEntitiesPersister;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity\Builder\EAVEntityBuilder;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity\Builder\EntityChangesCalculator;

class EAVEntityPersister extends AbstractWithNestedEntitiesPersister implements EAVPersisterInterface
{

    public function __construct(
        EAVEntityManagerInterface $em,
        EAVEntityBuilder $builder,
        CriteriaHandlerInterface $criteriaHandler,
        OrderCriteriaHandlerInterface $orderCriteriaHandler,
        EntityChangesCalculator $changesCalculator

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


    protected function getEntityType(): string
    {
        return EAVSettings::ENTITY;
    }


    protected function getNestedEntityType(): string
    {
        return EAVSettings::VALUES;
    }


    protected function getNestedEntityForeignKey(): string
    {
        return 'entity_id';
    }


    protected function getNestedEntitiesKey(): string
    {
        return '_values';
    }


    protected function convertNestedEntityDataToDbData(array &$nestedData): void
    {
        unset($nestedData['_value_type'], $nestedData['_value']);
    }


    public function getDeferredDeleteData(EAVPersistableInterface $entity): array
    {
        $deleteData = parent::getDeferredDeleteData($entity);

        $relationsTableName = $this->em->getEavSettings()->getTableNameForEntityType(EAVSettings::ENTITY_RELATION);

        $deleteData[] = DeleteLine::nested($entity->getId(), 'from_id', $relationsTableName);

        $deleteData[] = DeleteLine::nested($entity->getId(), 'to_id', $relationsTableName);

        return $deleteData;

    }

}