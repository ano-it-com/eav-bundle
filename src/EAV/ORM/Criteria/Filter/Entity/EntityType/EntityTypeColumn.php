<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityType;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class EntityTypeColumn implements ColumnInterface
{

    private string $columnName;


    public function __construct(string $columnName)
    {

        $this->columnName = $columnName;
    }


    public function getFullName(EAVSettings $eavSettings): string
    {
        return $eavSettings->getTypeTableName() . '.' . $this->columnName;

    }


    public function getJoinTables(EAVSettings $eavSettings): array
    {
        $typeTableName   = $eavSettings->getTypeTableName();
        $entityTableName = $eavSettings->getEntityTableName();

        return [
            new JoinTableParams($entityTableName, 'left', $typeTableName, null, 'eav_entity.type_id = ' . $typeTableName . '.id'),
        ];
    }
}