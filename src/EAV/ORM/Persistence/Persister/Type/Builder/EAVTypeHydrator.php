<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVTypeProperty;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVHydratorInterface;

class EAVTypeHydrator implements EAVHydratorInterface
{

    /**
     * @var EAVEntityManagerInterface
     */
    protected $em;


    public function __construct(EAVEntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function hydrate(array $typeRows): array
    {
        $uow      = $this->em->getUnitOfWork();
        $entities = [];

        foreach ($typeRows as $typeData) {
            $entity = $this->createEntity($typeData);
            $uow->registerManaged($entity, $typeData);
            $entities[] = $entity;
        }

        return $entities;
    }


    protected function createEntity(array $typeData): EAVType
    {
        $propertyData = $typeData['_properties'];
        unset($typeData['_properties']);

        $properties = [];
        foreach ($propertyData as $propertyDatum) {
            $properties[] = $this->createProperty($propertyDatum);
        }

        return $this->createType($typeData, $properties);
    }


    protected function createProperty(array $propertyDatum): EAVTypeProperty
    {
        $reflector = new \ReflectionClass(EAVTypeProperty::class);

        /** @var EAVTypeProperty $property */
        $property = $reflector->newInstanceWithoutConstructor();

        $eavSettings = $this->em->getEavSettings();

        $closure = \Closure::bind(static function ($object, $values) use ($eavSettings) {
            if (\array_key_exists('id', $values)) {
                $object->id = $values['id'];
            }
            if (\array_key_exists('type_id', $values)) {
                $object->typeId = $values['type_id'];
            }
            if (\array_key_exists('value_type', $values)) {
                $object->valueType = $eavSettings->getValueTypeByCode($values['value_type']);
            }
            if (\array_key_exists('alias', $values)) {
                $object->alias = $values['alias'];
            }
            if (\array_key_exists('title', $values)) {
                $object->title = $values['title'];
            }
            if (\array_key_exists('meta', $values)) {
                $object->meta = $values['meta'];
            }
        }, null, EAVTypeProperty::class);

        $closure->__invoke($property, $propertyDatum);

        return $property;

    }


    protected function createType(array $typeData, array $properties): EAVType
    {
        $reflector = new \ReflectionClass(EAVType::class);

        /** @var EAVType $type */
        $type = $reflector->newInstanceWithoutConstructor();

        $closure = \Closure::bind(static function ($object, $values, $properties) {
            if (\array_key_exists('id', $values)) {
                $object->id = $values['id'];
            }
            if (\array_key_exists('alias', $values)) {
                $object->alias = $values['alias'];
            }
            if (\array_key_exists('title', $values)) {
                $object->title = $values['title'];
            }
            if (\array_key_exists('meta', $values)) {
                $object->meta = $values['meta'];
            }
            $object->properties = $properties;
        }, null, EAVType::class);

        $closure->__invoke($type, $typeData, $properties);

        return $type;
    }


    public function extract(EAVPersistableInterface $entity): array
    {
        $propertiesClosure = \Closure::bind(function ($property) {
            return [
                'id'         => $property->id,
                'type_id'    => $property->typeId,
                'value_type' => $property->valueType->getCode(),
                'alias'      => $property->alias,
                'title'      => $property->title,
                'meta'       => $property->meta,
            ];

        }, null, EAVTypeProperty::class);

        $closure = \Closure::bind(function ($object) use ($propertiesClosure) {
            $properties = array_map(function ($value) use ($propertiesClosure) {
                return $propertiesClosure->__invoke($value);
            }, $object->properties);

            return [
                'id'          => $object->id,
                'alias'       => $object->alias,
                'title'       => $object->title,
                'meta'        => $object->meta,
                '_properties' => $properties,
            ];

        }, null, EAVType::class);

        return $closure->__invoke($entity);


    }
}