<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypeFilters\TypePropertyMetadata;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractDeferredFilterCriteria\AbstractJoinedTableFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class TypePropertyMetadataFilterCriteria extends AbstractJoinedTableFilterCriteria
{

    public function supports(string $entityType): bool
    {
        return $entityType === EAVSettings::TYPE;
    }


    protected function makeColumn($field, EAVSettings $eavSettings): ColumnInterface
    {
        $tableAlias = $eavSettings->getTableNameForEntityType(EAVSettings::TYPE_PROPERTY) . '_meta';

        return new TypePropertyMetadataColumn($field, $tableAlias);
    }
}
