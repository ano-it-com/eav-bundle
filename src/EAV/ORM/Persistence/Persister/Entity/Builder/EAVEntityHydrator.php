<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\AbstractWithNestedEntitiesHydrator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\NamesConverter;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\ValueTypeConverter\ValueTypeConverter;

class EAVEntityHydrator extends AbstractWithNestedEntitiesHydrator implements EAVHydratorInterface
{

    private ValueTypeConverter $valueTypeConverter;


    public function __construct(EAVEntityManagerInterface $em, NamesConverter $namesConverter, ValueTypeConverter $valueTypeConverter)
    {
        parent::__construct($em, $namesConverter);
        $this->valueTypeConverter = $valueTypeConverter;
    }


    public function getEntityClass(): string
    {
        return $this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY);
    }


    public function getNestedEntityClass(): string
    {
        return $this->em->getEavSettings()->getClassForEntityType(EAVSettings::VALUES);
    }


    protected function getDataFieldForNestedEntities(): string
    {
        return '_values';
    }


    protected function getEntityFieldForNestedEntities(): string
    {
        return 'values';
    }


    protected function getEntityDbExcludeFields(): array
    {
        return [ 'type_id', 'namespace_id' ];
    }


    protected function getNestedDbExcludeFields(): array
    {
        return [ 'type_id', 'namespace_id', 'entity_id' ];
    }


    protected function getHydrationCallback(): ?callable
    {
        return static function (object $entity, array $entityData) {
            $entity->type      = $entityData['_type'];
            $entity->namespace = $entityData['_namespace'];
        };
    }


    protected function getNestedHydrationCallback(): ?callable
    {
        $valuesConverter = $this->valueTypeConverter;

        return static function (object $entity, array $entityData) use ($valuesConverter) {
            if (\array_key_exists('_value', $entityData)) {
                $entity->value = $valuesConverter->convertToPhpByValueTypeCode($entityData['_value_type'], $entityData['_value']);
            }

            $entity->valueTypeCode = $entityData['_value_type'];
            $entity->namespace     = $entityData['_namespace'];
        };
    }


    protected function removeTemporaryKeys(array $data): array
    {
        unset($data['_type'], $data['_namespace']);

        return $data;
    }


    protected function getExtractionCallback(): ?callable
    {
        return static function (array &$data, object $object) {
            $data['type_id']      = $object->getType()->getId();
            $data['namespace_id'] = $object->getNamespace()->getId();
        };
    }


    protected function getNestedExtractionCallback(): ?callable
    {
        $settings        = $this->em->getEavSettings();
        $valuesConverter = $this->valueTypeConverter;
        $valueColumns    = $settings->getAllValueColumnsNames();

        return static function (array &$data, object $object, object $parentObject) use ($valuesConverter, $valueColumns, $settings) {
            $value = $valuesConverter->convertToDatabaseByValueTypeCode($object->valueTypeCode, $object->value);

            $data['entity_id']    = $parentObject->getId();
            $data['_value']       = $value;
            $data['_value_type']  = $object->valueTypeCode;
            $data['namespace_id'] = $object->getNamespace()->getId();

            foreach ($valueColumns as $column) {
                $data[$column] = null;
            }

            $valueColumn        = $settings->getColumnNameForValueType($object->valueTypeCode);
            $data[$valueColumn] = $value;
        };
    }

}