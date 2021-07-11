<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\NamespaceEntity\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\AbstractSimpleHydrator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;

class EAVNamespaceHydrator extends AbstractSimpleHydrator implements EAVHydratorInterface
{

    public function getEntityClass(): string
    {
        return $this->em->getEavSettings()->getClassForEntityType(EAVSettings::NAMESPACE);
    }


    protected function getDbExcludeFields(): array
    {
        return [];
    }

}