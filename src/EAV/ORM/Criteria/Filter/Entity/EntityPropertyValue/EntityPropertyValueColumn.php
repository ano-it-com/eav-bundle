<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityPropertyValue;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class EntityPropertyValueColumn implements ColumnInterface
{

    private string $columnName;


    public function __construct(string $columnName)
    {
        $this->columnName = $columnName;
    }


    public function getFullName(EAVSettings $eavSettings): string
    {
        return $eavSettings->getValuesTableName() . '.' . $this->columnName;

    }


    public function getJoinTables(EAVSettings $eavSettings): array
    {
        $entityTableName = $eavSettings->getEntityTableName();
        $valuesTableName = $eavSettings->getValuesTableName();

        return [
            new JoinTableParams($entityTableName, 'left', $valuesTableName, null, $entityTableName . '.id = ' . $valuesTableName . '.entity_id'),
        ];
    }
}