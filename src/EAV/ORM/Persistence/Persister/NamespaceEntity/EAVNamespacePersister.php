<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\NamespaceEntity;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaHandler\CriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Order\OrderCriteriaHandler\OrderCriteriaHandlerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespace;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\AbstractSimplePersister;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\NamespaceEntity\Builder\EAVNamespaceBuilder;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\NamespaceEntity\Builder\NamespaceChangesCalculator;

class EAVNamespacePersister extends AbstractSimplePersister implements EAVPersisterInterface
{

    public function __construct(
        EAVEntityManagerInterface $em,
        EAVNamespaceBuilder $builder,
        CriteriaHandlerInterface $criteriaHandler,
        OrderCriteriaHandlerInterface $orderCriteriaHandler,
        NamespaceChangesCalculator $changesCalculator

    ) {
        $this->em                   = $em;
        $this->criteriaHandler      = $criteriaHandler;
        $this->orderCriteriaHandler = $orderCriteriaHandler;
        $this->changesCalculator    = $changesCalculator;
        $this->builder              = $builder;
    }


    public static function getSupportedClass(): string
    {
        return EAVNamespace::class;
    }


    protected function getEntityType(): string
    {
        return EAVSettings::NAMESPACE;
    }

}