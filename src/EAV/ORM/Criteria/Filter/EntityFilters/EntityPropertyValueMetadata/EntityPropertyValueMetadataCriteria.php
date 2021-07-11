<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityFilters\EntityPropertyValueMetadata;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractDeferredFilterCriteria\AbstractJoinedTableFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class EntityPropertyValueMetadataCriteria extends AbstractJoinedTableFilterCriteria
{

    public function supports(string $entityType): bool
    {
        return $entityType === EAVSettings::ENTITY;
    }


    protected function makeColumn($field, EAVSettings $eavSettings): ColumnInterface
    {
        $tableAlias = $eavSettings->getTableNameForEntityType(EAVSettings::VALUES) . '_meta';

        return new EntityPropertyValueMetadataColumn($field, $tableAlias);
    }
}