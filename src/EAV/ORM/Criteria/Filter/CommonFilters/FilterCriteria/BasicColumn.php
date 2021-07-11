<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class BasicColumn implements ColumnInterface
{

    private string $columnName;


    public function __construct(string $columnName)
    {

        $this->columnName = $columnName;
    }


    public function getFullName(string $tableName): string
    {
        return $tableName . '.' . $this->columnName;

    }


    public function getJoinTables(EAVSettings $eavSettings): array
    {
        return [];
    }
}