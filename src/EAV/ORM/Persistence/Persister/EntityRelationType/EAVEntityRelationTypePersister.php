<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelationType;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler\CriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler\OrderCriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation\EAVEntityRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\AbstractSimplePersister;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelationType\Builder\EAVEntityRelationTypeBuilder;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelationType\Builder\EntityRelationTypeChangesCalculator;

class EAVEntityRelationTypePersister extends AbstractSimplePersister implements EAVPersisterInterface
{

    public function __construct(
        EAVEntityManagerInterface $em,
        EAVEntityRelationTypeBuilder $builder,
        CriteriaHandlerInterface $criteriaHandler,
        OrderCriteriaHandlerInterface $orderCriteriaHandler,
        EntityRelationTypeChangesCalculator $changesCalculator

    ) {
        $this->em                   = $em;
        $this->criteriaHandler      = $criteriaHandler;
        $this->orderCriteriaHandler = $orderCriteriaHandler;
        $this->changesCalculator    = $changesCalculator;
        $this->builder              = $builder;
    }


    public static function getSupportedClass(): string
    {
        return EAVEntityRelationType::class;
    }


    protected function getEntityType(): string
    {
        return EAVSettings::ENTITY_RELATION_TYPE;
    }

}