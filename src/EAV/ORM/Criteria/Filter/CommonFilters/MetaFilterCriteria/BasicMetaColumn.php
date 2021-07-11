<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\MetaFilterCriteria;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class BasicMetaColumn implements ColumnInterface
{

    private string $columnName = 'meta';

    private string $fieldName;


    public function __construct(string $fieldName)
    {

        $this->fieldName = $fieldName;
    }


    public function getFullName(string $tableName): string
    {
        return $tableName . '.' . $this->columnName . '->>\'' . $this->fieldName . '\'';

    }


    public function getJoinTables(EAVSettings $eavSettings): array
    {
        return [];
    }

}