<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;

class EAVSettings
{

    private array $config;

    /**
     * @var ValueTypes
     */
    private ValueTypes $valueTypes;


    public function __construct(array $config, ValueTypes $valueTypes)
    {

        $this->config     = $config;
        $this->valueTypes = $valueTypes;
    }


    public function getValueTypeByCode(int $valueTypeCode): ValueTypeInterface
    {
        return $this->valueTypes->getByCode($valueTypeCode);
    }


    public function getEntityTableName(): string
    {
        $name = $this->config['base_tables']['eav_entity']['table'] ?? null;

        if ($name === null) {
            throw new \RuntimeException('Entity Table Name not found in config');
        }

        return $name;
    }


    public function getTypeTableName(): string
    {
        $name = $this->config['base_tables']['eav_type']['table'] ?? null;

        if ($name === null) {
            throw new \RuntimeException('Type Table Name not found in config');
        }

        return $name;
    }


    public function getTypePropertyTableName(): string
    {
        $name = $this->config['base_tables']['eav_type_property']['table'] ?? null;

        if ($name === null) {
            throw new \RuntimeException('Type Property Table Name not found in config');
        }

        return $name;
    }


    public function getValuesTableName(): string
    {
        $name = $this->config['base_tables']['eav_values']['table'] ?? null;

        if ($name === null) {
            throw new \RuntimeException('Type Property Table Name not found in config');
        }

        return $name;
    }


    public function getEntityRelationsTableName(): string
    {
        // TODO - add to package config
        return 'eav_entity_relation';
    }


    public function getTypeRelationsTableName(): string
    {
        // TODO - add to package config
        return 'eav_type_relation';
    }


    /**
     * @return string[]
     */
    public function getValueTablesNames(): array
    {
        $valuesTablesConfig = $this->config['values_tables'] ?? [];

        return array_map(function (array $tableConfig) { return $tableConfig['table']; }, $valuesTablesConfig);
    }


    //TODO - precalculate all value columns
    public function getColumnNameForValueType(int $valueType): string
    {
        $type      = $this->valueTypes->getByCode($valueType);
        $typeClass = get_class($type);

        $foundColumns = [];

        $valueColumnsConfig = $this->config['base_tables']['eav_values']['columns'] ?? [];
        foreach ($valueColumnsConfig as $columnName => $types) {

            if (stripos($columnName, 'value_') !== 0) {
                continue;
            }

            if ($types && ! is_array($types)) {
                $types = [ $types ];
            }

            if (in_array($typeClass, $types, true)) {
                $foundColumns[] = $columnName;
            }
        }

        if ( ! count($foundColumns)) {
            throw new \RuntimeException('Table for type ' . $valueType . ' not found!');
        }

        if (count($foundColumns) > 1) {
            throw new \RuntimeException('Table for type ' . $valueType . ' is ambiguous - check config!');
        }

        return $foundColumns[0];
    }


    public function getAllValueColumnsNames(): array
    {
        $foundColumns = [];

        $valueColumnsConfig = $this->config['base_tables']['eav_values']['columns'] ?? [];
        foreach ($valueColumnsConfig as $columnName => $types) {

            if (stripos($columnName, 'value_') !== 0) {
                continue;
            }

            $foundColumns[] = $columnName;
        }

        return $foundColumns;
    }


    public function getBaseTypeClass(): string
    {
        return $this->config['base_type_class'];
    }

}