<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;

class EAVSettings
{

    public const NAMESPACE = 'namespace';
    public const ENTITY = 'entity';
    public const TYPE = 'type';
    public const TYPE_PROPERTY = 'type_property';
    public const VALUES = 'values';
    public const ENTITY_RELATION = 'entity_relation';
    public const ENTITY_RELATION_TYPE = 'entity_relation_type';

    private FieldValueTypeMapping $fieldValueTypeMapping;

    private array $entitySettingsByType;

    private array $entitySettingsByClass;

    private ValueTypes $valueTypes;


    public function __construct(ValueTypes $valueTypes, FieldValueTypeMapping $fieldValueTypeMapping, array $entitySettings)
    {
        $this->fieldValueTypeMapping = $fieldValueTypeMapping;
        $this->entitySettingsByType  = $entitySettings;
        $this->entitySettingsByClass = array_combine(array_map(function (EAVEntitySettings $entitySettings) { return $entitySettings->getEntityClass(); }, $entitySettings), $entitySettings);
        $this->valueTypes            = $valueTypes;
    }


    public function getValueTypeByCode(int $valueTypeCode): ValueTypeInterface
    {
        return $this->valueTypes->getByCode($valueTypeCode);
    }


    public function getClassForEntityType(string $entityType): string
    {
        return $this->getEntitySettingsByType($entityType)->getEntityClass();
    }


    private function getEntitySettingsByType(string $entityType): EAVEntitySettings
    {
        $entitySettings = $this->entitySettingsByType[$entityType] ?? null;

        if ($entitySettings === null) {
            throw new \RuntimeException('Entity settings for entity type \'' . $entityType . '\' not found in config');
        }

        return $entitySettings;
    }


    private function getEntitySettingsByClass(string $entityClass): EAVEntitySettings
    {
        $entitySettings = $this->entitySettingsByClass[$entityClass] ?? null;

        if ($entitySettings === null) {
            throw new \RuntimeException('Entity settings for entity class \'' . $entityClass . '\' not found in config');
        }

        return $entitySettings;
    }


    public function getTableNameForEntityType(string $entityType): string
    {
        return $this->getEntitySettingsByType($entityType)->getTableName();
    }


    public function getPersisterClassForEntityClass(string $entityType): string
    {
        return $this->getEntitySettingsByClass($entityType)->getPersisterClass();
    }


    public function getColumnNameForValueType(int $valueType): string
    {
        return $this->fieldValueTypeMapping->getColumnNameForValueType($valueType);
    }


    public function getAllValueColumnsNames(): array
    {
        return $this->fieldValueTypeMapping->getAllValueColumnsNames();
    }


    public function getValueTypeForField(string $class, string $field): ValueTypeInterface
    {
        return $this->fieldValueTypeMapping->getValueTypeForField($class, $field);

    }


    public function getFieldsMappingForEntityClass(string $class): array
    {
        return $this->fieldValueTypeMapping->getMappingForEntityClass($class);
    }

}