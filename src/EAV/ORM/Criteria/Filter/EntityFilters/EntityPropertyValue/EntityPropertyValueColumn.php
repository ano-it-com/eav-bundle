<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityFilters\EntityPropertyValue;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class EntityPropertyValueColumn implements ColumnInterface
{

    private string $columnName;

    private string $joinAlias;


    public function __construct(string $columnName, string $joinAlias)
    {
        $this->columnName = $columnName;
        $this->joinAlias  = $joinAlias;
    }


    public function getFullName(string $tableName): string
    {
        return $this->joinAlias . '.' . $this->columnName;

    }


    public function getJoinTables(EAVSettings $eavSettings): array
    {
        $entityTableName = $eavSettings->getTableNameForEntityType(EAVSettings::ENTITY);
        $valuesTableName = $eavSettings->getTableNameForEntityType(EAVSettings::VALUES);

        return [
            new JoinTableParams($entityTableName, 'left', $valuesTableName, $this->joinAlias, $entityTableName . '.id = ' . $this->joinAlias . '.entity_id'),
        ];
    }
}