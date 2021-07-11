<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelationType\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\AbstractSimpleChangesCalculator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator\ChangesCalculatorInterface;

class EntityRelationTypeChangesCalculator extends AbstractSimpleChangesCalculator implements ChangesCalculatorInterface
{

    protected function getEntityType(): string
    {
        return EAVSettings::ENTITY_RELATION_TYPE;
    }
}