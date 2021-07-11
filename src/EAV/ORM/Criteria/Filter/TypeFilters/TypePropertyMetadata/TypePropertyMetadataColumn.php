<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypeFilters\TypePropertyMetadata;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class TypePropertyMetadataColumn implements ColumnInterface
{

    private string $columnName = 'meta';

    private string $fieldName;

    private string $joinAlias;


    public function __construct(string $fieldName, string $joinAlias)
    {

        $this->fieldName = $fieldName;
        $this->joinAlias = $joinAlias;
    }


    public function getFullName(string $tableName): string
    {
        return $this->joinAlias . '.' . $this->columnName . '->>\'' . $this->fieldName . '\'';

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
                $this->joinAlias,
                $typeTableName . '.id = ' . $this->joinAlias . '.type_id'
            ),
        ];
    }
}
