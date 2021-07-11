<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\AbstractWithNestedEntitiesHydrator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;

class EAVTypeHydrator extends AbstractWithNestedEntitiesHydrator implements EAVHydratorInterface
{

    public function getEntityClass(): string
    {
        return $this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE);
    }


    public function getNestedEntityClass(): string
    {
        return $this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE_PROPERTY);
    }


    protected function getDataFieldForNestedEntities(): string
    {
        return '_properties';
    }


    protected function getEntityFieldForNestedEntities(): string
    {
        return 'properties';
    }


    protected function getEntityDbExcludeFields(): array
    {
        return [ 'namespace_id' ];
    }


    protected function getNestedDbExcludeFields(): array
    {
        return [ 'namespace_id', 'value_type' ];
    }


    protected function getHydrationCallback(): ?callable
    {
        return static function (object $entity, array $entityData) {

            $entity->namespace = $entityData['_namespace'];
        };
    }


    protected function getExtractionCallback(): ?callable
    {
        return static function (array &$data, object $object) {
            $data['namespace_id'] = $object->getNamespace()->getId();
        };
    }


    protected function getNestedHydrationCallback(): ?callable
    {
        $eavSettings = $this->em->getEavSettings();

        return static function (object $entity, array $entityData) use ($eavSettings) {
            if (\array_key_exists('value_type', $entityData)) {
                $entity->valueType = $eavSettings->getValueTypeByCode($entityData['value_type']);
                $entity->namespace = $entityData['_namespace'];
            }
        };
    }


    protected function getNestedExtractionCallback(): ?callable
    {
        return static function (array &$data, object $object, object $parentObject) {
            $data['value_type']   = $object->valueType->getCode();
            $data['namespace_id'] = $object->getNamespace()->getId();
        };
    }


    protected function removeTemporaryKeys(array $data): array
    {
        unset($data['_namespace']);

        foreach ($data['_properties'] as $i => &$property) {
            unset($property['_namespace']);
        }

        unset($property);

        return $data;
    }

}