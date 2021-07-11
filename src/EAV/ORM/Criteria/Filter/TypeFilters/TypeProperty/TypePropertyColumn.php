<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypeFilters\TypeProperty;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class TypePropertyColumn implements ColumnInterface
{

    private string $columnName;

    private string $tableAlias;


    public function __construct(string $columnName, string $tableAlias)
    {
        $this->columnName = $columnName;
        $this->tableAlias = $tableAlias;
    }


    public function getFullName(string $tableName): string
    {
        return $this->tableAlias . '.' . $this->columnName;
    }


    public function getJoinTables(EAVSettings $eavSettings): array
    {
        $typeTableName         = $eavSettings->getTableNameForEntityType(EAVSettings::TYPE);
        $typePropertyTableName = $eavSettings->getTableNameForEntityType(EAVSettings::TYPE_PROPERTY);

        return [
            new JoinTableParams(
                $typeTableName,
                'left',
                $typePropertyTableName,
                $this->tableAlias,
                $typeTableName . '.id =' . $this->tableAlias . '.type_id'
            ),
        ];
    }
}