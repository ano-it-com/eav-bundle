<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\NamespaceEntity\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\AbstractSimpleChangesCalculator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\AbstractWithNestedEntitiesChangesCalculator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\ChangesCalculatorInterface;

class NamespaceChangesCalculator extends AbstractSimpleChangesCalculator implements ChangesCalculatorInterface
{

    protected function getEntityType(): string
    {
        return EAVSettings::NAMESPACE;
    }
}