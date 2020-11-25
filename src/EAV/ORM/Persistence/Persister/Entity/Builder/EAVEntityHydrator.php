<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntityPropertyValue;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVHydratorInterface;

class EAVEntityHydrator implements EAVHydratorInterface
{

    /**
     * @var EAVEntityManagerInterface
     */
    protected $em;


    public function __construct(EAVEntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function hydrate(array $entityRows): array
    {
        $uow      = $this->em->getUnitOfWork();
        $entities = [];

        foreach ($entityRows as $entityData) {
            $entity = $this->createEntity($entityData);
            $uow->registerManaged($entity, $this->removeTemporaryKeys($entityData));
            $entities[] = $entity;
        }

        return $entities;
    }


    protected function createEntity(array $entityData): EAVEntity
    {
        $valuesData = $entityData['_values'];

        $values = [];
        foreach ($valuesData as $valueData) {
            $values[] = $this->createValue($valueData);
        }

        return $this->createEntityObject($entityData, $values);
    }


    protected function createValue(array $valueData): EAVEntityPropertyValue
    {
        $reflector = new \ReflectionClass(EAVEntityPropertyValue::class);

        /** @var EAVEntityPropertyValue $value */
        $value = $reflector->newInstanceWithoutConstructor();

        $closure = \Closure::bind(static function ($object, $values) {
            if (\array_key_exists('id', $values)) {
                $object->id = $values['id'];
            }
            if (\array_key_exists('_value', $values)) {
                $object->value = $values['_value'];
            }
            if (\array_key_exists('type_property_id', $values)) {
                $object->typePropertyId = $values['type_property_id'];
            }
            if (\array_key_exists('_value_type', $values)) {
                $object->valueType = $values['_value_type'];
            }
            if (\array_key_exists('meta', $values)) {
                $object->meta = $values['meta'];
            }
        }, null, EAVEntityPropertyValue::class);

        $closure->__invoke($value, $valueData);

        return $value;

    }


    protected function createEntityObject(array $entityData, array $values): EAVEntity
    {
        $reflector = new \ReflectionClass(EAVEntity::class);

        /** @var EAVEntity $entity */
        $entity = $reflector->newInstanceWithoutConstructor();

        $closure = \Closure::bind(static function ($object, $data, $values) {
            if (\array_key_exists('id', $data)) {
                $object->id = $data['id'];
            }
            if (\array_key_exists('meta', $data)) {
                $object->meta = $data['meta'];
            }
            $object->type   = $data['_type'];
            $object->values = $values;
        }, null, EAVEntity::class);

        $closure->__invoke($entity, $entityData, $values);

        return $entity;
    }


    protected function removeTemporaryKeys(array $data): array
    {
        unset($data['_type']);

        return $data;
    }


    public function extract(EAVPersistableInterface $entity): array
    {
        $valueClosure = \Closure::bind(function ($propertyValue, $entityId) {
            return [
                'id'               => $propertyValue->id,
                'entity_id'        => $entityId,
                'value'            => $propertyValue->value,
                'type_property_id' => $propertyValue->typePropertyId,
                'meta'             => $propertyValue->meta,
                '_value_type'      => $propertyValue->valueType,
            ];

        }, null, EAVEntityPropertyValue::class);

        $closure = \Closure::bind(function ($object) use ($valueClosure) {
            $objectId = $object->id;

            $values = array_map(function ($value) use ($valueClosure, $objectId) {
                return $valueClosure->__invoke($value, $objectId);
            }, $object->values);

            return [
                'id'      => $objectId,
                'meta'    => $object->meta,
                'type_id' => $object->getType()->getId(),
                '_values' => $values,
            ];

        }, null, EAVEntity::class);

        return $closure->__invoke($entity);


    }
}