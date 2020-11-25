<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Entity\EntityTypeMetadata;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\JoinHandler\JoinTableParams;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

class EntityTypeMetadataColumn implements ColumnInterface
{

    private string $columnName;

    private string $fieldName;


    public function __construct(string $columnName)
    {
        $this->columnName = 'meta';
        $this->fieldName  = $columnName;
    }


    public function getFullName(EAVSettings $eavSettings): string
    {
        return $eavSettings->getEntityTableName() . '.meta' . '->>\'' . $this->fieldName . '\'';
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